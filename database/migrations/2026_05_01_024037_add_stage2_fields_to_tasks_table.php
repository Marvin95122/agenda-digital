<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->text('observations')->nullable();
            $table->text('reschedule_reason')->nullable();
            $table->timestamp('rescheduled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'observations',
                'reschedule_reason',
                'rescheduled_at',
                'cancel_reason',
                'cancelled_at',
            ]);
        });
    }
};