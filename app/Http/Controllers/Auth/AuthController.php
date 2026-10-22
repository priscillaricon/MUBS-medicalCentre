<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // is_active => true means a deactivated account cannot log in
        if (! Auth::attempt($credentials + ['is_active' => true])) {
            return back()
                ->withErrors(['email' => 'Invalid email or password, or the account is deactivated.'])
                ->onlyInput('email');
        }

        // New session id after login prevents session fixation
        $request->session()->regenerate();

        // Send each role to its own dashboard (student.dashboard, doctor.dashboard, admin.dashboard)
        return redirect()->route(Auth::user()->dashboardRoute());
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:150', 'unique:users,email'],
            'reg_number' => ['required', 'string', 'max:30', 'unique:students,reg_number'],
            'course'     => ['required', 'string', 'max:120'],
            'gender'     => ['required', Rule::in(['Male', 'Female', 'Other'])],
            'phone'      => ['nullable', 'string', 'max:20'],
            'password'   => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        // Both rows are created together or not at all
        DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => 'student',   // public sign-up can ONLY ever create students
            ]);

            Student::create([
                'user_id'    => $user->id,
                'reg_number' => $data['reg_number'],
                'course'     => $data['course'],
                'gender'     => $data['gender'],
                'phone'      => $data['phone'] ?? null,
            ]);
        });

        return redirect()->route('login')->with('status', 'Registration successful. Please log in.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
