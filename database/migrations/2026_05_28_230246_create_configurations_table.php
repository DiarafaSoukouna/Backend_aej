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
       Schema::create('configurations', function (Blueprint $table) {
    $table->id();

    $table->string('logo_systeme')->nullable();
    $table->string('sigle_systeme');
    $table->string('intitule_systeme');

    $table->string('sigle_structure');
    $table->string('intitule_structure');
    $table->string('logo_structure')->nullable();

    $table->text('adresse_sociale_structure')->nullable();
    $table->string('email_structure');
    $table->string('whatsapp_structure');
    $table->string('telephone_structure');

    $table->string('sigle_monnaie_pays');
    $table->string('sigle_devise_principale');
    $table->decimal('taux_devise_principale', 10, 2);

    $table->boolean('mise_en_maintenance')->default(false);

    $table->integer('delai_inactivite_minutes');
    $table->integer('nombre_session_possible');
    $table->integer('nombre_tentatives_connexion');

    $table->integer('delai_code_tp_minutes');
    $table->integer('delai_changement_mdp_mois');
    $table->integer('delai_suppression_secondes');

    $table->string('code_instance_whatsapp')->nullable();
    $table->string('token_instance_whatsapp')->nullable();

    $table->string('email_notifications');
    $table->string('mot_de_passe_email_notifications');
    $table->string('smtp_email_notifications');
    $table->string('smtp_host_notifications');
    $table->integer('smtp_port_notifications')->default(587);
    $table->string('smtp_encrypt_notifications', 10)->default('tls');

    $table->timestamps();
});
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
