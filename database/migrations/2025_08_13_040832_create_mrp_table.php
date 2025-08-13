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
        Schema::create('mrp', function (Blueprint $table) {
            $table->id('id_mrp');
            $table->foreignId('id_kode')->constrained('kode','id_kode')->cascadeOnDelete();
            $table->string('mrp'); // nama kolom huruf kecil biar konsisten
            $table->timestamps();

            $table->unique(['id_kode','mrp']); // 1 kode tidak duplikat MRP
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mrp');
    }
};
