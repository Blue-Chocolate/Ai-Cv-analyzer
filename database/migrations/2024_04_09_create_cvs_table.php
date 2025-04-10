<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cvs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path');
            $table->text('summary');
            $table->integer('experience_years')->default(0);
            $table->integer('skill_score')->default(0);
            $table->integer('soft_skills')->default(0);
            $table->integer('education_score')->default(0);
            $table->integer('relevant_experience')->default(0);
            $table->integer('fit_score')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cvs');
    }
};