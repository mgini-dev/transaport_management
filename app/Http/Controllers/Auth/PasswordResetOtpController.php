<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordResetOtpController extends Controller
{
    private const OTP_EXPIRY_MINUTES = 10;
    private const MAX_VERIFY_ATTEMPTS = 5;
    private const SESSION_EMAIL_KEY = 'password_reset_otp.email';
    private const SESSION_VERIFIED_KEY = 'password_reset_otp.verified';

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            ['email' => ['required', 'email', 'exists:users,email']],
            ['email.exists' => 'No registered user was found with this email address.']
        );

        $user = $this->resolveUserByEmail((string) $validated['email']);
        if (! $user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'No registered user was found with this email address.']);
        }

        $this->sendOtpToUser($user);

        $request->session()->put(self::SESSION_EMAIL_KEY, $user->email);
        $request->session()->forget(self::SESSION_VERIFIED_KEY);

        return redirect()
            ->route('password.otp.form')
            ->with('status', 'OTP has been sent to your email address.');
    }

    public function showOtpForm(Request $request): View|RedirectResponse
    {
        $email = (string) $request->session()->get(self::SESSION_EMAIL_KEY, '');
        if ($email === '') {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Enter your email first to receive OTP.']);
        }

        $otpRecord = $this->activeOtpRecord($email);
        if (! $otpRecord || $otpRecord->consumed_at !== null) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'OTP session was not found. Request a new OTP.']);
        }

        $expiresInSeconds = max(0, now()->diffInSeconds($otpRecord->expires_at, false));
        if ($expiresInSeconds > 0) {
            $expiresInSeconds++;
        }

        return view('auth.verify-otp', [
            'email' => $email,
            'expiresInMinutes' => self::OTP_EXPIRY_MINUTES,
            'otpExpiresInSeconds' => $expiresInSeconds,
            'otpTotalSeconds' => self::OTP_EXPIRY_MINUTES * 60,
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $email = (string) $request->session()->get(self::SESSION_EMAIL_KEY, '');
        if ($email === '') {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Enter your email first to receive OTP.']);
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $otpRecord = $this->activeOtpRecord($email);
        if (! $otpRecord) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'OTP session was not found. Request a new OTP.']);
        }

        if ($otpRecord->consumed_at !== null) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'OTP already used. Request a new one.']);
        }

        if (now()->greaterThan($otpRecord->expires_at)) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new OTP.']);
        }

        if ((int) $otpRecord->attempts >= self::MAX_VERIFY_ATTEMPTS) {
            return back()->withErrors(['otp' => 'Too many invalid attempts. Please request a new OTP.']);
        }

        if (! Hash::check((string) $validated['otp'], (string) $otpRecord->otp_hash)) {
            $otpRecord->increment('attempts');

            return back()->withErrors([
                'otp' => 'Invalid OTP code. Please check and try again.',
            ]);
        }

        $otpRecord->forceFill([
            'verified_at' => now(),
            'attempts' => 0,
        ])->save();

        $request->session()->put(self::SESSION_VERIFIED_KEY, true);

        return redirect()->route('password.reset');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $email = (string) $request->session()->get(self::SESSION_EMAIL_KEY, '');
        if ($email === '') {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Enter your email first to receive OTP.']);
        }

        $user = $this->resolveUserByEmail($email);
        if (! $user) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'No registered user was found with this email address.']);
        }

        $this->sendOtpToUser($user);
        $request->session()->forget(self::SESSION_VERIFIED_KEY);

        return back()->with('status', 'A new OTP has been sent to your email.');
    }

    public function showResetForm(Request $request): View|RedirectResponse
    {
        $email = (string) $request->session()->get(self::SESSION_EMAIL_KEY, '');
        $verified = (bool) $request->session()->get(self::SESSION_VERIFIED_KEY, false);
        if ($email === '' || ! $verified) {
            return redirect()
                ->route('password.otp.form')
                ->withErrors(['otp' => 'Please verify OTP before setting a new password.']);
        }

        $otpRecord = $this->activeOtpRecord($email);
        if (! $otpRecord || $otpRecord->verified_at === null || $otpRecord->consumed_at !== null) {
            return redirect()
                ->route('password.otp.form')
                ->withErrors(['otp' => 'OTP verification is required.']);
        }

        return view('auth.reset-password-otp', [
            'email' => $email,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $email = (string) $request->session()->get(self::SESSION_EMAIL_KEY, '');
        $verified = (bool) $request->session()->get(self::SESSION_VERIFIED_KEY, false);
        if ($email === '' || ! $verified) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Password reset session expired. Start again.']);
        }

        $validated = $request->validate([
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $otpRecord = $this->activeOtpRecord($email);
        if (! $otpRecord || $otpRecord->verified_at === null || $otpRecord->consumed_at !== null) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'OTP verification is required.']);
        }

        $user = $this->resolveUserByEmail($email);
        if (! $user) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'No registered user was found with this email address.']);
        }

        $user->forceFill([
            'password' => Hash::make((string) $validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        $otpRecord->forceFill([
            'consumed_at' => now(),
        ])->save();

        $request->session()->forget(self::SESSION_EMAIL_KEY);
        $request->session()->forget(self::SESSION_VERIFIED_KEY);

        return redirect()
            ->route('login')
            ->with('status', 'Password has been reset successfully. You can now log in.');
    }

    private function resolveUserByEmail(string $email): ?User
    {
        $normalizedEmail = strtolower(trim($email));
        if ($normalizedEmail === '') {
            return null;
        }

        return User::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();
    }

    private function activeOtpRecord(string $email): ?PasswordResetOtp
    {
        return PasswordResetOtp::query()
            ->whereRaw('LOWER(email) = ?', [strtolower(trim($email))])
            ->orderByDesc('id')
            ->first();
    }

    private function sendOtpToUser(User $user): void
    {
        $otp = (string) random_int(100000, 999999);

        PasswordResetOtp::query()->updateOrCreate(
            ['email' => (string) $user->email],
            [
                'otp_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
                'verified_at' => null,
                'consumed_at' => null,
                'attempts' => 0,
            ]
        );

        Mail::to($user->email)->send(
            new PasswordResetOtpMail(
                userName: (string) $user->name,
                otpCode: $otp,
                expiresInMinutes: self::OTP_EXPIRY_MINUTES
            )
        );
    }
}
