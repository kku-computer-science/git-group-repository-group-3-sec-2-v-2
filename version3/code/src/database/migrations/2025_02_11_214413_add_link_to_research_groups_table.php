<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLinkToResearchGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('research_groups', function (Blueprint $table) {
            $table->string('link')->nullable()->after('group_image'); // เพิ่มคอลัมน์ link
        });
    }
    
    public function down()
    {
        Schema::table('research_groups', function (Blueprint $table) {
            $table->dropColumn('link');
        });
    }
    
}
