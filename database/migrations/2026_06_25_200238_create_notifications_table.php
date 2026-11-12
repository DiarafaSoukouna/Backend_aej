<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->string('titre', 200);
            $table->text('message');
            $table->boolean('lue')->default(false);
            $table->dateTime('created_at');

            $table->foreign('personnel_id')->references('id')->on('personnels')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

