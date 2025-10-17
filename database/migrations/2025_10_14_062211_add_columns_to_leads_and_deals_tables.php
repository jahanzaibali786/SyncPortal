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
        // Add columns to leads table
        Schema::table('leads', function (Blueprint $table) {
            $table->bigInteger('lead_id')->nullable()->after('id');
            $table->bigInteger('user_id')->nullable()->after('lead_id');
            $table->string('designation')->nullable()->after('client_email');
            $table->string('contact_person')->nullable()->after('designation');
        });

        // Add columns to deals table
        Schema::table('deals', function (Blueprint $table) {
            $table->string('labels')->nullable()->after('name');
            $table->string('subject')->nullable()->after('labels');
            $table->string('product')->nullable()->after('subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns if migration is rolled back
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['designation', 'contact_person']);
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn(['labels', 'product','subject']);
        });
    }
};
