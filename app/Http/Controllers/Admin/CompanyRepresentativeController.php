<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyRepresentative;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CompanyRepresentativeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_representatives'),
        ];
    }

    public function index(Request $request)
    {
        $query = CompanyRepresentative::withCount(['companies', 'foreignCompanies']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('verified')) {
            $query->where('is_verified', $request->verified);
        }

        if ($request->filled('has_companies')) {
            if ($request->has_companies === 'local') {
                $query->has('companies');
            } elseif ($request->has_companies === 'foreign') {
                $query->has('foreignCompanies');
            } elseif ($request->has_companies === 'both') {
                $query->has('companies')->has('foreignCompanies');
            }
        }

        $representatives = $query->latest()->paginate(15);

        $stats = [
            'total' => CompanyRepresentative::count(),
            'verified' => CompanyRepresentative::where('is_verified', true)->count(),
            'unverified' => CompanyRepresentative::where('is_verified', false)->count(),
            'with_local' => CompanyRepresentative::has('companies')->count(),
            'with_foreign' => CompanyRepresentative::has('foreignCompanies')->count(),
        ];

        return view('admin.company-representatives.index', compact('representatives', 'stats'));
    }

    public function show($id)
    {
        $representative = CompanyRepresentative::with([
            'companies',
            'foreignCompanies.pharmaceuticalProducts',
        ])->findOrFail($id);

        return view('admin.company-representatives.show', compact('representative'));
    }
}
