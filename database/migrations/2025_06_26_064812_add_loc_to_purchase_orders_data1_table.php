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
    Schema::table('purchase_orders_data1', function (Blueprint $table) {
        $table->string('LOC')->nullable()->after('WEEK'); // letakkan di akhir
    });
}

public function down()
{
    Schema::table('purchase_orders_data1', function (Blueprint $table) {
        $table->dropColumn('LOC');
    });
}

};
