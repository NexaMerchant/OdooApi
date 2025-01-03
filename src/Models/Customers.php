<?php

namespace NexaMerchant\OdooApi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    use HasFactory;

    protected $table = 'odoo_customers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'mobile',
        'street',
        'street2',
        'city',
        'zip',
        'state_id',
        'country_id',
        'vat',
        'function',
        'title',
        'company_id',
        'category_id',
        'user_id',
        'team_id',
        'lang',
        'tz',
        'active',
        'customer',
        'supplier',
        'employee',
        'partner_share',
        'is_company',
        'customer_rank',
        'supplier_rank',
        'company_type',
        'commercial_partner_id',
        'color',
        'partner_gid',
        'ref',
        'opt_out',
        'signup_type',
        'signup_expiration',
        'signup_token',
        'signup_url',
        'signup_valid',
        'signup_type',
        'signup_expiration',
    ];

    public function getCustomers()
    {
        return $this->all();
    }

    public function getCustomer($id)
    {
        return $this->find($id);
    }
    
}
    