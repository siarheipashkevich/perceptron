<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('data', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('input')->default('');
            $table->string('hidden')->default('');
            $table->string('output')->default('');
            $table->json('weights')->default('');
            $table->json('weightsSum')->default('');
            $table->json('valuesY')->default('');
            $table->json('images')->default('');
            $table->json('compounds')->default('');
            $table->string('title')->default('');
            $table->timestamps(false);
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('data');
	}

}
