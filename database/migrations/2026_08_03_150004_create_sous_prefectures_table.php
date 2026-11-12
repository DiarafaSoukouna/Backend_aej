<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sous_prefectures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('departement_id')->nullable();
            $table->string('code', 50)->unique()->nullable();
            $table->string('nom', 100);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('departement_id')->references('id')->on('departements')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sous_prefectures');
    }
};

