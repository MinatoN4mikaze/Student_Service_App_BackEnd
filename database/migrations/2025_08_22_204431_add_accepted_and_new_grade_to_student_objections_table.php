<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_objections', function (Blueprint $table) {
            $table->boolean('accepted')->default(false)->after('subject_year');
            $table->integer('new_grade')->nullable()->after('accepted');
        });
    }

    public function down(): void
    {
        Schema::table('student_objections', function (Blueprint $table) {
            $table->dropColumn(['accepted', 'new_grade']);
        });
    }
};
