<?php

namespace App\Http\Controllers;

use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('frontend.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة'])->withInput();
    }

    public function showRegister(): View
    {
        return view('frontend.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|unique:users',
            'country_code' => 'required|string|size:2',
            'state_code' => 'nullable|string|max:5',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $countries = config('ecommerce.countries', []);
        $dial = $countries[$data['country_code']]['dial_code'] ?? '';
        $fullPhone = $dial.$data['phone'];

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $fullPhone,
            'country_code' => $data['country_code'],
            'state_code' => $data['state_code'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'customer',
            'status' => 'active',
        ]);

        $customerRole = Role::where('name', 'customer')->first();
        if ($customerRole) {
            $user->roles()->attach($customerRole);
        }

        Auth::login($user);

        return redirect()->route('home')->with('success', 'تم التسجيل بنجاح');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
