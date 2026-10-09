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
        Schema::disableForeignKeyConstraints();

        Schema::create('termins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ucionica_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->string('naziv');
            $table->text('opis')->nullable();
            $table->date('datum');
            $table->time('vreme_pocetka');
            $table->time('vreme_kraja');
            $table->string('bulk_uid')->nullable();
            $table->enum('status', ["potvrdjen","odbijen","ceka"])->default('ceka');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('termins');
    }
};
