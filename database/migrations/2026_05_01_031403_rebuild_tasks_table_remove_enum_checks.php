<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF;');

        Schema::dropIfExists('tasks_stage2_temp');

        Schema::create('tasks_stage2_temp', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();

            $table->string('title');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->text('observations')->nullable();

            $table->date('due_date');
            $table->time('due_time')->nullable();

            // Se cambian de enum a string para permitir:
            // prioridad: critica, alta, media, baja
            // estado: pendiente, en_proceso, completada, reprogramada, cancelada
            $table->string('priority')->default('media');
            $table->string('status')->default('pendiente');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->text('reschedule_reason')->nullable();
            $table->timestamp('rescheduled_at')->nullable();

            $table->text('cancel_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });

        DB::statement("
            INSERT INTO tasks_stage2_temp (
                id,
                user_id,
                assigned_by,
                category_id,
                title,
                location,
                description,
                observations,
                due_date,
                due_time,
                priority,
                status,
                started_at,
                completed_at,
                reschedule_reason,
                rescheduled_at,
                cancel_reason,
                cancelled_at,
                created_at,
                updated_at
            )
            SELECT
                id,
                user_id,
                assigned_by,
                category_id,
                title,
                location,
                description,
                observations,
                due_date,
                due_time,
                priority,
                status,
                started_at,
                completed_at,
                reschedule_reason,
                rescheduled_at,
                cancel_reason,
                cancelled_at,
                created_at,
                updated_at
            FROM tasks
        ");

        Schema::drop('tasks');

        Schema::rename('tasks_stage2_temp', 'tasks');

        DB::statement('PRAGMA foreign_keys=ON;');
    }

    public function down(): void
    {
        // No se revierte automáticamente porque podría haber datos con:
        // prioridad = critica
        // estado = reprogramada / cancelada
        // y esos valores no existían en la tabla original.
    }
};