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
        // 1. Ajouter les colonnes de suivi des rappels sur micro_projets si absentes
        Schema::table('micro_projets', function (Blueprint $table) {
            if (!Schema::hasColumn('micro_projets', 'compteur_rappels_impayes')) {
                $col = $table->integer('compteur_rappels_impayes')->default(0);
                if (Schema::hasColumn('micro_projets', 'synced_at')) {
                    $col->after('synced_at');
                }
            }
            if (!Schema::hasColumn('micro_projets', 'dernier_rappel_at')) {
                $col = $table->dateTime('dernier_rappel_at')->nullable();
                if (Schema::hasColumn('micro_projets', 'compteur_rappels_impayes')) {
                    $col->after('compteur_rappels_impayes');
                }
            }
        });

        // 2. Créer la table d'historique de chaque rappel et des suivis manuels
        Schema::create('rappel_historiques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('micro_projet_id');
            $table->unsignedBigInteger('promoteur_id')->nullable();
            $table->unsignedBigInteger('personnel_id')->nullable(); // Utilisateur/agent ayant saisi la ligne (null si système)
            
            // Type d'événement / historique : adaptable à de multiples réalités
            // Ex: RAPPEL_AUTOMATIQUE, PROMESSE_REGLEMENT, SUIVI_PROJET, APPEL_TELEPHONIQUE, VISITE_TERRAIN, NOTE, CONTENTIEUX, etc.
            $table->string('type')->default('RAPPEL_AUTOMATIQUE');

            // Canal utilisé : EMAIL, TELEPHONE, SUR_SITE, WHATSAPP, SMS, COURRIER, SYSTEME, etc.
            $table->string('canal')->nullable();

            // Numéro du rappel automatique (1 à 5, max 5)
            $table->integer('numero_rappel')->nullable();

            // Statut de l'action : ENVOYE, EFFECTUE, EN_ATTENTE, RESPECTE, NON_RESPECTE, ECHOUE, ANNULE
            $table->string('statut')->default('ENVOYE');

            // Titre synthétique
            $table->string('titre');

            // Description détaillée, remarques, constat terrain, propos du promoteur
            $table->text('description')->nullable();

            // Date de l'action / événement
            $table->dateTime('date_action')->useCurrent();

            // Cas d'une promesse de paiement : date fixée par le promoteur
            $table->date('date_promesse')->nullable();

            // Montant promis lors d'un échange
            $table->decimal('montant_promis', 18, 2)->nullable();

            // Montant total concerné / impayé au moment du rappel
            $table->decimal('montant_concerne', 18, 2)->nullable();

            // Détails des échéances concernées (tableau JSON : id, période, date_echeance, montant)
            $table->json('echeances_concernees')->nullable();

            // Email destinataire
            $table->string('destinataire_email')->nullable();

            // Chemin du document joint (rapport PDF généré ou pièce justificative déposée)
            $table->string('fichier_joint')->nullable();

            // Métadonnées libres sous forme de JSON pour une adaptabilité totale à toute nouvelle réalité
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Clés étrangères
            $table->foreign('micro_projet_id')->references('id')->on('micro_projets')->onDelete('cascade');
            $table->foreign('promoteur_id')->references('id')->on('promoteurs')->onDelete('set null');
            $table->foreign('personnel_id')->references('id')->on('personnels')->onDelete('set null');

            // Index
            $table->index(['micro_projet_id', 'type']);
            $table->index(['promoteur_id']);
            $table->index('date_action');
            $table->index('date_promesse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rappel_historiques');

        Schema::table('micro_projets', function (Blueprint $table) {
            $colsToDrop = [];
            if (Schema::hasColumn('micro_projets', 'compteur_rappels_impayes')) {
                $colsToDrop[] = 'compteur_rappels_impayes';
            }
            if (Schema::hasColumn('micro_projets', 'dernier_rappel_at')) {
                $colsToDrop[] = 'dernier_rappel_at';
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};

