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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['operator', 'client'])->default('client')->after('email');
            $table->unsignedBigInteger('entity_id')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('entity_id');

            $table->index('role');
            $table->index('entity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['entity_id']);
            $table->dropColumn(['role', 'entity_id', 'is_active']);
        });
    }
};
