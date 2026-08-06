<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suivis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('micro_projet_id')->nullable();
            $table->unsignedBigInteger('promoteur_id')->nullable();
            $table->string('libelle', 100);
            $table->timestamps();
            
            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('cascade');
            $table->foreign('promoteur_id')->references('id')->on('promoteurs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suivis');
    }
};
