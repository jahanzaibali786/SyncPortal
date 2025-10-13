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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('income_account')->after('description');
            $table->unsignedBigInteger('expense_account')->after('income_account');

            // Add foreign key constraints if ChartOfAccount table exists
            $table->foreign('income_account')->references('id')->on('chart_of_accounts');
            $table->foreign('expense_account')->references('id')->on('chart_of_accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['income_account']);
            $table->dropForeign(['expense_account']);
            $table->dropColumn(['income_account', 'expense_account']);
        });
    }
};
