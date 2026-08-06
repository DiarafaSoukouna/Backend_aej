<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 100);
            $table->text('description')->nullable();
            $table->integer('niveau')->default(1);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on('categories_transactions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories_transactions');
    }
};
