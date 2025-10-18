<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->string('sub_agents')->nullable()->after('agent_id')
                ->comment('Comma separated sub agent IDs');
        });
    }

    public function down()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn('sub_agents');
        });
    }
};
