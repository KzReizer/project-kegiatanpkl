<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pkl_journals', function (Blueprint $table) {
            $table->string('location')->nullable()->after('title');
            $table->string('category')->default('Kegiatan')->after('location');
            $table->text('learning')->nullable()->after('description');
            $table->text('obstacle')->nullable()->after('learning');
            $table->text('next_plan')->nullable()->after('obstacle');
        });
    }

    public function down(): void
    {
        Schema::table('pkl_journals', function (Blueprint $table) {
            $table->dropColumn([
                'location',
                'category',
                'learning',
                'obstacle',
                'next_plan',
            ]);
        });
    }
};
