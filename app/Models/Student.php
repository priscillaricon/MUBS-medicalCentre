<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = ['user_id', 'reg_number', 'course', 'gender', 'phone', 'date_of_birth'];

    protected $casts = ['date_of_birth' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function sickSheets(): HasMany
    {
        return $this->hasMany(SickSheet::class);
    }

    // Lets views write {{ $student->name }} instead of {{ $student->user->name }}
    public function getNameAttribute(): string
    {
        return $this->user->name ?? '';
    }
}
