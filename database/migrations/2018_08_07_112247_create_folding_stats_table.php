<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFoldingStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folding_stats', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('member_id')->unsigned()->index();
            
            $table->integer('points')->default(0);
            $table->integer('work_units')->default(0);

            $table->timestamp('start_date');
            $table->tinyInteger('period_type');

            $table->foreign('member_id')->references('id')->on('folding_members')->onDelete('cascade');

            $table->index(['start_date', 'period_type']);
            $table->unique(['start_date', 'member_id', 'period_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('folding_stats');
    }
}
