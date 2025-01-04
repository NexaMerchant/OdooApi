<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('odoo_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('default_code');
            $table->string('barcode');
            $table->string('type');
            $table->string('list_price');
            $table->string('standard_price');
            $table->string('cost');
            $table->string('qty_available');
            $table->string('uom_id');
            $table->string('categ_id');
            $table->string('taxes_id');
            $table->string('description');
            $table->string('seo_name');
            $table->string('currency_id');
            $table->timestamps();


        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('odoo_products');
    }
};
