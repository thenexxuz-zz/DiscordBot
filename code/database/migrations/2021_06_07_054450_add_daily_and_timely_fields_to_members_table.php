<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDailyAndTimelyFieldsToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->addColumn('date', 'lastDaily')->nullable(true);
            $table->addColumn('date', 'lastTimely')->nullable(true);
            $table->addColumn('integer', 'streakTimely')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->removeColumn('lastDaily');
            $table->removeColumn('lastTimely');
            $table->removeColumn('streakTimely');
        });
    }
}
