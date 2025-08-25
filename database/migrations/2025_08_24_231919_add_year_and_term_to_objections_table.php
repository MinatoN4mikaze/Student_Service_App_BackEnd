<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('objections', function (Blueprint $table) {
            $table->string('subject_year')->after('subject_name');
            $table->string('subject_term')->after('subject_year');
        });
    }

    public function down(): void
    {
        Schema::table('objections', function (Blueprint $table) {
            $table->dropColumn(['subject_year', 'subject_term']);
        });
    }
};
