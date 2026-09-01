<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compte_financements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('micro_projet_id')->nullable();
            $table->unsignedBigInteger('organisme_id')->nullable();
            $table->unsignedBigInteger('budget_id')->nullable()->unique();
            $table->enum('etat_ouverture', ['OUVERT', 'FERME', 'NON_OUVERT'])->default('NON_OUVERT');
            $table->enum('avis_partenaire', ['ACCORDE', 'AJOURNE', 'REJETE'])->nullable();
            $table->decimal('montant_accorde', 15, 2)->nullable();
            $table->integer('duree_pret')->nullable();
            $table->integer('duree_remboursement')->nullable();
            $table->decimal('taux_interet', 5, 2)->nullable();
            $table->date('date_ouverture')->nullable();
            $table->string('lieu_ouverture', 100)->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('cascade');
            $table->foreign('organisme_id')->references('id')->on('organisme_financements')->onDelete('cascade');
            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compte_financements');
    }
};
