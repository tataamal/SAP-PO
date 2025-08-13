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
        Schema::create('kode', function (Blueprint $table) {
            $table->id('id_kode');
            $table->foreignId('user_sap_id')->constrained('user_sap')->cascadeOnDelete();
            $table->string('kode');
            $table->timestamps();

            $table->unique(['user_sap_id','kode']); // 1 user_sap tidak duplikat kode
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kode');
    }
};
