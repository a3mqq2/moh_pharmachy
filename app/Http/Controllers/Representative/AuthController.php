<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Mail\RepresentativeOtpMail;
use App\Models\CompanyRepresentative;
use App\Models\RepresentativeOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
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
            'name.required' => 'الاسم مطلوب',
            'job_title.required' => 'المسمى الوظيفي مطلوب',
            'phone.required' => 'رقم الهاتف مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صالح',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً',
        ]);

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
            ->with('success', 'تم إرسال رمز التحقق إلى بريدك الإلكتروني');
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
            'otp.required' => 'رمز التحقق مطلوب',
            'otp.size' => 'رمز التحقق يجب أن يكون 6 أرقام',
        ]);

        $registrationData = session('registration_data');
        if (!$registrationData) {
            return redirect()->route('register')
                ->with('error', 'انتهت صلاحية الجلسة، يرجى التسجيل مرة أخرى');
        }

        if (!RepresentativeOtp::verifyOtp($registrationData['email'], $request->otp, 'registration')) {
            return back()->with('error', 'رمز التحقق غير صحيح أو منتهي الصلاحية');
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
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'كلمة المرور غير متطابقة',
        ]);

        $registrationData = session('registration_data');
        if (!$registrationData) {
            return redirect()->route('register')
                ->with('error', 'انتهت صلاحية الجلسة، يرجى التسجيل مرة أخرى');
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
            ->with('success', 'تم إنشاء حسابك بنجاح!');
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
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صالح',
            'password.required' => 'كلمة المرور مطلوبة',
        ]);

        // Find representative
        $representative = CompanyRepresentative::where('email', $request->email)->first();

        if (!$representative || !Hash::check($request->password, $representative->password)) {
            return back()->with('error', 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
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
            ->with('success', 'تم إرسال رمز التحقق إلى بريدك الإلكتروني');
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
            'otp.required' => 'رمز التحقق مطلوب',
            'otp.size' => 'رمز التحقق يجب أن يكون 6 أرقام',
        ]);

        $loginData = session('login_data');
        if (!$loginData) {
            return redirect()->route('login')
                ->with('error', 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مرة أخرى');
        }

        if (!RepresentativeOtp::verifyOtp($loginData['email'], $request->otp, 'login')) {
            return back()->with('error', 'رمز التحقق غير صحيح أو منتهي الصلاحية');
        }

        // Find representative and login
        $representative = CompanyRepresentative::find($loginData['representative_id']);

        Auth::guard('representative')->login($representative, $loginData['remember']);
        $request->session()->regenerate();

        // Clear login session data
        session()->forget('login_data');

        return redirect()->intended(route('representative.dashboard'))
            ->with('success', 'تم تسجيل الدخول بنجاح');
    }

    public function logout(Request $request)
    {
        Auth::guard('representative')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'تم تسجيل الخروج بنجاح');
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
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صالح',
            'email.exists' => 'البريد الإلكتروني غير مسجل',
        ]);

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
            ->with('success', 'تم إرسال رمز التحقق إلى بريدك الإلكتروني');
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
            'otp.required' => 'رمز التحقق مطلوب',
            'otp.size' => 'رمز التحقق يجب أن يكون 6 أرقام',
        ]);

        $resetData = session('password_reset_data');
        if (!$resetData) {
            return redirect()->route('forgot-password')
                ->with('error', 'انتهت صلاحية الجلسة، يرجى المحاولة مرة أخرى');
        }

        if (!RepresentativeOtp::verifyOtp($resetData['email'], $request->otp, 'password_reset')) {
            return back()->with('error', 'رمز التحقق غير صحيح أو منتهي الصلاحية');
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
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'كلمة المرور غير متطابقة',
        ]);

        $resetData = session('password_reset_data');
        if (!$resetData) {
            return redirect()->route('forgot-password')
                ->with('error', 'انتهت صلاحية الجلسة، يرجى المحاولة مرة أخرى');
        }

        // Update password
        $representative = CompanyRepresentative::find($resetData['representative_id']);
        $representative->update([
            'password' => Hash::make($request->password),
        ]);

        // Clear session
        session()->forget('password_reset_data');

        return redirect()->route('login')
            ->with('success', 'تم تغيير كلمة المرور بنجاح! يمكنك الآن تسجيل الدخول');
    }

    public function resendOtp(Request $request)
    {
        $type = $request->input('type', 'registration');

        if ($type === 'login') {
            $loginData = session('login_data');
            if (!$loginData) {
                return response()->json(['success' => false, 'message' => 'انتهت صلاحية الجلسة']);
            }

            $otp = RepresentativeOtp::generateOtp($loginData['email'], 'login');
            Mail::to($loginData['email'])->send(new RepresentativeOtpMail($otp, $loginData['name'], 'login'));

            return response()->json(['success' => true, 'message' => 'تم إرسال رمز جديد']);
        } elseif ($type === 'password_reset') {
            $resetData = session('password_reset_data');
            if (!$resetData) {
                return response()->json(['success' => false, 'message' => 'انتهت صلاحية الجلسة']);
            }

            $otp = RepresentativeOtp::generateOtp($resetData['email'], 'password_reset');
            Mail::to($resetData['email'])->send(new RepresentativeOtpMail($otp, $resetData['name'], 'password_reset'));

            return response()->json(['success' => true, 'message' => 'تم إرسال رمز جديد']);
        } else {
            $registrationData = session('registration_data');
            if (!$registrationData) {
                return response()->json(['success' => false, 'message' => 'انتهت صلاحية الجلسة']);
            }

            $otp = RepresentativeOtp::generateOtp($registrationData['email'], 'registration');
            Mail::to($registrationData['email'])->send(new RepresentativeOtpMail($otp, $registrationData['name'], 'registration'));

            return response()->json(['success' => true, 'message' => 'تم إرسال رمز جديد']);
        }
    }
}
