<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_report_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personnel_id')->nullable()->comment('Utilisateur ayant déclenché le rapport');
            $table->enum('type_rapport', ['individual', 'global', 'bulletin', 'question'])->comment('Type de rapport généré');
            $table->unsignedBigInteger('sujet_id')->nullable()->comment('ID du jeune ou de l\'organisme concerné');
            $table->string('sujet_type')->nullable()->comment('Type de sujet: jeune, organisme');
            $table->text('question')->nullable()->comment('Question posée à l\'IA');
            $table->text('sql_genere')->nullable()->comment('SQL généré par l\'IA');
            $table->boolean('sql_valide')->nullable()->comment('SQL validé par SqlGuardService');
            $table->json('erreurs_validation')->nullable()->comment('Erreurs de validation SQL');
            $table->longText('reponse_ia')->nullable()->comment('Réponse texte de l\'IA');
            $table->string('pdf_path')->nullable()->comment('Chemin du PDF généré');
            $table->unsignedInteger('duree_ms')->nullable()->comment('Durée de la génération en ms');
            $table->string('modele_ia')->nullable()->default('claude-sonnet-4-5');
            $table->unsignedInteger('tokens_utilises')->nullable();
            $table->enum('statut', ['success', 'error', 'sql_rejected'])->default('success');
            $table->text('erreur_message')->nullable();
            $table->timestamps();

            $table->index('personnel_id');
            $table->index(['type_rapport', 'created_at']);
            $table->index(['sujet_id', 'sujet_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_report_logs');
    }
};

