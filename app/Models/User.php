<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    // NOTE: no 'hashed' cast on purpose. Always use Hash::make() when creating users.
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function isStudent(): bool { return $this->role === 'student'; }
    public function isDoctor(): bool  { return $this->role === 'doctor'; }
    public function isAdmin(): bool   { return $this->role === 'admin'; }

    /** Route name of this user's home page: student.dashboard / doctor.dashboard / admin.dashboard */
    public function dashboardRoute(): string
    {
        return $this->role . '.dashboard';
    }
}
