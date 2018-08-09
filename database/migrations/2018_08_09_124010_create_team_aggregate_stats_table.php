<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamAggregateStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_aggregate_stats', function (Blueprint $table) {
            $table->integer('number')->unique();
            $table->string('name')->index();

            $table->integer('all_points')->default(0)->index();
            $table->integer('all_work_units')->default(0)->index();

            $table->integer('week_points')->default(0)->index();
            $table->integer('week_work_units')->default(0)->index();
            
            $table->integer('day_points')->default(0)->index();
            $table->integer('day_work_units')->default(0)->index();

            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_aggregate_stats');
    }
}
