<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sick_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('reference_no', 30)->unique();   // e.g. MC/2026/0142
            $table->date('rest_from');
            $table->date('rest_to');
            $table->text('remarks')->nullable();             // diagnosis is deliberately NOT stored here
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sick_sheets');
    }
};
