<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deal_histories', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('label_id')->nullable()->after('proposal_id');
            $table->unsignedBigInteger('call_id')->nullable()->after('label_id');
            $table->foreign('call_id')->references('id')->on('lead_calls')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deal_histories', function (Blueprint $table) {
            //
        });
    }
};
