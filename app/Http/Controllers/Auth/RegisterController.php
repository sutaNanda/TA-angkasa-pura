<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Daftar divisi hardcoded — tidak butuh tabel database.
     * Cukup update array ini jika ada perubahan struktur organisasi.
     */
    private const DIVISIONS = [
        'IT',
        'HR & GA',
        'Finance',
        'Operations',
        'Commercial',
        'Technical',
        'Security',
    ];

    public function showRegistrationForm()
    {
        $divisions = self::DIVISIONS;
        return view('auth.register', compact('divisions'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'regex:/(@angkasapura\.co\.id|@gmail\.com)$/',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            // Validasi division sebagai string pilihan, bukan FK
            'division'  => 'nullable|string|in:' . implode(',', self::DIVISIONS),
        ], [
            'email.regex'    => 'Gunakan email resmi (@angkasapura.co.id) atau akun Gmail.',
            'division.in'    => 'Divisi yang dipilih tidak valid.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user', // Default role untuk registrasi mandiri
            'division' => $request->division,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('user.tickets.index');
    }
}
