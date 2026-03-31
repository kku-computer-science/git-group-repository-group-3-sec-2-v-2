<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddIndexesForPerformance extends Migration
{
    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Safely add an index only if the column exists and the index doesn't already exist.
     */
    private function addIndexIfNeeded(string $table, string $column): void
    {
        $indexName = "{$table}_{$column}_index";
        if (Schema::hasColumn($table, $column) && !$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->index($column);
            });
        }
    }

    public function up()
    {
        // Add indexes to error_logs table
        if (Schema::hasTable('error_logs')) {
            $this->addIndexIfNeeded('error_logs', 'level');
            $this->addIndexIfNeeded('error_logs', 'created_at');
            $this->addIndexIfNeeded('error_logs', 'user_id');
            $this->addIndexIfNeeded('error_logs', 'ip_address');
        }

        // Add indexes to security_events table
        if (Schema::hasTable('security_events')) {
            $this->addIndexIfNeeded('security_events', 'event_type');
            $this->addIndexIfNeeded('security_events', 'created_at');
            $this->addIndexIfNeeded('security_events', 'user_id');
            $this->addIndexIfNeeded('security_events', 'ip_address');
            $this->addIndexIfNeeded('security_events', 'threat_level');
        }

        // Add indexes to activity_logs table
        if (Schema::hasTable('activity_logs')) {
            $this->addIndexIfNeeded('activity_logs', 'action_type');
            $this->addIndexIfNeeded('activity_logs', 'created_at');
            $this->addIndexIfNeeded('activity_logs', 'user_id');
        }

        // Add indexes to blocked_ips table
        if (Schema::hasTable('blocked_ips')) {
            $this->addIndexIfNeeded('blocked_ips', 'created_at');
            $this->addIndexIfNeeded('blocked_ips', 'ip_address');
        }
    }

    public function down()
    {
        // Remove indexes from error_logs table
        if (Schema::hasTable('error_logs')) {
            Schema::table('error_logs', function (Blueprint $table) {
                if ($this->indexExists('error_logs', 'error_logs_level_index')) $table->dropIndex(['level']);
                if ($this->indexExists('error_logs', 'error_logs_created_at_index')) $table->dropIndex(['created_at']);
                if ($this->indexExists('error_logs', 'error_logs_user_id_index')) $table->dropIndex(['user_id']);
                if ($this->indexExists('error_logs', 'error_logs_ip_address_index')) $table->dropIndex(['ip_address']);
            });
        }

        // Remove indexes from security_events table
        if (Schema::hasTable('security_events')) {
            Schema::table('security_events', function (Blueprint $table) {
                if ($this->indexExists('security_events', 'security_events_event_type_index')) $table->dropIndex(['event_type']);
                if ($this->indexExists('security_events', 'security_events_created_at_index')) $table->dropIndex(['created_at']);
                if ($this->indexExists('security_events', 'security_events_user_id_index')) $table->dropIndex(['user_id']);
                if ($this->indexExists('security_events', 'security_events_ip_address_index')) $table->dropIndex(['ip_address']);
                if ($this->indexExists('security_events', 'security_events_threat_level_index')) $table->dropIndex(['threat_level']);
            });
        }

        // Remove indexes from activity_logs table
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                if ($this->indexExists('activity_logs', 'activity_logs_action_type_index')) $table->dropIndex(['action_type']);
                if ($this->indexExists('activity_logs', 'activity_logs_created_at_index')) $table->dropIndex(['created_at']);
                if ($this->indexExists('activity_logs', 'activity_logs_user_id_index')) $table->dropIndex(['user_id']);
            });
        }

        // Remove indexes from blocked_ips table
        if (Schema::hasTable('blocked_ips')) {
            Schema::table('blocked_ips', function (Blueprint $table) {
                if ($this->indexExists('blocked_ips', 'blocked_ips_created_at_index')) $table->dropIndex(['created_at']);
                if ($this->indexExists('blocked_ips', 'blocked_ips_ip_address_index')) $table->dropIndex(['ip_address']);
            });
        }
    }
} 