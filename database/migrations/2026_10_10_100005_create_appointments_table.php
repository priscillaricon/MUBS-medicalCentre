<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('category', 100)->default('General Consultation');
            $table->date('date');
            $table->time('time_slot');
            $table->enum('status', ['pending', 'approved', 'completed', 'cancelled'])->default('pending');
            $table->string('reason', 255)->nullable();

            // Double-booking guard that still lets a CANCELLED slot be rebooked:
            // active rows hold 1, cancelled rows hold NULL (the Appointment model sets this
            // automatically). MySQL treats NULLs as distinct, so only active rows must be unique.
            $table->boolean('slot_active')->nullable()->default(true);
            $table->unique(['doctor_id', 'date', 'time_slot', 'slot_active'], 'uq_active_slot');

            $table->index(['doctor_id', 'date']);
            $table->index('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
