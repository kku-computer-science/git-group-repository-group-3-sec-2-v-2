<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalFieldsToErrorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('error_logs', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('line');
            $table->unsignedBigInteger('user_id')->nullable()->after('ip_address');
            $table->string('username')->nullable()->after('user_id');
            $table->string('url')->nullable()->after('username');
            $table->string('method', 10)->nullable()->after('url');
            $table->string('user_agent')->nullable()->after('method');
            
            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('error_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'ip_address',
                'user_id',
                'username',
                'url',
                'method',
                'user_agent'
            ]);
        });
    }
}
