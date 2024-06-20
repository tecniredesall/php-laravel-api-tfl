<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('id');
	        $table->string('report_name')->nullable();
            $table->string('field_report')->nullable();
            $table->string('select_params')->nullable();
            $table->string('main_table')->nullable();
            $table->string('inner_params')->nullable();
            $table->string('where_params')->nullable();
            $table->string('input_params')->nullable();
            $table->string('order_params')->nullable();
            $table->string('group_params')->nullable();
            $table->string('group_by')->nullable();
            $table->string('group_by_select')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
