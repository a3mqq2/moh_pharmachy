<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CompanyRepresentative;
use App\Models\RepresentativeOtp;
use App\Mail\RepresentativeOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class UnifiedAuthController extends Controller
{
    protected function verifyRecaptcha(?string $token, string $action): bool
    {
        if (app()->environment('local', 'testing') || config('app.debug')) {
            return true;
        }

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
        ]);

        if (!$this->verifyRecaptcha($request->input('recaptcha_token'), 'login')) {
            return back()
                ->withInput($request->except('password'))
                ->with('error', __('auth.recaptcha_failed'));
        }

        $remember = $request->has('remember');

        $admin = User::where('email', $request->email)->first();
        if ($admin && Hash::check($request->password, $admin->password)) {
            Auth::guard('web')->login($admin, $remember);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')
                ->with('success', __('auth.welcome_dashboard'));
        }

        $representative = CompanyRepresentative::where('email', $request->email)->first();
        if ($representative && Hash::check($request->password, $representative->password)) {
            Auth::guard('representative')->login($representative, $remember);
            $request->session()->regenerate();

            return redirect()->route('representative.dashboard')
                ->with('success', __('auth.welcome_dashboard'));
        }

        return back()
            ->withInput($request->except('password'))
            ->with('error', __('auth.invalid_credentials'));
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
            ->with('success', __('auth.logged_out'));
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        $representative = CompanyRepresentative::where('email', $request->email)->first();

        if (!$user && !$representative) {
            return back()
                ->withInput()
                ->with('error', __('auth.email_not_found'));
        }

        $name = $user ? $user->name : $representative->name;
        $accountType = $user ? 'user' : 'representative';
        $accountId = $user ? $user->id : $representative->id;

        $otp = RepresentativeOtp::generateOtp($request->email, 'password_reset');

        Mail::to($request->email)->send(new RepresentativeOtpMail($otp, $name, 'password_reset'));

        session([
            'password_reset_data' => [
                'email' => $request->email,
                'name' => $name,
                'account_type' => $accountType,
                'account_id' => $accountId,
            ]
        ]);

        return redirect()->route('admin.verify-password-otp');
    }

    public function showVerifyPasswordOtpForm()
    {
        $data = session('password_reset_data');
        if (!$data) {
            return redirect()->route('admin.forgot-password');
        }

        return view('auth.verify-password-otp', ['email' => $data['email']]);
    }

    public function verifyPasswordOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $data = session('password_reset_data');
        if (!$data) {
            return redirect()->route('admin.forgot-password');
        }

        if (!RepresentativeOtp::verifyOtp($data['email'], $request->otp, 'password_reset')) {
            return back()->with('error', __('auth.invalid_otp'));
        }

        session(['password_reset_verified' => true]);

        return redirect()->route('admin.reset-password');
    }

    public function showResetPasswordForm()
    {
        if (!session('password_reset_data') || !session('password_reset_verified')) {
            return redirect()->route('admin.forgot-password');
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $data = session('password_reset_data');
        if (!$data || !session('password_reset_verified')) {
            return redirect()->route('admin.forgot-password');
        }

        if ($data['account_type'] === 'user') {
            User::where('id', $data['account_id'])->update([
                'password' => Hash::make($request->password),
            ]);
        } else {
            CompanyRepresentative::where('id', $data['account_id'])->update([
                'password' => Hash::make($request->password),
            ]);
        }

        session()->forget(['password_reset_data', 'password_reset_verified']);

        return redirect()->route('login')
            ->with('success', __('auth.password_reset_success'));
    }

    public function resendPasswordOtp(Request $request)
    {
        $data = session('password_reset_data');
        if (!$data) {
            return response()->json(['success' => false, 'message' => __('auth.session_expired')]);
        }

        $otp = RepresentativeOtp::generateOtp($data['email'], 'password_reset');
        Mail::to($data['email'])->send(new RepresentativeOtpMail($otp, $data['name'], 'password_reset'));

        return response()->json(['success' => true, 'message' => __('auth.otp_resent')]);
    }
}
