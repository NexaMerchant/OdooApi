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
        if (!Schema::hasTable('ba_odoo_orders')) {
            Schema::create('odoo_products', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('产品ID');
                $table->string('name', 191)->comment('产品名称');
                $table->string('product_id', 191)->comment('Odoo产品ID');
                $table->string('default_code', 191)->nullable()->comment('产品编码');
                $table->string('barcode', 191)->nullable()->comment('条形码');
                $table->string('type', 191)->nullable()->comment('产品类型');
                $table->string('list_price', 191)->nullable()->comment('销售价格');
                $table->string('standard_price', 191)->nullable()->comment('标准价格');
                $table->string('cost', 191)->nullable()->comment('成本');
                $table->string('qty_available', 191)->nullable()->comment('可用数量');
                $table->string('uom_id', 191)->nullable()->comment('单位ID');
                $table->string('categ_id', 191)->nullable()->comment('分类ID');
                $table->string('taxes_id', 191)->comment('税率ID');
                $table->string('description', 191)->comment('产品描述');
                $table->string('seo_name', 191)->comment('SEO名称');
                $table->string('currency_id', 191)->comment('货币ID');
                $table->text('values')->comment('其他属性值');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odoo_products');
    }
};
