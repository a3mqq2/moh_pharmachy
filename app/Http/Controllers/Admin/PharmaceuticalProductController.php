<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PharmaceuticalProduct;
use Illuminate\Http\Request;

class PharmaceuticalProductController extends Controller
{
    public function index(Request $request)
    {
        $query = PharmaceuticalProduct::with(['foreignCompany.localCompany', 'representative']);

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search !== '') {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('foreign_company') && $request->foreign_company !== '') {
            $query->whereHas('foreignCompany', function ($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->foreign_company . '%');
            });
        }

        if ($request->has('local_company') && $request->local_company !== '') {
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
        if ($product->status !== 'pending_review') {
            return back()->with('error', 'لا يمكن الموافقة على هذا الصنف في حالته الحالية.');
        }

        $product->update([
            'status' => 'preliminary_approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'preliminary_approved_by' => auth()->id(),
            'preliminary_approved_at' => now(),
            'rejection_reason' => null,
        ]);

        $product->representative->notify(new \App\Notifications\PharmaceuticalProductPreliminaryApproved($product));

        return back()->with('success', 'تم الموافقة المبدئية على الصنف الدوائي. تم إرسال إشعار للممثل لاستكمال البيانات التفصيلية.');
    }

    public function finalApprove(PharmaceuticalProduct $product)
    {
        if ($product->status !== 'pending_final_approval') {
            return back()->with('error', 'لا يمكن الموافقة النهائية على هذا الصنف في حالته الحالية.');
        }

        if (!$product->hasCompleteDetailedInfo()) {
            return back()->with('error', 'البيانات التفصيلية للصنف غير مكتملة.');
        }

        $product->update([
            'status' => 'pending_payment',
            'final_approved_by' => auth()->id(),
            'final_approved_at' => now(),
        ]);

        $invoice = $product->invoices()->create([
            'invoice_number' => \App\Models\PharmaceuticalProductInvoice::generateInvoiceNumber(),
            'amount' => 3000.00,
            'status' => 'unpaid',
        ]);

        $product->representative->notify(new \App\Notifications\PharmaceuticalProductFinalApproved($product, $invoice));

        return back()->with('success', 'تم الموافقة النهائية على الصنف الدوائي وإنشاء فاتورة بقيمة 3000 د.ل. تم إرسال إشعار للممثل.');
    }

    public function reject(Request $request, PharmaceuticalProduct $product)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($product->status !== 'pending_review') {
            return back()->with('error', 'لا يمكن رفض هذا الصنف في حالته الحالية.');
        }

        $product->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $product->representative->notify(new \App\Notifications\PharmaceuticalProductRejected($product, $request->rejection_reason));

        return back()->with('success', 'تم رفض الصنف الدوائي وإرسال إشعار للممثل.');
    }

    public function approveReceipt(PharmaceuticalProduct $product, \App\Models\PharmaceuticalProductInvoice $invoice)
    {
        if ($invoice->status !== 'pending_review') {
            return back()->with('error', 'لا يمكن الموافقة على هذا الإيصال في حالته الحالية.');
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $product->update([
            'status' => 'active',
        ]);

        $product->representative->notify(new \App\Notifications\PharmaceuticalProductActivated($product, $invoice));

        return back()->with('success', 'تم الموافقة على الإيصال وتفعيل الصنف الدوائي.');
    }

    public function rejectReceipt(Request $request, PharmaceuticalProduct $product, \App\Models\PharmaceuticalProductInvoice $invoice)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($invoice->status !== 'pending_review') {
            return back()->with('error', 'لا يمكن رفض هذا الإيصال في حالته الحالية.');
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

        $product->representative->notify(new \App\Notifications\PharmaceuticalProductReceiptRejected($product, $invoice, $request->rejection_reason));

        return back()->with('success', 'تم رفض الإيصال وإرسال إشعار للممثل.');
    }

    public function printCertificate(PharmaceuticalProduct $product)
    {
        if ($product->status !== 'active') {
            return back()->with('error', 'يمكن طباعة الشهادة فقط للأصناف المفعلة.');
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
