<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_categories_etapes', function (Blueprint $table) {
            $table->id();
            $table->string('etape_code', 50);
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('etape_code')->references('code')->on('workflow_etapes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_categories_etapes');
    }
};
