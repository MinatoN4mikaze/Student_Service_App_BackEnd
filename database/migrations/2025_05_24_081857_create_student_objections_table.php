<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('student_objections', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('objection_id')->constrained()->onDelete('cascade');
    $table->unique(['user_id', 'objection_id']);
    $table->integer("grade");
    $table->string("lecturer_name");
    $table->string("test_hall");
    $table->string("subject_term");
    $table->string("subject_year");
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_objections');
    }
};
