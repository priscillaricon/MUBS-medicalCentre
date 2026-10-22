<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SickSheet extends Model
{
    protected $fillable = [
        'appointment_id', 'student_id', 'doctor_id', 'reference_no', 'rest_from', 'rest_to', 'remarks',
    ];

    protected $casts = [
        'rest_from' => 'date',
        'rest_to' => 'date',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** Number of days off, counting both the first and last day. */
    public function getDaysAttribute(): int
    {
        return (int) $this->rest_from->diffInDays($this->rest_to) + 1;
    }

    /** Next letter reference, e.g. MC/2026/0142 */
    public static function nextReference(): string
    {
        return sprintf('MC/%d/%04d', now()->year, static::count() + 1);
    }
}
