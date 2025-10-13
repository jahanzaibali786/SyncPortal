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
        Schema::create('journal_entry_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $t->unsignedBigInteger('company_id')->index();
            $t->foreignId('chart_of_account_id')->constrained('chart_of_accounts');
            $t->decimal('debit', 20, 2)->default(0);
            $t->decimal('credit', 20, 2)->default(0);
            // Extra dimensions (optional for reporting/analysis)
            $t->unsignedBigInteger('tax_id')->nullable();
            $t->unsignedBigInteger('cost_center_id')->nullable();
            $t->unsignedBigInteger('project_id')->nullable();
            // Direct link back to the *source row/item*
            $t->nullableMorphs('source_line');  
            // Creates: source_line_type, source_line_id
            // Example: App\Models\InvoiceItem + 55
            // Works for invoice_items, expense_items, bill_items, etc.
            $t->string('memo')->nullable();
            $t->timestamps();
            
            // Short custom index name
            $t->index(['company_id','chart_of_account_id'], 'jel_tenant_account_idx');
            $t->index(['company_id','source_line_type','source_line_id'], 'jel_source_line_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
