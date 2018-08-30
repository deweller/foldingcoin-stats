<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberAggregateStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_aggregate_stats', function (Blueprint $table) {
            $table->string('user_name', 80)->unique();
            $table->string('friendly_name')->index();
            $table->string('friendly_name_lc')->index();
            $table->string('bitcoin_address')->index();
            
            $table->bigInteger('all_points')->default(0)->index();
            $table->bigInteger('all_work_units')->default(0)->index();

            $table->bigInteger('week_points')->default(0)->index();
            $table->bigInteger('week_work_units')->default(0)->index();
            
            $table->bigInteger('day_points')->default(0)->index();
            $table->bigInteger('day_work_units')->default(0)->index();

            $table->integer('all_rank')->unsigned()->nullable()->unique();
            $table->integer('week_rank')->unsigned()->nullable()->unique();
            $table->integer('day_rank')->unsigned()->nullable()->unique();

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
        Schema::dropIfExists('member_aggregate_stats');
    }
}
