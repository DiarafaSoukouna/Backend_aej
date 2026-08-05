<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_etapes_deliverable', function (Blueprint $table) {
            $table->id();
            $table->string('etape_code');
            $table->string('deliverable_code');
            $table->boolean('is_required')->default(true);
            
            $table->foreign('etape_code')->references('code')->on('workflow_etapes')->onDelete('cascade');
            $table->foreign('deliverable_code')->references('code')->on('workflow_deliverables')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_etapes_deliverable');
    }
};
