<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyWorkOfResearchGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_of_research_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('author_id')->nullable()->after('research_group_id');
            $table->foreign('author_id')->references('id')->on('authors')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // เพิ่ม foreign key constraint กลับใหม่
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_of_research_groups', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropColumn('author_id');
            // ลบ foreign key constraint ที่เราเพิ่มไว้
            $table->dropForeign(['user_id']);

            // เปลี่ยนกลับให้ user_id ไม่ nullable
            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            // เพิ่ม foreign key constraint กลับใหม่
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }
}
