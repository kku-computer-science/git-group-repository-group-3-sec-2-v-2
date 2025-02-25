<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCanEditToWorkOfResearchGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('work_of_research_groups', 'can_edit')) {
            Schema::table('work_of_research_groups', function (Blueprint $table) {
                $table->boolean('can_edit')->default(0);
            });
        }
    }

    public function down()
    {
        Schema::table('work_of_research_groups', function (Blueprint $table) {
            $table->dropColumn('can_edit');
        });
    }
    
}
