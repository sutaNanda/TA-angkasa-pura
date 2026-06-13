<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Tampilkan form request link reset password.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Kirim email link reset password.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['success' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Tampilkan form reset password (dari email link).
     *
     * KEAMANAN: Validasi bahwa token dan email benar-benar valid
     * sebelum menampilkan form. Ini mencegah user mengakses
     * halaman reset password dengan mengetik URL secara manual.
     */
    public function showResetForm(Request $request, $token = null)
    {
        $email = $request->query('email');

        // 1. Email wajib ada di query string
        if (empty($email)) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Link reset password tidak valid. Silakan minta link baru.']);
        }

        // 2. Cek apakah ada record token untuk email ini di database
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Link reset password tidak valid atau sudah digunakan.']);
        }

        // 3. Cek apakah token cocok (Laravel menyimpan token dalam bentuk hash)
        if (!Hash::check($token, $record->token)) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Link reset password tidak valid.']);
        }

        // 4. Cek apakah token sudah expired (default 60 menit dari config/auth.php)
        $expireMinutes = config('auth.passwords.users.expire', 60);
        $createdAt = \Carbon\Carbon::parse($record->created_at);

        if ($createdAt->addMinutes($expireMinutes)->isPast()) {
            // Hapus token yang sudah expired
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return redirect()->route('password.request')
                ->withErrors(['email' => 'Link reset password sudah kedaluwarsa. Silakan minta link baru.']);
        }

        // Token valid → tampilkan form
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $email]
        );
    }

    /**
     * Proses reset password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'string', 'min:8', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()],
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));

                // Langsung login otomatis setelah berhasil reset password
                Auth::login($user);
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect('/')->with('success', 'Password berhasil diatur! Anda telah otomatis masuk ke sistem.')
                    : back()->withErrors(['email' => [__($status)]]);
    }
}

