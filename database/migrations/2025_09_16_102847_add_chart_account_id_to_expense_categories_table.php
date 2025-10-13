<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses_category', function (Blueprint $table) {
            // Step 1: Add column (nullable at first to avoid FK violation)
            $table->unsignedBigInteger('chart_account_id')->nullable()->after('category_name');
        });

        // Step 2: Update existing rows to 1
        DB::table('expenses_category')->update(['chart_account_id' => 1]);

        Schema::table('expenses_category', function (Blueprint $table) {
            // Step 3: Make column NOT NULL with default 1
            $table->unsignedBigInteger('chart_account_id')->default(1)->change();

            // Step 4: Add foreign key
            $table->foreign('chart_account_id')
                  ->references('id')->on('chart_of_accounts')
                  ->onDelete('cascade'); // or set null, depending on your need
        });


        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('journal_id')->nullable()->after('company_id');

            $table->foreign('journal_id')
                  ->references('id')->on('journals')
                  ->onDelete('set null');
        });
        //add chart_account_id in 	bank_accounts table
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('chart_account_id')->nullable()->after('account_number');
        });
        DB::table('bank_accounts')->update(['chart_account_id' => 1]);
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('chart_account_id')->default(1)->change();
        });
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->foreign('chart_account_id')
                  ->references('id')->on('chart_of_accounts')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses_category', function (Blueprint $table) {
            $table->dropForeign(['chart_account_id']);
            $table->dropColumn('chart_account_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['journal_id']);
            $table->dropColumn('journal_id');
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['chart_account_id']);
            $table->dropColumn('chart_account_id');
        });
    }
};
