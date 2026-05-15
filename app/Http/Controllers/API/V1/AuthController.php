<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $verification = EmailVerificationCode::where('email', strtolower($request->email))
            ->where('code', $request->verification_code)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$verification) {
            return $this->errorResponse('Kode verifikasi salah atau sudah kedaluwarsa.', [], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'customer',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $verification->update(['verified_at' => now()]);
        EmailVerificationCode::where('email', strtolower($request->email))
            ->whereNull('verified_at')
            ->delete();

        return $this->successResponse('Registrasi berhasil. Silakan login.', [
            'user' => new UserResource($user),
        ], 201);
    }

    public function sendRegisterCode(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email:rfc,dns|unique:users,email',
        ]);

        $email = strtolower($data['email']);
        $code = (string) random_int(100000, 999999);

        EmailVerificationCode::where('email', $email)->delete();
        EmailVerificationCode::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::raw(
            "Kode verifikasi akun Aurevina kamu adalah: {$code}\n\nKode berlaku 10 menit. Jangan berikan kode ini kepada siapa pun.",
            fn($message) => $message
                ->to($email)
                ->subject('Kode Verifikasi Aurevina')
        );

        return $this->successResponse('Kode verifikasi berhasil dikirim ke email.');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->errorResponse('Email atau password salah', [], 401);
        }

        $user = Auth::user();

        if ($user->isCustomer() && !$user->hasVerifiedEmail()) {
            Auth::logout();

            return $this->errorResponse('Email belum diverifikasi. Silakan cek email verifikasi atau kirim ulang verifikasi.', [], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->successResponse('Login berhasil', [
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->successResponse('Logout berhasil');
    }

    public function resendVerification(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email:rfc,dns|unique:users,email',
        ]);

        return $this->sendRegisterCode($request);
    }
}
