<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('multiple_choice','multi_select','scale','open_text','numeric','true_false') DEFAULT 'multiple_choice'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('multiple_choice','multi_select','scale','open_text','numeric') DEFAULT 'multiple_choice'");
    }
};
