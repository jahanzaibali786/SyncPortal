<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employee_transfers', function (Blueprint $table) {
            // Add new columns first (nullable to allow backfill)
            $table->unsignedInteger('from_department_id')->nullable()->after('employee_id');
            $table->unsignedInteger('to_department_id')->nullable()->after('from_department_id');
        });

        // Backfill: move old department_id -> to_department_id
        // (We can't reliably infer "from" for historical rows, so we leave it NULL.)
        DB::statement('UPDATE employee_transfers SET to_department_id = department_id WHERE to_department_id IS NULL');

        // Add FKs & indexes for the new columns
        Schema::table('employee_transfers', function (Blueprint $table) {
            $table->foreign('from_department_id')
                ->references('id')->on('teams')
                ->nullOnDelete();

            $table->foreign('to_department_id')
                ->references('id')->on('teams')
                ->nullOnDelete();

            // Old index: ['employee_id','transfer_date'] — keep it
            // Add new composite index for history
            $table->index(['employee_id', 'from_department_id', 'to_department_id'], 'emp_trans_emp_from_to_idx');
        });

        // Drop old FK & column
        Schema::table('employee_transfers', function (Blueprint $table) {
            // Drop FK on department_id if it exists
            try {
                $table->dropForeign(['department_id']);
            } catch (\Throwable $e) {
                // ignore if not present (some platforms need this guard)
            }

            if (Schema::hasColumn('employee_transfers', 'department_id')) {
                $table->dropColumn('department_id');
            }
        });
    }

    public function down(): void
    {
        // Recreate the old column
        Schema::table('employee_transfers', function (Blueprint $table) {
            $table->unsignedInteger('department_id')->nullable()->after('employee_id');
        });

        // Backfill department_id from to_department_id
        DB::statement('UPDATE employee_transfers SET department_id = to_department_id WHERE department_id IS NULL');

        // Restore FK
        Schema::table('employee_transfers', function (Blueprint $table) {
            $table->foreign('department_id')
                ->references('id')->on('teams')
                ->nullOnDelete();
        });

        // Drop new FKs / indexes and columns
        Schema::table('employee_transfers', function (Blueprint $table) {
            try { $table->dropForeign(['from_department_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['to_department_id']); } catch (\Throwable $e) {}

            if (Schema::hasColumn('employee_transfers', 'from_department_id')) {
                $table->dropColumn('from_department_id');
            }
            if (Schema::hasColumn('employee_transfers', 'to_department_id')) {
                $table->dropColumn('to_department_id');
            }

            try { $table->dropIndex('emp_trans_emp_from_to_idx'); } catch (\Throwable $e) {}
        });
    }
};
