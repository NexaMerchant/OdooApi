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
        Schema::create('odoo_orders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('partner_id');
            $table->string('partner_invoice_id');
            $table->string('partner_shipping_id');
            $table->string('pricelist_id');
            $table->string('currency_id');
            $table->string('payment_term_id');
            $table->string('team_id');
            $table->string('user_id');
            $table->string('company_id');
            $table->string('warehouse_id');
            $table->string('client_order_ref');
            $table->string('origin');
            $table->string('date_order');
            $table->string('validity_date');
            $table->string('require_signature');
            $table->string('require_payment');
            $table->string('require_shipment');
            $table->string('require_invoice');
            $table->string('state');
            $table->string('invoice_status');
            $table->string('picking_policy');
            $table->string('picking_policy');
            $table->string('note');
            $table->string('amount_untaxed');
            $table->string('amount_tax');
            $table->string('amount_total');
            $table->string('amount_delivery');
            $table->string('amount_discount');
            $table->string('amount_rounding');
            $table->string('amount_paid');
            $table->string('amount_residual');
            $table->string('amount_tax');
            $table->string('amount_total_company_signed');
            $table->string('amount_total_signed');
            $table->string('amount_untaxed_signed');
            $table->string('amount_tax_signed');
            $table->string('amount_delivery_signed');
            $table->string('amount_discount_signed');
            $table->string('amount_rounding_signed');
            $table->string('amount_paid_signed');
            $table->string('amount_residual_signed');
            $table->string('amount_tax_signed');
            $table->string('amount_total_company_signed');
            $table->string('amount_total_signed');
            $table->string('amount_untaxed_signed');
            $table->string('amount_tax_signed');
            $table->string('amount_delivery_signed');
            $table->string('amount_discount_signed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('odoo_orders');
    }
};
