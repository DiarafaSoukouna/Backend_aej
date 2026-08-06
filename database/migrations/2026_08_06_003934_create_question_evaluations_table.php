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
        Schema::create('question_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formulaire_id')->constrained('formulaire_evaluations')->onDelete('cascade');
            $table->string('code', 50);
            $table->text('libelle');
            $table->enum('type_question', ['number', 'select', 'text', 'textarea', 'date', 'boolean'])->nullable();
            $table->json('options')->nullable();
            $table->smallInteger('ordre')->default(0);
            $table->boolean('affichage')->nullable();
            $table->boolean('obligatoire')->default(true);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_evaluations');
    }
};
