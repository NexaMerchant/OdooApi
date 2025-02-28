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
        // $this->url = config('OdooApi.host');
        // if($this->url=="http://localhost") {
        //     return throw new \Exception("Please config ODOO_HOST in env", 1);
            
        // }
        // $this->db = config('OdooApi.db');
        // $this->username = config('OdooApi.username');
        // $this->password = '';
        // $this->api_key = config('OdooApi.api_key');

        // $this->common = Ripcord::client("{$this->url}/xmlrpc/2/common");
        // $this->uid = $this->auth();
        // $this->models = Ripcord::client("{$this->url}/xmlrpc/2/object");
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


    public function getProduct($prod_id, $is_product_variant = False)
    {
        try {


            $domain = [
                ['id', '=', (int)$prod_id]
            ];
            $fields = [
                'id',
                'name',
                'list_price',
                'description',
                'product_tmpl_id',
                'product_variant_ids',
                'is_product_variant'
            ];
            $limit = 1;

            $product = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'product.product',
                'search_read',
                [$domain],
                ['fields' => $fields, 'limit' => $limit]
            );

            $product = $product ? $product[0] : false;

            return $product;
        } catch (\Exception $e) {
            return $e;
        }
    }


    public function getVariant($variant_id)
    {
        try {
            $domain = [
                ['id', '=', (int)$variant_id]
            ];
            $fields = [
                'id',
                'name',
                'product_id',
                'product_tmpl_id',
                'attribute_value_ids',
            ];
            $limit = 1;

            $variant = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'product.product',
                'search_read',
                [$domain],
                ['fields' => $fields, 'limit' => $limit]
            );

            return $variant ? $variant[0] : false;

        } catch (\Exception $e) {
            return $e;
        }
    }


    public function get_product_variant($variant_id)
    {

       

        try {
            $domain = [['id', '=', (int)$variant_id]];
            $fields = [
                'id',
                'name',
                // 'product_id',
                'product_tmpl_id',
                // 'attribute_value_ids',
            ];
            $limit = 1;

                // 使用 search 方法获取记录的 ID
            $variant_ids = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'product.product',
                'search',
                [$domain],
                ['limit' => $limit]
            );


            var_dump($variant_ids);exit;


            // 使用 read 方法读取记录
            $variant = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'product.product',
                'read',
                [$variant_ids],
                ['fields' => $fields]
            );



            

            return $variant ? $variant[0] : false;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getOrder($order_id) {
        try {
            $domain = [
                ['id', '=', (int)$order_id]
            ];
            $fields = [
                'id',
                'name',
                'partner_id',
            ];
            $limit = 1;

            $order = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'sale.order',
                'search_read',
                [$domain],
                ['fields' => $fields, 'limit' => $limit]
            );

            return $order ? $order[0] : false;



        }catch (\Exception $e) {
            return $e;
        }
    }


    public function createOrder($order_data)
    {
        try {
            $order_id = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'sale.order',
                'create',
                [$order_data]
            );


          
            return $order_id;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function createCustomer($customer_data)
    {
        try {
            $customer_id = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'res.partner',
                'create',
                [$customer_data]
            );

            return $customer_id;
        } catch (\Exception $e) {
            return $e;
        }
    }



    public function createOrderLine($order_line_data)
    {


        // print_r($order_line_data);exit;

        try {
            $order_line_id = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'sale.order.line',
                'create',
                [$order_line_data]
            );

            return $order_line_id;
        } catch (\Exception $e) {
            return $e;
        }
    }
    
    public function createAttribute($attribute_data)
    {
        try {
            $attribute_id = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'product.attribute',
                'create',
                [$attribute_data]
            );

            return $attribute_id;
        } catch (\Exception $e) {
            return $e;
        }
    }


    public function searchAttributeId($attribute_name)
    {
        try {
            $domain = [
                [
                    'name', '=', $attribute_name
                   
                ],
            
            ];
            $fields = [
                'id',
                'name',
            ];
            $limit = 1;

            $attribute = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'product.attribute',
                'search_read',
                [$domain],
                ['fields'=> $fields,'limit' => $limit]
            );

            return $attribute ? $attribute[0] : false;

        } catch (\Exception $e) {
            return $e;
        }
    }



    public function createAttributeValue($attribute_value_data)
    {
        try {
            $attribute_value_id = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'product.attribute.value',
                'create',
                [$attribute_value_data]
            );

            return $attribute_value_id;
        } catch (\Exception $e) {
            return $e;
        }
    }


    public function searchAttributeValueId($attribute_value_name, $attribute_id)
    {

        try {
            $domain = [
                [
                    'name', '=', $attribute_value_name,
                    // 'attribute_id', '=', $attribute_id
                    
                    ]
            ];
            $fields = [
                'id',
                'name',
            ];
            $limit = 1;

            $attribute_value = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'product.attribute.value',
                'search_read',
                [$domain],
                ['fields'=> $fields,'limit' => $limit]
            );
  

            return $attribute_value ? $attribute_value[0] : false;

        } catch (\Exception $e) {
            return $e;
        }
    }


    public function searchCountry($country_name)
    {
    
        try {
            $domain = [
                ['code', '=', $country_name]
            ];
            $fields = [
                'id',
                'name',
                'code',
            ];
            $limit = 1;

            $country = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'res.country',
                'search_read',
                [$domain],
                ['fields'=> $fields,'limit' => $limit]
            );

            return $country ? $country[0] : false;

        } catch (\Exception $e) {
            return $e;
        }
    }


    public function stateSearch($state_name)
    {
        try {
            $domain = [
                ['name', '=', $state_name]
            ];
            $fields = [
                'id',
                'name',
                'code',
            ];
            $limit = 1;

            $state = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'res.country.state',
                'search_read',
                [$domain],
                ['fields'=> $fields,'limit' => $limit]
            );


            var_dump($state);exit;


            return $state ? $state[0] : false;

        } catch (\Exception $e) {
            return $e;
        }
    }


    public function createProduct($product_data)
    {
        try {
            $product_id = $this->models->execute_kw(
                $this->db,
                $this->uid,
                $this->api_key,
                'product.product',
                'create',
                [$product_data]
            );

            return $product_id;
        } catch (\Exception $e) {
            return $e;
        }
    }


}