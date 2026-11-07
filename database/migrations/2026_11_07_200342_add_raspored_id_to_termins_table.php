<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('termins', function (Blueprint $table) {
            $table->foreignId('raspored_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->dropColumn('bulk_uid');
        });
    }

    public function down(): void
    {
        Schema::table('termins', function (Blueprint $table) {
            $table->string('bulk_uid')->nullable();
            $table->dropConstrainedForeignId('raspored_id');
        });
    }
};
