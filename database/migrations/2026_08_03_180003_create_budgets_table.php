<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('micro_projet_id')->unique();
            $table->string('intitule', 100);
            $table->decimal('montant_accorde', 15, 2);
            $table->date('date_accord')->nullable();
            $table->string('source', 100)->nullable();
            $table->enum('statut', ['EN_ATTENTE', 'APPROUVE', 'NON_APPROUVE'])->default('EN_ATTENTE');
            $table->string('devise', 10)->default('FCFA');
            $table->enum('deblocage', ['OUI', 'NON'])->default('NON');
            $table->date('date_deblocage')->nullable();
            $table->enum('signature_convention', ['SIGNEE', 'NON_SIGNEE'])->default('NON_SIGNEE');
            $table->date('date_signature')->nullable();
            $table->enum('reception_acte_credit', ['OUI', 'NON', 'PARTIEL'])->default('NON');
            $table->date('date_reception')->nullable();
            $table->text('observations')->nullable();
            $table->unsignedBigInteger('valide_par')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            
            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('cascade');
            $table->foreign('valide_par')->references('id')->on('personnels');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
