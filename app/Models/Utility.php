<?php

namespace App\Models;

use App\Models\Invoice;
use App\Models\InvoiceItems;
use App\Models\JournalVoucher;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class Utility
{
    /**
     * Create a JV voucher for an invoice and return the JV ID.
     */
    public static function createInvoiceJV(Invoice $invoice)
    {

        // return 45;
        DB::beginTransaction();
        try {
            $invoiceItems = $invoice->items;

            $totalIncome = 0;
            $totalTax = 0;
            $jvEntries = [];

            // Fetch receivable account from invoice settings
            $receivableAccount = InvoiceSetting::where('company_id', company()->id)
                ->value('receivable_account_id');

            foreach ($invoiceItems as $item) {
                dd($receivableAccount,$item,$item->product);
                $product = $item->product;
                $incomeAccount = $product->income_account;

                $itemTotal = $item->amount * $item->quantity;
                if ($item->discount > 0) {
                    $itemTotal -= $item->discount;
                }

                $totalIncome += $itemTotal;
                // dd($item,$item->taxData());
                // --- Handle multiple taxes ---
                if ($item->taxData && $item->taxData->count() > 0) {
                    // dd($item->taxData);
                    foreach ($item->taxData as $tax) {
                        $taxAmount = ($itemTotal * $tax->rate_percent / 100);
                        $totalTax += $taxAmount;

                        if ($tax->chart_of_account_id) {
                            $jvEntries[] = [
                                'account_id' => $tax->chart_of_account_id,
                                'debit' => 0,
                                'credit' => $taxAmount,
                                'source_id' => $item->id,
                                'source_line_type' => 'App\Models\InvoiceItems',
                                'tax_id' => $tax->id
                            ];
                        }
                    }
                }

                // dd($jvEntries);
                // Income entry
                $jvEntries[] = [
                    'account_id' => $incomeAccount,
                    'debit' => 0,
                    'credit' => $itemTotal,
                    'source_id' => $item->id,
                    'source_line_type' => 'App\Models\InvoiceItems',
                ];
            }

            // Debit Accounts Receivable
            if ($receivableAccount) {
                $jvEntries[] = [
                    'account_id' => $receivableAccount,
                    'debit' => $totalIncome + $totalTax,
                    'credit' => 0,
                ];
            }

            // Generate voucher number
            $response = Company::fetchNumber('JV');
            $data = json_decode($response->getContent(), true);
            $voucher_no = $data['number'];

            // Create Journal Entry
            $journalEntry = JournalEntry::create([
                'company_id' => company()->id,
                'date' => now()->format('Y-m-d'),
                'number' => $voucher_no,
                'voucher_type' => 'JV',
                'source_type' => 'App\Models\Invoice',
                'source_id' => $invoice->id,
                'status' => 'draft',
            ]);

            // Create JV lines
            foreach ($jvEntries as $entry) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'chart_of_account_id' => $entry['account_id'],
                    'debit' => $entry['debit'],
                    'credit' => $entry['credit'],
                    'company_id' => company()->id,
                    'source_line_type' => $entry['source_line_type'] ?? null,
                    'source_line_id' => $entry['source_id'] ?? null,
                    'tax_id' => $entry['tax_id'] ?? null, // optional
                ]);
            }

            DB::commit();
            return $journalEntry->id;

        } catch (\Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public static function createExpenseVoucher(Expense $expense)
    {

        $response = Company::fetchNumber('JV');
        $data = json_decode($response->getContent(), true);
        $voucher_no = $data['number'];
        $journalEntry = \App\Models\JournalEntry::create([
            'company_id' => company()->id,
            'date' => now(),
            'posted_at' => now(),
            'number' => $voucher_no,
            'memo' => 'Expense: ' . $expense->item_name,
            'voucher_type' => 'JV',
            'source_type' => 'App\Models\Expense',
            'source_id' => $expense->id,
            'created_by' => user()->id,
            'approved_by' => $expense->approver_id ?? null,
        ]);

        $expenseCategory = ExpensesCategory::find($expense->category_id);

        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'company_id' => company()->id,
            'chart_of_account_id' => $expenseCategory->chart_account_id,
            'debit' => $expense->price,
            'credit' => 0,
            'memo' => 'Expense: ' . $expense->item_name,
            'source_line_type' => 'App\Models\Expense',
            'source_line_id' => $expense->id,
        ]);


        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'company_id' => company()->id,
            'chart_of_account_id' => InvoiceSetting::where('company_id', company()->id)->value('expense_payable_account_id'),
            'debit' => 0,
            'credit' => $expense->price,
            'memo' => 'Expense Payable',
            'source_line_type' => 'App\Models\Expense',
            'source_line_id' => $expense->id,
        ]);

        return $journalEntry;
    }

    public static function createReceiveVoucher(Invoice $invoice)
    {
        $voucherType = $invoice->bankAccount->type == 'bank' ? 'BRV' : 'CRV';
        $response = Company::fetchNumber($voucherType);
        $data = json_decode($response->getContent(), true);
        $voucher_no = $data['number'];

        $journalEntry = JournalEntry::create([
            'company_id' => company()->id,
            'date' => now(),
            'number' => $voucher_no,
            'deposit_slip' => $invoice->bill ?? null,
            'posted_at' => now(),
            'bank_id' => $invoice->bank_account_id,
            'memo' => 'Receipt for Invoice: ' . $invoice->item_name,
            'voucher_type' => $voucherType,
            'source_type' => 'App\Models\Invoice',
            'source_id' => $invoice->id,
            'created_by' => user()->id,
            'approved_by' => $invoice->approver_id,
        ]);

        $RevicePayableAccountId = InvoiceSetting::where('company_id', company()->id)->value('receivable_account_id');

        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'company_id' => company()->id,
            'chart_of_account_id' => $RevicePayableAccountId,
            'debit' => $invoice->total,
            'credit' => 0,
            'memo' => 'Receipt for Invoice: ' . $invoice->item_name,
            'source_line_type' => 'App\Models\Invoice',
            'source_line_id' => $invoice->id,
        ]);

        $paymentAccountId = null;

        $bankAccount = BankAccount::find($invoice->bank_account_id);
        $paymentAccountId = $bankAccount->chart_account_id;
        if(!$paymentAccountId){
            // dd($bankAccount);
            return 'Receive account not found';
        }
    
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'company_id' => company()->id,
            'chart_of_account_id' => $paymentAccountId,
            'debit' => 0,
            'credit' => $invoice->total,
            'memo' => $voucherType == 'BRV' ? 'Bank Receipt' : 'Cash Receipt',
            'source_line_type' => 'App\Models\Invoice',
            'source_line_id' => $invoice->id,
        ]);
        // dd($journalEntry);
        return $journalEntry;
    }

    /**
     * Create payment voucher for expense (CPV or BPV)
     * 
     * @param Expense $expense
     * @return \App\Models\JournalEntry
     */
    public static function createPaymentVoucher(Expense $expense)
    {
        $voucherType = $expense->bankAccount->type == 'bank' ? 'BPV' : 'CPV';
        $response = Company::fetchNumber($voucherType);
        $data = json_decode($response->getContent(), true);
        $voucher_no = $data['number'];

        $journalEntry = JournalEntry::create([
            'company_id' => company()->id,
            'date' => now(),
            'number' => $voucher_no,
            'deposit_slip' => $expense->bill ?? null,
            'posted_at' => now(),
            'bank_id' => $expense->bank_account_id,
            'memo' => 'Payment for Expense: ' . $expense->item_name,
            'voucher_type' => $voucherType,
            'source_type' => 'App\Models\Expense',
            'source_id' => $expense->id,
            'created_by' => user()->id,
            'approved_by' => $expense->approver_id,
        ]);

        $expensePayableAccountId = InvoiceSetting::where('company_id', company()->id)->value('expense_payable_account_id');

        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'company_id' => company()->id,
            'chart_of_account_id' => $expensePayableAccountId,
            'debit' => $expense->price,
            'credit' => 0,
            'memo' => 'Payment for Expense: ' . $expense->item_name,
            'source_line_type' => 'App\Models\Expense',
            'source_line_id' => $expense->id,
        ]);

        $paymentAccountId = null;

        $bankAccount = BankAccount::find($expense->bank_account_id);
        $paymentAccountId = $bankAccount->chart_account_id;
        if(!$paymentAccountId){
            // dd($bankAccount);
            return 'Payment account not found';
        }
    
        JournalEntryLine::create([
            'journal_entry_id' => $journalEntry->id,
            'company_id' => company()->id,
            'chart_of_account_id' => $paymentAccountId,
            'debit' => 0,
            'credit' => $expense->price,
            'memo' => $voucherType == 'BPV' ? 'Bank Payment' : 'Cash Payment',
            'source_line_type' => 'App\Models\Expense',
            'source_line_id' => $expense->id,
        ]);

        return $journalEntry;
    }

}
