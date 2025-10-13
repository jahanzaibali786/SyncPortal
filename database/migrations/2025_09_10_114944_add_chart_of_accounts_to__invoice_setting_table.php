<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('receivable_account_id')->nullable()->after('company_id');
            $table->unsignedBigInteger('expense_payable_account_id')->nullable()->after('company_id');

            $table->foreign('receivable_account_id')
                  ->references('id')
                  ->on('chart_of_accounts')
                  ->onDelete('cascade');
            $table->foreign('expense_payable_account_id')
                  ->references('id')
                  ->on('chart_of_accounts')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->dropForeign(['receivable_account_id']);
            $table->dropColumn('receivable_account_id');
            $table->dropForeign(['expense_payable_account_id']);
            $table->dropColumn('expense_payable_account_id');
        });
    }
};
