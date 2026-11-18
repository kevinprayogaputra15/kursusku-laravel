<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('course_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('status', 20)->default('registered');
            $table->timestamps();

            // Satu participant hanya boleh terdaftar sekali pada course yang sama.
            $table->unique(['participant_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
