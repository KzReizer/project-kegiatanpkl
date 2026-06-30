<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkl_journals', function (Blueprint $table) {
            $table->id();
            $table->date('activity_date')->index();
            $table->string('title');
            $table->text('description');
            $table->string('photo_path')->nullable();
            $table->string('photo_original_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkl_journals');
    }
};
