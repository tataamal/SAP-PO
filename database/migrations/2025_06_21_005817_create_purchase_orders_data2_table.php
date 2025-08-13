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
        Schema::create('purchase_orders_data2', function (Blueprint $table) {
            $table->id();
            $table->string('MANDT')->nullable();
            $table->string('STATS')->nullable();
            $table->string('WEEK')->nullable();
            $table->date('BADAT')->nullable();   // Tanggal Dokumen
            $table->date('BEDAT')->nullable();   // Tanggal Permintaan
            $table->string('EBELN')->unique()->nullable(); // Nomor PO
            $table->string('EBELP')->nullable(); // Item PO
            $table->string('FRGCO')->nullable(); // Release Code
            $table->string('MATNR')->nullable(); // Material Number
            $table->string('MATTX')->nullable(); // Material Description
            $table->string('MENGE')->nullable(); // Kuantitas
            $table->string('MEINS')->nullable(); // Satuan
            $table->string('NETPR')->nullable(); // Harga/Net Price
            $table->string('NETWR')->nullable(); // Net Value
            $table->string('TAX')->nullable();   // Pajak
            $table->string('NAME1')->nullable(); // Vendor Name
            $table->string('TOTPR')->nullable(); // Total Price
            $table->string('WAERK')->nullable(); // Currency
            $table->string('KRYW')->nullable();  // Nama Pemroses
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
        Schema::dropIfExists('purchase_orders_data2');
    }
};
