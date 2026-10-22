<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorManagementController extends Controller
{
    public function index(): View
    {
        $doctors = Doctor::with('user')->get()->sortBy('name')->values();

        return view('admin.manage-doctors', compact('doctors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'email'          => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'       => ['required', 'string', 'min:8'],
            'specialization' => ['required', 'string', 'max:100'],
            'room_number'    => ['nullable', 'string', 'max:20'],
            'phone'          => ['nullable', 'string', 'max:20'],
        ]);

        // User + doctor profile are created together or not at all
        DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => 'doctor',      // only an admin can create this role
            ]);

            Doctor::create([
                'user_id'        => $user->id,
                'specialization' => $data['specialization'],
                'room_number'    => $data['room_number'] ?? null,
                'phone'          => $data['phone'] ?? null,
            ]);
        });

        return back()->with('status', "Dr. {$data['name']} added. Give them the temporary password privately.");
    }

    /** Activate / deactivate. A deactivated doctor cannot log in and cannot be booked. */
    public function toggle(Doctor $doctor): RedirectResponse
    {
        $user = $doctor->user;
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', 'Dr. ' . $user->name . ($user->is_active ? ' is active again.' : ' has been deactivated.'));
    }
}
