<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruiter_id')->constrained()->onDelete('cascade');
            $table->foreignId('timeline_id')->constrained()->onDelete('cascade');
            $table->foreignId('step_category_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Each step category can only appear once per timeline.
            $table->unique(['timeline_id', 'step_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('steps');
    }
};
