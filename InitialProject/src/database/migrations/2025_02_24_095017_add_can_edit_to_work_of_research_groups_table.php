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
        Schema::table('work_of_research_groups', function (Blueprint $table) {
            $table->boolean('can_edit')->default(false);
        });
    }
    
    public function down()
    {
        Schema::table('work_of_research_groups', function (Blueprint $table) {
            $table->dropColumn('can_edit');
        });
    }
    
}
