<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date')->index();
            
            // Check-in data
            $table->time('check_in_time')->nullable();
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->string('check_in_device')->nullable();
            
            // Check-out data
            $table->time('check_out_time')->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->string('check_out_device')->nullable();
            
            // Status & notes
            $table->enum('status', ['hadir', 'terlambat', 'sakit', 'izin', 'alpha'])->default('alpha');
            $table->text('notes')->nullable();
            $table->string('sick_letter')->nullable(); // path untuk surat sakit
            
            $table->timestamps();
            
            // Index untuk query cepat
            $table->index(['user_id', 'attendance_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
