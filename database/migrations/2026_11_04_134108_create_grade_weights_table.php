<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mapel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('assignment_weight')->default(30);
            $table->unsignedTinyInteger('uts_weight')->default(30);
            $table->unsignedTinyInteger('uas_weight')->default(40);
            $table->timestamps();

            $table->unique(['mapel_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_weights');
    }
};
