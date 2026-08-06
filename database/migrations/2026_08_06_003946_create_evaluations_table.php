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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formulaire_id')->constrained('formulaire_evaluations')->onDelete('cascade');
            $table->string('cible_type', 50)->nullable();
            $table->foreignId('evaluateur_id')->constrained('personnels')->onDelete('cascade');
            $table->dateTime('date_evaluation');
            $table->decimal('score_global', 5, 2)->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
