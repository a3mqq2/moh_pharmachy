<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CompanyRepresentative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class UnifiedAuthController extends Controller
{
    protected function verifyRecaptcha(?string $token, string $action): bool
    {
        $secretKey = config('services.recaptcha.secret_key');
        if (!$secretKey || !$token) {
            return true;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $token,
        ]);

        $result = $response->json();

        return ($result['success'] ?? false)
            && ($result['action'] ?? '') === $action
            && ($result['score'] ?? 0) >= 0.5;
    }

    public function showLoginForm()
    {
        return view('auth.unified-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
        ]);

        if (!$this->verifyRecaptcha($request->input('recaptcha_token'), 'login')) {
            return back()
                ->withInput($request->except('password'))
                ->with('error', 'فشل التحقق الأمني، يرجى المحاولة مرة أخرى');
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        $admin = User::where('email', $request->email)->first();
        if ($admin && Hash::check($request->password, $admin->password)) {
            Auth::guard('web')->login($admin, $remember);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')
                ->with('success', 'مرحباً بك في لوحة التحكم');
        }

        $representative = CompanyRepresentative::where('email', $request->email)->first();
        if ($representative && Hash::check($request->password, $representative->password)) {
            Auth::guard('representative')->login($representative, $remember);
            $request->session()->regenerate();

            return redirect()->route('representative.dashboard')
                ->with('success', 'مرحباً بك في لوحة التحكم');
        }

        return back()
            ->withInput($request->except('password'))
            ->with('error', 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
    }

    public function logout(Request $request)
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        } elseif (Auth::guard('representative')->check()) {
            Auth::guard('representative')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'تم تسجيل الخروج بنجاح');
    }
}
