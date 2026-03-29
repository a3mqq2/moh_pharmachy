<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Mail\RepresentativeOtpMail;
use App\Models\CompanyRepresentative;
use App\Models\RepresentativeOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
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

    public function showRegisterForm()
    {
        return view('representative.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:company_representatives,email',
        ], [
            'name.required' => __('auth.validation_name_required'),
            'job_title.required' => __('auth.validation_job_title_required'),
            'phone.required' => __('auth.validation_phone_required'),
            'email.required' => __('auth.validation_email_required'),
            'email.email' => __('auth.validation_email_invalid'),
            'email.unique' => __('auth.validation_email_taken'),
        ]);

        if (!$this->verifyRecaptcha($request->input('recaptcha_token'), 'register')) {
            return back()
                ->withInput()
                ->with('error', __('auth.recaptcha_failed'));
        }

        // Store registration data in session
        session([
            'registration_data' => [
                'name' => $request->name,
                'job_title' => $request->job_title,
                'phone' => $request->phone,
                'email' => $request->email,
            ]
        ]);

        // Generate and send OTP
        $otp = RepresentativeOtp::generateOtp($request->email, 'registration');
        Mail::to($request->email)->send(new RepresentativeOtpMail($otp, $request->name, 'registration'));

        return redirect()->route('verify-otp')
            ->with('success', __('auth.otp_sent_to_email'));
    }

    public function showVerifyOtpForm()
    {
        if (!session('registration_data')) {
            return redirect()->route('register');
        }

        return view('representative.auth.verify-otp', [
            'email' => session('registration_data.email'),
            'type' => 'registration'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => __('auth.validation_otp_required'),
            'otp.size' => __('auth.validation_otp_size'),
        ]);

        $registrationData = session('registration_data');
        if (!$registrationData) {
            return redirect()->route('register')
                ->with('error', __('auth.session_expired_register'));
        }

        if (!RepresentativeOtp::verifyOtp($registrationData['email'], $request->otp, 'registration')) {
            return back()->with('error', __('auth.invalid_otp'));
        }

        // Redirect to set password
        return redirect()->route('set-password');
    }

    public function showSetPasswordForm()
    {
        if (!session('registration_data')) {
            return redirect()->route('register');
        }

        return view('representative.auth.set-password');
    }

    public function setPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => __('auth.validation_password_required'),
            'password.min' => __('auth.validation_password_min'),
            'password.confirmed' => __('auth.validation_password_confirmed'),
        ]);

        $registrationData = session('registration_data');
        if (!$registrationData) {
            return redirect()->route('register')
                ->with('error', __('auth.session_expired_register'));
        }

        // Create the representative
        $representative = CompanyRepresentative::create([
            'name' => $registrationData['name'],
            'job_title' => $registrationData['job_title'],
            'phone' => $registrationData['phone'],
            'email' => $registrationData['email'],
            'password' => Hash::make($request->password),
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Clear session
        session()->forget('registration_data');

        // Login the user
        Auth::guard('representative')->login($representative);

        return redirect()->route('representative.dashboard')
            ->with('success', __('auth.account_created'));
    }

    public function showLoginForm()
    {
        return view('representative.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => __('auth.validation_email_required'),
            'email.email' => __('auth.validation_email_invalid'),
            'password.required' => __('auth.validation_password_required'),
        ]);

        // Find representative
        $representative = CompanyRepresentative::where('email', $request->email)->first();

        if (!$representative || !Hash::check($request->password, $representative->password)) {
            return back()->with('error', __('auth.invalid_credentials'));
        }

        // Store login data in session
        session([
            'login_data' => [
                'representative_id' => $representative->id,
                'email' => $representative->email,
                'name' => $representative->name,
                'remember' => $request->filled('remember'),
            ]
        ]);

        // Generate and send OTP
        $otp = RepresentativeOtp::generateOtp($representative->email, 'login');
        Mail::to($representative->email)->send(new RepresentativeOtpMail($otp, $representative->name, 'login'));

        return redirect()->route('verify-login-otp')
            ->with('success', __('auth.otp_sent_to_email'));
    }

    public function showVerifyLoginOtpForm()
    {
        if (!session('login_data')) {
            return redirect()->route('login');
        }

        return view('representative.auth.verify-login-otp', [
            'email' => session('login_data.email'),
            'type' => 'login'
        ]);
    }

    public function verifyLoginOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => __('auth.validation_otp_required'),
            'otp.size' => __('auth.validation_otp_size'),
        ]);

        $loginData = session('login_data');
        if (!$loginData) {
            return redirect()->route('login')
                ->with('error', __('auth.session_expired_login'));
        }

        if (!RepresentativeOtp::verifyOtp($loginData['email'], $request->otp, 'login')) {
            return back()->with('error', __('auth.invalid_otp'));
        }

        $representative = CompanyRepresentative::find($loginData['representative_id']);

        if (!$representative) {
            session()->forget('login_data');
            return redirect()->route('login')
                ->with('error', __('auth.account_not_found'));
        }

        Auth::guard('representative')->login($representative, $loginData['remember']);
        $request->session()->regenerate();

        // Clear login session data
        session()->forget('login_data');

        return redirect()->intended(route('representative.dashboard'))
            ->with('success', __('auth.logged_in'));
    }

    public function logout(Request $request)
    {
        Auth::guard('representative')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', __('auth.logged_out'));
    }

    public function showForgotPasswordForm()
    {
        return view('representative.auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:company_representatives,email',
        ], [
            'email.required' => __('auth.validation_email_required'),
            'email.email' => __('auth.validation_email_invalid'),
            'email.exists' => __('auth.validation_email_not_registered'),
        ]);

        if (!$this->verifyRecaptcha($request->input('recaptcha_token'), 'forgot_password')) {
            return back()
                ->withInput()
                ->with('error', __('auth.recaptcha_failed'));
        }

        $representative = CompanyRepresentative::where('email', $request->email)->first();

        // Store password reset data in session
        session([
            'password_reset_data' => [
                'representative_id' => $representative->id,
                'email' => $representative->email,
                'name' => $representative->name,
            ]
        ]);

        // Generate and send OTP
        $otp = RepresentativeOtp::generateOtp($representative->email, 'password_reset');
        Mail::to($representative->email)->send(new RepresentativeOtpMail($otp, $representative->name, 'password_reset'));

        return redirect()->route('verify-password-reset-otp')
            ->with('success', __('auth.otp_sent_to_email'));
    }

    public function showVerifyPasswordResetOtpForm()
    {
        if (!session('password_reset_data')) {
            return redirect()->route('forgot-password');
        }

        return view('representative.auth.verify-password-reset-otp', [
            'email' => session('password_reset_data.email'),
            'type' => 'password_reset'
        ]);
    }

    public function verifyPasswordResetOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => __('auth.validation_otp_required'),
            'otp.size' => __('auth.validation_otp_size'),
        ]);

        $resetData = session('password_reset_data');
        if (!$resetData) {
            return redirect()->route('forgot-password')
                ->with('error', __('auth.session_expired_retry'));
        }

        if (!RepresentativeOtp::verifyOtp($resetData['email'], $request->otp, 'password_reset')) {
            return back()->with('error', __('auth.invalid_otp'));
        }

        // Redirect to reset password
        return redirect()->route('reset-password');
    }

    public function showResetPasswordForm()
    {
        if (!session('password_reset_data')) {
            return redirect()->route('forgot-password');
        }

        return view('representative.auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => __('auth.validation_password_required'),
            'password.min' => __('auth.validation_password_min'),
            'password.confirmed' => __('auth.validation_password_confirmed'),
        ]);

        $resetData = session('password_reset_data');
        if (!$resetData) {
            return redirect()->route('forgot-password')
                ->with('error', __('auth.session_expired_retry'));
        }

        $representative = CompanyRepresentative::find($resetData['representative_id']);

        if (!$representative) {
            session()->forget('password_reset_data');
            return redirect()->route('forgot-password')
                ->with('error', __('auth.account_not_found_short'));
        }

        $representative->update([
            'password' => Hash::make($request->password),
        ]);

        // Clear session
        session()->forget('password_reset_data');

        return redirect()->route('login')
            ->with('success', __('auth.password_changed_login'));
    }

    public function resendOtp(Request $request)
    {
        $type = $request->input('type', 'registration');

        if ($type == 'login') {
            $loginData = session('login_data');
            if (!$loginData) {
                return response()->json(['success' => false, 'message' => __('auth.session_expired_login')]);
            }

            $otp = RepresentativeOtp::generateOtp($loginData['email'], 'login');
            Mail::to($loginData['email'])->send(new RepresentativeOtpMail($otp, $loginData['name'], 'login'));

            return response()->json(['success' => true, 'message' => __('auth.otp_new_sent')]);
        } elseif ($type == 'password_reset') {
            $resetData = session('password_reset_data');
            if (!$resetData) {
                return response()->json(['success' => false, 'message' => __('auth.session_expired_retry')]);
            }

            $otp = RepresentativeOtp::generateOtp($resetData['email'], 'password_reset');
            Mail::to($resetData['email'])->send(new RepresentativeOtpMail($otp, $resetData['name'], 'password_reset'));

            return response()->json(['success' => true, 'message' => __('auth.otp_new_sent')]);
        } else {
            $registrationData = session('registration_data');
            if (!$registrationData) {
                return response()->json(['success' => false, 'message' => __('auth.session_expired_register')]);
            }

            $otp = RepresentativeOtp::generateOtp($registrationData['email'], 'registration');
            Mail::to($registrationData['email'])->send(new RepresentativeOtpMail($otp, $registrationData['name'], 'registration'));

            return response()->json(['success' => true, 'message' => __('auth.otp_new_sent')]);
        }
    }
}
