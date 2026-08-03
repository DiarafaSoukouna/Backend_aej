<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_instance_comment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_instance_id');
            $table->string('etape_code', 50);
            $table->unsignedBigInteger('commented_by_id')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('workflow_instance_id')->references('id')->on('workflow_instance')->onDelete('cascade');
            $table->foreign('etape_code')->references('code')->on('workflow_etapes');
            $table->foreign('commented_by_id')->references('id')->on('personnels');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_instance_comment');
    }
};
