<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets_remboursements', function (Blueprint $table) {
            $table->unsignedBigInteger('budget_id')->primary();
            $table->decimal('montant_remboursement', 18, 2)->nullable();
            $table->decimal('montant_garantie', 18, 2)->nullable();
            $table->decimal('montant_recouvrement', 18, 2)->nullable();
            $table->integer('dure_remboursement')->nullable();
            $table->integer('dure_differe')->nullable();
            $table->date('date_premiere_echeance')->nullable();
            $table->date('date_derniere_cheance')->nullable();
            $table->decimal('echeance_rembourse', 18, 2)->nullable();
            $table->enum('restructuration_pret', ['OUI', 'NON'])->default('NON');
            $table->decimal('capital_amorti', 18, 2)->default(0);
            $table->decimal('interets', 18, 2)->default(0);
            
            $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets_remboursements');
    }
};
