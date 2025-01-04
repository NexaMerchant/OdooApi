<?php
namespace NexaMerchant\OdooApi\Helper;

class Odoo {

    protected $url;
    protected $db;
    protected $username;
    protected $password;
    protected $uid;
    protected $common;
    protected $models;
    protected $api_key;

    public function __construct()
    {
        // ...existing code...
        $this->url = config('OdooApi.host');
        $this->db = config('OdooApi.db');
        $this->username = config('OdooApi.username');
        $this->password = '';
        $this->api_key = config('OdooApi.api_key');

        $this->common = Ripcord::client("{$this->url}/xmlrpc/2/common");
        $this->uid = $this->auth();
        $this->models = Ripcord::client("{$this->url}/xmlrpc/2/object");
    }

    public function auth() {
        // ...existing code...
        return $this->common->authenticate($this->db, $this->username, $this->api_key, array());
    }

    public function getProducts()
    {
        // ...existing code...
        return $this->models->execute_kw($this->db, $this->uid, $this->api_key,
            'product.product', 'search_read',
            array(array(array('sale_ok', '=', true))),
            array('fields'=> array('name', 'list_price'), 'limit'=>10)
        );
    }

    public function getProduct($prod_id)
    {
        // ...existing code...
        $product = $this->models->execute_kw($this->db, $this->uid, $this->api_key,
            'product.product', 'search_read',
            array(array(array('id', '=', $prod_id))),
            // fields @link https://github.com/NexaMerchant/OdooClient/blob/main/docs/product.product.txt
            array('fields'=> array('name', 'list_price', 'description', 'product_tmpl_id', 'product_variant_ids'), 'limit'=>1)
        );
        // products variable is an array of products
        $product = $product[0];

        // get products options
        // product.template.attribute.value @link https://github.com/NexaMerchant/OdooClient/blob/main/docs/product.template.attribute.value.txt
        $product['options'] = $this->models->execute_kw($this->db, $this->uid, $this->api_key,
            'product.template.attribute.value', 'search_read',
            array(array(array('product_tmpl_id', '=', $product['product_tmpl_id'][0]))),
            array('fields'=> array('name', 'attribute_id'), 'limit'=>10)
        );



        return $product;
    }
}