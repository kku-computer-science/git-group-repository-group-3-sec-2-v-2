<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduledCommandsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scheduled_commands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('command')->unique();
            $table->text('description')->nullable();
            $table->string('cron_expression', 100);
            $table->string('timezone', 100)->default('Asia/Bangkok');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['is_enabled', 'display_order']);
        });

        Schema::create('scheduled_command_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_command_id')->constrained('scheduled_commands')->onDelete('cascade');
            $table->string('executed_command');
            $table->string('status', 20)->default('running');
            $table->integer('exit_code')->nullable();
            $table->longText('output')->nullable();
            $table->text('error_message')->nullable();
            $table->string('ran_via', 50)->default('scheduler');
            $table->json('meta')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['scheduled_command_id', 'started_at']);
            $table->index(['status', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scheduled_command_runs');
        Schema::dropIfExists('scheduled_commands');
    }
}
