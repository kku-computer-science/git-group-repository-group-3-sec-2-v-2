<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesForPerformance extends Migration
{
    public function up()
    {
        // Add indexes to error_logs table
        Schema::table('error_logs', function (Blueprint $table) {
            $table->index('level');
            $table->index('created_at');
            $table->index('user_id');
            $table->index('ip_address');
        });

        // Add indexes to security_events table
        Schema::table('security_events', function (Blueprint $table) {
            $table->index('event_type');
            $table->index('created_at');
            $table->index('user_id');
            $table->index('ip_address');
            $table->index('threat_level');
        });

        // Add indexes to activity_logs table
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('action_type');
            $table->index('created_at');
            $table->index('user_id');
        });

        // Add indexes to blocked_ips table
        Schema::table('blocked_ips', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('ip_address');
        });
    }

    public function down()
    {
        // Remove indexes from error_logs table
        Schema::table('error_logs', function (Blueprint $table) {
            $table->dropIndex(['level']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['ip_address']);
        });

        // Remove indexes from security_events table
        Schema::table('security_events', function (Blueprint $table) {
            $table->dropIndex(['event_type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['ip_address']);
            $table->dropIndex(['threat_level']);
        });

        // Remove indexes from activity_logs table
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['action_type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id']);
        });

        // Remove indexes from blocked_ips table
        Schema::table('blocked_ips', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['ip_address']);
        });
    }
} 