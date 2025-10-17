<?php  
  
use Illuminate\Database\Migrations\Migration;  
use Illuminate\Database\Schema\Blueprint;  
use Illuminate\Support\Facades\Schema;  
  
return new class extends Migration  
{  
    public function up(): void  
    {  
        Schema::create('meetings', function (Blueprint $table) {  
            $table->id();  
            $table->unsignedBigInteger('lead_id')->nullable();  
            $table->unsignedBigInteger('user_id')->nullable();  
            $table->string('name')->nullable()->after('user_id');
            $table->string('email')->nullable()->after('name');
            $table->date('date')->nullable()->after('email');
            $table->time('time')->nullable()->after('date');
            $table->integer('total_min')->nullable()->after('time');
            $table->string('meeting_id')->nullable()->after('total_min');
            $table->string('password')->nullable()->after('meeting_id');
            $table->text('start_url')->nullable()->after('password');
            $table->string('status', 50)->nullable()->after('join_url');
            $table->dateTime('end_time')->nullable()->after('status');
            $table->text('discuss')->nullable()->after('end_time');
            $table->unsignedBigInteger('created_by')->nullable()->after('discuss');
        });  
    }  
  
    public function down(): void  
    {  
        Schema::dropIfExists('meetings');  
    }  
};