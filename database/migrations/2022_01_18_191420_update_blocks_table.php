<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blocks', function(Blueprint $table) {
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('period_id')->constrained('periods');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blocks', function(Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['period_id']);

            $table->dropColumn('subject_id');
            $table->dropColumn('period_id');
        });
    }
}
