<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    public const STATUSES = ['pending', 'approved', 'completed', 'cancelled'];

    protected $fillable = ['student_id', 'doctor_id', 'category', 'date', 'time_slot', 'status', 'reason'];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'slot_active' => 'boolean',
    ];

    /**
     * Keeps the DB double-booking guard in sync: active appointments hold 1,
     * cancelled ones hold NULL so their slot can be booked again.
     * (Runs on ->save()/->update() on a model. A raw query-builder update would skip it.)
     */
    protected static function booted(): void
    {
        static::saving(function (Appointment $appointment) {
            $appointment->slot_active = $appointment->status === 'cancelled' ? null : true;
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function sickSheet(): HasOne
    {
        return $this->hasOne(SickSheet::class);
    }

    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /** Students may only cancel/reschedule appointments that haven't happened yet. */
    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'approved'], true);
    }
}
