<?php

namespace NexaMerchant\OdooApi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $table = 'odoo_orders';

    protected $fillable = [
        'name',
        'partner_id',
        'partner_invoice_id',
        'partner_shipping_id',
        'pricelist_id',
        'currency_id',
        'payment_term_id',
        'team_id',
        'user_id',
        'company_id',
        'warehouse_id',
        'client_order_ref',
        'origin',
        'date_order',
        'validity_date',
        'require_signature',
        'require_payment',
        'require_shipment',
        'require_invoice',
        'state',
        'invoice_status',
        'picking_policy',
        'picking_policy',
        'note',
        'amount_untaxed',
        'amount_tax',
        'amount_total',
        'amount_delivery',
        'amount_discount',
        'amount_rounding',
        'amount_paid',
        'amount_residual',
        'amount_tax',
        'amount_total_company_signed',
        'amount_total_signed',
        'amount_untaxed_signed',
        'amount_tax_signed',
        'amount_delivery_signed',
        'amount_discount_signed',
        'amount_rounding_signed',
        'amount_paid_signed',
        'amount_residual_signed',
        'amount_tax_signed',
        'amount_total_company_signed',
    ];

    public function getOrders()
    {
        return $this->all();
    }

    public function getOrder($id)
    {
        return $this->find($id);
    }
}