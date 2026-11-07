<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rasporeds', function (Blueprint $table) {
            $table->id();
            $table->string('naziv');
            $table->enum('tip', ['nastava', 'testiranje']);
            $table->foreignId('user_id')->constrained();
            $table->string('fajl_raspored');
            $table->string('fajl_dani')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rasporeds');
    }
};
