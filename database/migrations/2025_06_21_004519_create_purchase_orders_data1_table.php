<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('purchase_orders_data1', function (Blueprint $table) {
            $table->id();
            $table->string('BADAT')->nullable();  
            $table->string('BEDAT')->nullable();  
            $table->string('EBELN')->unique()->nullable();  
            $table->string('FRGCO')->nullable(); 
            $table->string('KRYW')->nullable();
            $table->string('NAME1')->nullable();  
            $table->string('STATS')->nullable();  
            $table->string('TOTPR')->nullable();  
            $table->string('WAERK')->nullable();  
            $table->string('WEEK')->nullable();  
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
        Schema::dropIfExists('purchase_orders_data1');
    }
};
