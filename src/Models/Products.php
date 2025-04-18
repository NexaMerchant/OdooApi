<?php

namespace NexaMerchant\OdooApi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $table = 'odoo_products';

    protected $fillable = [
        'name',
        'default_code',
        'barcode',
        'type',
        'list_price',
        'standard_price',
        'cost',
        'qty_available',
        'uom_id',
        'categ_id',
        'taxes_id',
        'values',
        'description',
        'description_sale',
        'description_purchase',
        'description_picking',
        'description_internal',
        'description_variant',
        'description_invoice',
        'description_pickingout',
        'description_pickingin',
        'description_pickinginternal',
        'description_pickingoutin',
        'description_pickinginout',
        'description_pickinginternalout',
        'description_pickinginternalin',
        'description_pickinginternaloutin',
        'description_pickinginternalinout',
        'description_pickingoutinternal',
        'description_pickingininternal',
        'description_pickingoutinternalin',
    ];

    protected $casts = [
        'values' => 'array',
        'taxes_id' => 'array',
        'categ_id' => 'array',
        'uom_id' => 'array',
    ];

    public function getProducts()
    {
        return $this->all();
    }

    public function getProduct($id)
    {
        return $this->find($id);
    }
}