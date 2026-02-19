<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAcademicFieldsToAuthorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('authors', function (Blueprint $table) {
            if (!Schema::hasColumn('authors', 'doctoral_degree')) {
                $table->boolean('doctoral_degree')->nullable()->comment('Has doctoral degree (0=No, 1=Yes)');
            }
            if (!Schema::hasColumn('authors', 'academic_ranks_en')) {
                $table->string('academic_ranks_en')->nullable()->comment('Academic rank in English (Professor, Associate Professor, etc.)');
            }
            if (!Schema::hasColumn('authors', 'academic_ranks_th')) {
                $table->string('academic_ranks_th')->nullable()->comment('Academic rank in Thai');
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
        Schema::table('authors', function (Blueprint $table) {
            $table->dropColumn('doctoral_degree');
            $table->dropColumn('academic_ranks_en');
            $table->dropColumn('academic_ranks_th');
        });
    }
}
