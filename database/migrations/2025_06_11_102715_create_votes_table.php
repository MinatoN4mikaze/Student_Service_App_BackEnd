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
        if (!Schema::hasTable('votes')) {
    Schema::create('votes', function (Blueprint $table) {
    $table->id();
    // الطالب الذي صوّت
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    // الخيار الذي تم اختياره
    $table->foreignId('poll_option_id')->constrained()->onDelete('cascade');
    $table->timestamps();
    // منع التصويت مرتين لنفس الخيار
    $table->unique(['user_id', 'poll_id']);
});
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
