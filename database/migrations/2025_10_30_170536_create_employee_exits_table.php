<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_exits', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // keep it consistent with your other tables (INT UNSIGNED)
            $table->increments('id');

            // FK columns must exactly match the referenced type (INT UNSIGNED)
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('created_by')->nullable();

            $table->date('notice_date')->nullable();
            $table->date('effective_date')->nullable();
            $table->enum('kind', ['termination','resignation'])->index();
            $table->unsignedTinyInteger('termination_type')->nullable(); // 1..3 for termination
            $table->text('description')->nullable();

            $table->timestamps();

            // FKs — assuming you want to link to users.id
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['employee_id','kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_exits');
    }
};
