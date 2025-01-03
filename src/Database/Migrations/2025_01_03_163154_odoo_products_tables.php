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
            $table->string('description_sale');
            $table->string('description_purchase');
            $table->string('description_picking');
            $table->string('description_internal');
            $table->string('description_variant');
            $table->string('description_invoice');
            $table->string('description_pickingout');
            $table->string('description_pickingin');
            $table->string('description_pickinginternal');
            $table->string('description_pickingoutin');
            $table->string('description_pickinginout');
            $table->string('description_pickinginternalout');
            $table->string('description_pickinginternalin');
            $table->string('description_pickinginternaloutin');
            $table->string('description_pickinginternalinout');
            $table->string('description_pickingoutinternal');
            $table->string('description_pickingininternal');
            $table->string('description_pickingoutinternalin');
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
