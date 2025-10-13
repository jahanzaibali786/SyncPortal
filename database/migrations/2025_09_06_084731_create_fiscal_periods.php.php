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
        Schema::create('fiscal_periods', function (Blueprint $t){
            $t->id();
            $t->unsignedBigInteger('company_id')->index();
            $t->integer('year');
            $t->tinyInteger('month');
            $t->date('starts_on');
            $t->date('ends_on');
            $t->enum('status', ['OPEN','LOCKED','CLOSED'])->default('OPEN');
            $t->timestamps();
            $t->unique(['company_id','year','month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscal_periods');
    }
};
