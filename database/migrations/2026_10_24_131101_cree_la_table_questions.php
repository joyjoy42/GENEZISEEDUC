<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('libelle');
            $table->string('dimension', 20);
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('est_active')->default(true);
            $table->timestamps();

            $table->index(['est_active', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
