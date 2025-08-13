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
    Schema::table('purchase_orders_data2', function (Blueprint $table) {
        $table->text('TEXT')->nullable(); // Tambahkan field TEXT
    });
}

public function down()
{
    Schema::table('purchase_orders_data2', function (Blueprint $table) {
        $table->dropColumn('TEXT'); // Hapus saat rollback
    });
}

};
