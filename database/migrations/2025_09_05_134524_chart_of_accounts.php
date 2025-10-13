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
        Schema::create('chart_of_accounts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->index();
            $t->string('code', 20)->index();
            $t->string('name');
            $t->foreignId('chart_of_account_type_id')->constrained('chart_of_account_types');
            $t->foreignId('chart_of_account_sub_type_id')->constrained('chart_of_account_sub_types');
            $t->foreignId('parent_id')->nullable()->constrained('chart_of_accounts');
            $t->string('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['company_id','code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
