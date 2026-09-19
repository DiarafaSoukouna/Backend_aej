<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_remboursements', function (Blueprint $table) {
            $table->date('date_ouverture')->nullable()->after('budget_id');
            $table->enum('decision', ['EN_ATTENTE', 'APPROUVE', 'NON_APPROUVE'])->default('EN_ATTENTE')->after('date_ouverture');
            $table->decimal('montant_credit', 18, 2)->nullable()->after('decision');
            $table->decimal('interets', 18, 2)->nullable()->after('montant_credit');
            $table->integer('duree_pret')->nullable()->after('interets');
            $table->integer('duree_remboursement')->nullable()->after('duree_pret');
            $table->text('fichier_amortissement')->nullable()->after('duree_remboursement');
            $table->text('fichier_convention')->nullable()->after('fichier_amortissement');
        });

        $legacyColumns = array_values(array_filter(
            ['echeance_mensuelle', 'montant_echeance', 'justificatif_path'],
            fn (string $column) => Schema::hasColumn('plan_remboursements', $column)
        ));

        if ($legacyColumns !== []) {
            Schema::table('plan_remboursements', function (Blueprint $table) use ($legacyColumns) {
                $table->dropColumn($legacyColumns);
            });
        }

        Schema::create('tableau_amortissements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_remboursement_id')->nullable();
            $table->integer('periode')->nullable();
            $table->date('date_echeance')->nullable();
            $table->decimal('montant_echeance', 18, 2)->nullable();
            $table->decimal('capital_rembourse', 18, 2)->nullable();
            $table->decimal('capital_restant', 18, 2)->nullable();
            $table->decimal('interets', 18, 2)->nullable();
            $table->decimal('amortissement_capital', 18, 2)->nullable();
            $table->enum('statut', ['PAYE', 'PARTIEL', 'NON_PAYE'])->default('NON_PAYE');
            $table->timestamps();

            $table->foreign('plan_remboursement_id')->references('id')->on('plan_remboursements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tableau_amortissements');

        Schema::table('plan_remboursements', function (Blueprint $table) {
            $table->dropColumn([
                'date_ouverture',
                'decision',
                'montant_credit',
                'interets',
                'duree_pret',
                'duree_remboursement',
                'fichier_amortissement',
                'fichier_convention',
            ]);
        });

        Schema::table('plan_remboursements', function (Blueprint $table) {
            $table->date('echeance_mensuelle')->nullable();
            $table->decimal('montant_echeance', 18, 2)->nullable();
            $table->text('justificatif_path')->nullable();
        });
    }
};
