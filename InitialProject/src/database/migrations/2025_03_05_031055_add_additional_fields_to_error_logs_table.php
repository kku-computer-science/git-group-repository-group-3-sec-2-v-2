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
            if (!Schema::hasColumn('error_logs', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('line');
            }
            if (!Schema::hasColumn('error_logs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('error_logs', 'username')) {
                $table->string('username')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('error_logs', 'url')) {
                $table->string('url')->nullable()->after('username');
            }
            if (!Schema::hasColumn('error_logs', 'method')) {
                $table->string('method', 10)->nullable()->after('url');
            }
            if (!Schema::hasColumn('error_logs', 'user_agent')) {
                $table->string('user_agent')->nullable()->after('method');
            }
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
