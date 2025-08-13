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
        Schema::create('user_sap', function (Blueprint $table) {
            $table->id();                         // PK lokal
            $table->string('id_sap')->unique();   // ID SAP dari sistem SAP
            $table->string('sap_username');       // bisa dipakai sebagai "Nama User" tampilan
            $table->string('sap_password')->nullable(); // sebaiknya NULL & tidak dipakai
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
        Schema::dropIfExists('user_sap');
    }
};
