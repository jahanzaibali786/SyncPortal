<?php

// database/migrations/2025_11_01_000000_create_employee_transfers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_transfers', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');

            // Employee (maps to users.id)
            $table->unsignedInteger('employee_id');

            // New department for the employee (no branches in your schema)
            $table->unsignedInteger('department_id')->nullable();

            $table->date('transfer_date');
            $table->string('description', 191)->nullable();

            // Who recorded the transfer
            $table->unsignedInteger('created_by')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['employee_id', 'transfer_date']);

            // FKs
            $table->foreign('employee_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('department_id')
                ->references('id')->on('teams')   // <- same table employee_details.department_id uses
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_transfers');
    }
};
