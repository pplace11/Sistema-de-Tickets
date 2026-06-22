<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entity_user', function (Blueprint $table) {
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['entity_id', 'user_id']);
        });

        DB::table('users')
            ->whereNotNull('entity_id')
            ->orderBy('id')
            ->chunkById(200, function ($users): void {
                $rows = [];

                foreach ($users as $user) {
                    $rows[] = [
                        'entity_id' => (int) $user->entity_id,
                        'user_id' => (int) $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (! empty($rows)) {
                    DB::table('entity_user')->insertOrIgnore($rows);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_user');
    }
};
