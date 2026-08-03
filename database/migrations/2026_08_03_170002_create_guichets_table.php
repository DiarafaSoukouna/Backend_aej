<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guichets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 100);
            $table->text('description')->nullable();
            $table->string('couleur', 7)->nullable();
            $table->decimal('montant_min', 15, 2)->default(0);
            $table->decimal('montant_max', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_form_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guichets');
    }
};
