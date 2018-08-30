<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFoldingMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folding_members', function (Blueprint $table) {
            $table->increments('id');

            $table->string('user_name', 80)->index();

            $table->string('friendly_name', 80);
            $table->string('friendly_name_lc', 80);

            $table->string('bitcoin_address')->index();
            $table->integer('team_id')->unsigned()->index();
            $table->string('team_number', 16); // denormalized for efficiency

            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('folding_teams')->onDelete('cascade');
            $table->unique(['user_name', 'team_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('folding_members');
    }
}


//         {
//             "userName": "otherdude_ALL_1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j",
//             "friendlyName": "Other Dude",
//             "bitcoinAddress": "1AAAA1111xxxxxxxxxxxxxxxxxxy43CZ9j",
//             "teamNumber": 1234567890,
//             "pointsGained": 1,
//             "workUnitsGained": 1
//         },
