<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MetadataUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metadata_users', function (Blueprint $table) {
            $table->bigIncrements( 'id' );
            $table->bigInteger( 'user_id' )->index();
            $table->text( 'target_arn' )->nullable();
            $table->string( 'serial_number', 100 )->nullable()->unique()->index();
            $table->date( 'temp_ini' )->nullable()->index();
            $table->date( 'temp_out' )->nullable()->index();
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
        Schema::dropIfExists('metadata_users');
    }
}
