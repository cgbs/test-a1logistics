<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrencyRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->unsignedInteger('bank_id')->default(1);
            $table->foreign('bank_id')->references('id')->on('banks');
            $table->unsignedInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currency');
            $table->decimal("rate",12,6);
            $table->timestamps();

            $table->index('date');
            $table->index('currency_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currency_rates');
    }
}
