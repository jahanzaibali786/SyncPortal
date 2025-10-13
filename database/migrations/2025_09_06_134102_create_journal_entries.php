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
        Schema::create('journal_entries', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->index();  
            $t->date('date')->index();
            $t->string('number', 50)->index();
            $t->enum('voucher_type', ['JV','CPV','BPV','CRV','BRV'])->index();
            $t->string('memo')->nullable();
            $t->nullableMorphs('source'); // source_type, source_id
            $t->unsignedBigInteger('bank_id')->nullable();
            $t->unsignedBigInteger('tax_id')->nullable();
            $t->enum('status', ['draft','posted','voided'])->default('draft')->index();
            $t->foreignId('reversed_entry_id')->nullable()->constrained('journal_entries');
            $t->string('payment_method', 100)->nullable(); // cash, bank, card, etc.
            $t->string('check_number', 100)->nullable();   // for CPV/ BPV
            $t->string('bank_reference', 150)->nullable(); // for BPV
            $t->string('deposit_slip', 150)->nullable();   // for BRV/CRV
            $t->string('cashier_info', 150)->nullable();   // who received cash
            $t->timestamp('posted_at')->nullable();
            $t->timestamp('voided_at')->nullable();
            $t->unsignedInteger('created_by')->nullable();
            $t->unsignedInteger('approved_by')->nullable();

            //bankid
            $t->foreign('bank_id')->references('id')->on('bank_accounts')->nullOnDelete();
            $t->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $t->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $t->foreignId('fiscal_period_id')->nullable()->constrained('fiscal_periods');
            $t->timestamps();
            $t->softDeletes();
            $t->index(['company_id','date']);
            $t->index(['company_id','source_type','source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
