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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('subject');
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained('ticket_types');
            $table->foreignId('ticket_status_id')->constrained('ticket_statuses');
            $table->foreignId('assigned_operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('creator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('message');
            $table->timestamps();

            $table->index(['inbox_id', 'ticket_status_id']);
            $table->index(['assigned_operator_id']);
            $table->index(['entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
