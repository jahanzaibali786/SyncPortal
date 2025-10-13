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
        Schema::create('chart_of_account_sub_types', function (Blueprint $t){
            $t->id();
            $t->unsignedBigInteger('company_id')->index();
            $t->string('code', 20);
            $t->string('name');
            $t->timestamps();
            $t->unique(['company_id','code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_account_sub_types');
    }
};
