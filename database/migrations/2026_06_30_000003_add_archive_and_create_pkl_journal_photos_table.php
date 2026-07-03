<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pkl_journals', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('updated_at');
        });

        Schema::create('pkl_journal_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkl_journal_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('pkl_journals')
            ->whereNotNull('photo_path')
            ->orderBy('id')
            ->get()
            ->each(function (object $journal): void {
                DB::table('pkl_journal_photos')->insert([
                    'pkl_journal_id' => $journal->id,
                    'path' => $journal->photo_path,
                    'original_name' => $journal->photo_original_name,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkl_journal_photos');

        Schema::table('pkl_journals', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
    }
};
