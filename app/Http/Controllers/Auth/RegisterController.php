<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $divisions = Division::all();
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
                'regex:/(@angkasapura\.co\.id|@gmail\.com)$/' // Domain Restriction (Updated: Allow Gmail)
            ],
            'password' => 'required|string|min:8|confirmed',
            'division_id' => 'required|exists:divisions,id',
        ], [
            'email.regex' => 'Gunakan email resmi (@angkasapura.co.id) atau akun Gmail.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user', // Hardcoded Role
            'division_id' => $request->division_id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('user.tickets.index');
    }
}
