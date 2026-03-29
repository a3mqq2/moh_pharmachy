<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PharmaceuticalProduct;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PharmaceuticalProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_pharmaceutical_products', only: ['index', 'show']),
            new Middleware('permission:preliminary_approve_product', only: ['approve']),
            new Middleware('permission:final_approve_product', only: ['finalApprove']),
            new Middleware('permission:reject_product', only: ['reject']),
            new Middleware('permission:approve_product_receipt', only: ['approveReceipt']),
            new Middleware('permission:reject_product_receipt', only: ['rejectReceipt']),
            new Middleware('permission:print_product_certificate', only: ['printCertificate']),
        ];
    }

    public function index(Request $request)
    {
        $query = PharmaceuticalProduct::with(['foreignCompany.localCompany', 'representative']);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->search . '%')
                  ->orWhere('scientific_name', 'like', '%' . $request->search . '%')
                  ->orWhere('registration_number', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('foreign_company') && $request->foreign_company != '') {
            $query->whereHas('foreignCompany', function ($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->foreign_company . '%');
            });
        }

        if ($request->has('local_company') && $request->local_company != '') {
            $query->whereHas('foreignCompany.localCompany', function ($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->local_company . '%');
            });
        }

        $products = $query->latest()->paginate(15);

        $stats = [
            'total' => PharmaceuticalProduct::count(),
            'uploading_documents' => PharmaceuticalProduct::where('status', 'uploading_documents')->count(),
            'pending_review' => PharmaceuticalProduct::where('status', 'pending_review')->count(),
            'preliminary_approved' => PharmaceuticalProduct::where('status', 'preliminary_approved')->count(),
            'pending_final_approval' => PharmaceuticalProduct::where('status', 'pending_final_approval')->count(),
            'pending_payment' => PharmaceuticalProduct::where('status', 'pending_payment')->count(),
            'payment_review' => PharmaceuticalProduct::where('status', 'payment_review')->count(),
            'active' => PharmaceuticalProduct::where('status', 'active')->count(),
            'rejected' => PharmaceuticalProduct::where('status', 'rejected')->count(),
        ];

        return view('admin.pharmaceutical-products.index', compact('products', 'stats'));
    }

    public function show(PharmaceuticalProduct $product)
    {
        $product->load(['foreignCompany.localCompany', 'representative', 'reviewedBy']);

        return view('admin.pharmaceutical-products.show', compact('product'));
    }

    public function approve(PharmaceuticalProduct $product)
    {
        if ($product->status != 'pending_review') {
            return back()->with('error', __('products.msg_cannot_approve_status'));
        }

        $product->update([
            'status' => 'preliminary_approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'preliminary_approved_by' => auth()->id(),
            'preliminary_approved_at' => now(),
            'rejection_reason' => null,
        ]);

        if ($product->representative) {
            $product->representative->notify(new \App\Notifications\PharmaceuticalProductPreliminaryApproved($product));
        }

        return back()->with('success', __('products.msg_preliminary_approved_success'));
    }

    public function finalApprove(Request $request, PharmaceuticalProduct $product)
    {
        if ($product->status != 'pending_final_approval') {
            return back()->with('error', __('products.msg_cannot_final_approve_status'));
        }

        if (!$product->hasCompleteDetailedInfo()) {
            return back()->with('error', __('products.msg_detailed_data_incomplete'));
        }

        $registrationFee = Setting::get('pharmaceutical_product_fee', 3000.00);

        if ($request->has('is_pre_registered')) {
            $year = $request->input('pre_registration_year');
            $seq = $request->input('pre_registration_sequence');
            $product->update([
                'is_pre_registered' => true,
                'pre_registration_number' => ($year && $seq) ? "{$year}-{$seq}" : null,
                'pre_registration_year' => $year,
            ]);
            $product->refresh();
        }

        $product->update([
            'status' => 'pending_payment',
            'final_approved_by' => auth()->id(),
            'final_approved_at' => now(),
        ]);

        $invoice = $product->invoices()->create([
            'invoice_number' => \App\Models\PharmaceuticalProductInvoice::generateInvoiceNumber(),
            'amount' => $registrationFee,
            'status' => 'unpaid',
        ]);

        if ($product->representative) {
            $product->representative->notify(new \App\Notifications\PharmaceuticalProductFinalApproved($product, $invoice));
        }

        return back()->with('success', __('products.msg_final_approved_success', ['amount' => number_format($registrationFee)]));
    }

    public function reject(Request $request, PharmaceuticalProduct $product)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($product->status != 'pending_review') {
            return back()->with('error', __('products.msg_cannot_reject_status'));
        }

        $product->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        if ($product->representative) {
            $product->representative->notify(new \App\Notifications\PharmaceuticalProductRejected($product, $request->rejection_reason));
        }

        return back()->with('success', __('products.msg_rejected_success'));
    }

    public function approveReceipt(PharmaceuticalProduct $product, \App\Models\PharmaceuticalProductInvoice $invoice)
    {
        if ($invoice->status == 'paid') {
            return back()->with('info', __('products.msg_receipt_already_approved'));
        }

        if ($invoice->status != 'pending_review') {
            return back()->with('error', __('products.msg_cannot_approve_receipt_status'));
        }

        if (!$invoice->receipt_path) {
            return back()->with('error', __('products.msg_no_receipt_uploaded'));
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($product, $invoice) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            if (!$product->registration_number) {
                if ($product->is_pre_registered && $product->pre_registration_number) {
                    $existingProduct = PharmaceuticalProduct::whereNotNull('registration_number')
                        ->where('registration_number', $product->pre_registration_number)
                        ->where('id', '!=', $product->id)
                        ->first();

                    if ($existingProduct) {
                        throw new \Exception(__('products.msg_reg_number_in_use', ['number' => $product->pre_registration_number]));
                    }

                    $product->update([
                        'registration_number' => $product->pre_registration_number,
                    ]);
                } else {
                    $product->update([
                        'registration_number' => PharmaceuticalProduct::generateRegistrationNumber(),
                    ]);
                }
            }

            if ($product->status == 'payment_review') {
                $product->update([
                    'status' => 'active',
                ]);
            }
        });

        if ($product->representative) {
            $product->representative->notify(new \App\Notifications\PharmaceuticalProductActivated($product, $invoice));
        }

        return back()->with('success', __('products.msg_receipt_approved_product_activated'));
    }

    public function rejectReceipt(Request $request, PharmaceuticalProduct $product, \App\Models\PharmaceuticalProductInvoice $invoice)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($invoice->status != 'pending_review') {
            return back()->with('error', __('products.msg_cannot_reject_receipt_status'));
        }

        if ($invoice->receipt_path) {
            \Storage::disk('public')->delete($invoice->receipt_path);
        }

        $invoice->update([
            'status' => 'unpaid',
            'receipt_path' => null,
        ]);

        $product->update([
            'status' => 'pending_payment',
        ]);

        if ($product->representative) {
            $product->representative->notify(new \App\Notifications\PharmaceuticalProductReceiptRejected($product, $invoice, $request->rejection_reason));
        }

        return back()->with('success', __('products.msg_receipt_rejected_success'));
    }

    public function printCertificate(PharmaceuticalProduct $product)
    {
        if ($product->status != 'active') {
            return back()->with('error', __('products.msg_certificate_active_only'));
        }

        $product->load(['foreignCompany.localCompany', 'representative']);

        $translations = $this->getTranslations();

        return view('admin.pharmaceutical-products.certificate', compact('product', 'translations'));
    }

    private function getTranslations()
    {
        return [
            'countries' => [
                'الأرجنتين' => 'Argentina',
                'مصر' => 'Egypt',
                'الأردن' => 'Jordan',
                'لبنان' => 'Lebanon',
                'سوريا' => 'Syria',
                'العراق' => 'Iraq',
                'السعودية' => 'Saudi Arabia',
                'الإمارات' => 'UAE',
                'الكويت' => 'Kuwait',
                'البحرين' => 'Bahrain',
                'قطر' => 'Qatar',
                'عمان' => 'Oman',
                'اليمن' => 'Yemen',
                'السودان' => 'Sudan',
                'المغرب' => 'Morocco',
                'الجزائر' => 'Algeria',
                'تونس' => 'Tunisia',
                'ليبيا' => 'Libya',
                'فلسطين' => 'Palestine',
                'الصومال' => 'Somalia',
                'جيبوتي' => 'Djibouti',
                'موريتانيا' => 'Mauritania',
                'الصين' => 'China',
                'الهند' => 'India',
                'باكستان' => 'Pakistan',
                'بنغلاديش' => 'Bangladesh',
                'تركيا' => 'Turkey',
                'إيران' => 'Iran',
                'أفغانستان' => 'Afghanistan',
                'إندونيسيا' => 'Indonesia',
                'ماليزيا' => 'Malaysia',
                'تايلاند' => 'Thailand',
                'الفلبين' => 'Philippines',
                'اليابان' => 'Japan',
                'كوريا الجنوبية' => 'South Korea',
                'فيتنام' => 'Vietnam',
                'ألمانيا' => 'Germany',
                'فرنسا' => 'France',
                'إيطاليا' => 'Italy',
                'إسبانيا' => 'Spain',
                'المملكة المتحدة' => 'United Kingdom',
                'بريطانيا' => 'United Kingdom',
                'روسيا' => 'Russia',
                'بولندا' => 'Poland',
                'رومانيا' => 'Romania',
                'هولندا' => 'Netherlands',
                'بلجيكا' => 'Belgium',
                'السويد' => 'Sweden',
                'النرويج' => 'Norway',
                'الدنمارك' => 'Denmark',
                'فنلندا' => 'Finland',
                'سويسرا' => 'Switzerland',
                'النمسا' => 'Austria',
                'البرتغال' => 'Portugal',
                'اليونان' => 'Greece',
                'الولايات المتحدة' => 'United States',
                'أمريكا' => 'United States',
                'كندا' => 'Canada',
                'المكسيك' => 'Mexico',
                'البرازيل' => 'Brazil',
                'الأرجنتين' => 'Argentina',
                'تشيلي' => 'Chile',
                'كولومبيا' => 'Colombia',
                'بيرو' => 'Peru',
                'فنزويلا' => 'Venezuela',
                'أستراليا' => 'Australia',
                'نيوزيلندا' => 'New Zealand',
                'جنوب أفريقيا' => 'South Africa',
                'نيجيريا' => 'Nigeria',
                'كينيا' => 'Kenya',
                'إثيوبيا' => 'Ethiopia',
                'غانا' => 'Ghana',
            ],
            'pharmaceutical_forms' => [
                'أقراص' => 'Tablets',
                'كبسولات' => 'Capsules',
                'شراب' => 'Syrup',
                'حقن' => 'Injection',
                'مرهم' => 'Ointment',
                'كريم' => 'Cream',
                'قطرة' => 'Drops',
                'بخاخ' => 'Spray',
                'لبوس' => 'Suppository',
                'محلول' => 'Solution',
                'معلق' => 'Suspension',
                'أخرى' => 'Other',
                'اخرى' => 'Other',
                'أخري' => 'Other',
                'اخري' => 'Other',
            ],
            'usage_methods' => [
                'فموي' => 'Oral',
                'حقن' => 'Injection',
                'موضعي' => 'Topical',
                'عيني' => 'Ophthalmic',
                'أذني' => 'Otic',
                'أنفي' => 'Nasal',
                'استنشاق' => 'Inhalation',
                'شرجي' => 'Rectal',
                'مهبلي' => 'Vaginal',
                'أخرى' => 'Other',
                'اخرى' => 'Other',
                'أخري' => 'Other',
                'اخري' => 'Other',
            ],
            'free_sale' => [
                'بوصفة طبية' => 'Prescription',
                'بدون وصفة' => 'Over the Counter',
                'أخرى' => 'Other',
                'اخرى' => 'Other',
                'أخري' => 'Other',
                'اخري' => 'Other',
            ],
            'packaging' => [
                'علبة' => 'Box',
                'زجاجة' => 'Bottle',
                'شريط' => 'Strip',
                'أمبولة' => 'Ampoule',
                'قارورة' => 'Vial',
                'أنبوب' => 'Tube',
                'أخرى' => 'Other',
                'اخرى' => 'Other',
                'أخري' => 'Other',
                'اخري' => 'Other',
            ],
        ];
    }
}
