<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Student;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'students'  => Student::count(),
            'doctors'   => Doctor::count(),
            'pending'   => Appointment::where('status', 'pending')->count(),
            'completed' => Appointment::where('status', 'completed')->count(),
            'today'     => Appointment::whereDate('date', today())->where('status', '!=', 'cancelled')->count(),
        ];

        // One query for all the per-doctor numbers
        $doctorStats = Doctor::with('user')->withCount([
            'appointments as total_count',
            'appointments as completed_count' => fn ($q) => $q->where('status', 'completed'),
            'appointments as pending_count'   => fn ($q) => $q->where('status', 'pending'),
        ])->get()->sortByDesc('total_count')->values();

        $recentStudents = Student::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'doctorStats', 'recentStudents'));
    }
}
