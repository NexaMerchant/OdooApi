<?php
namespace NexaMerchant\OdooApi\Console\Commands\Orders;

use Illuminate\Console\Command;
use NexaMerchant\Apis\Docs\V1\Admin\Models\Catalog\Product;
use Webkul\Sales\Models\Order;
use NexaMerchant\OdooApi\Helper\Odoo;
use Webkul\Product\Repositories\ProductRepository;

class Sync extends Command
{
    protected $signature = 'odoo:orders:sync {--order-id=}';

    protected $description = 'Sync orders from the main database to the shop database odoo:orders:sync {--order-id=}';

    protected $Order;
    protected $Odoo;

    protected $productRepository;

    public function __construct(
    )
    {
        $this->Order = new Order();
        $this->Odoo = new Odoo();
        $this->Odoo->init();
        $this->productRepository = app(ProductRepository::class);
        //$this->customerRepository = app(CustomerRepository::class);
        parent::__construct();
    }

    public function handle()
    {
        // $this->info('Syncing orders...');

        $order_id = $this->option('order-id');

        // var_dump($order_id);
        // find this order in db
        $order = $this->Order->findOrFail($order_id);
        $orderPayment = $order->payment;  

        $shipping_address = $order->shipping_address;//发货信息
        $products = $order->items;//商品信息


    //   $res = $this->Odoo->get_product_variant(12638);
        // var_dump($products);exit;


        //$products = $order->items->toArray();

        $products = $order->items->toArray();

        // print_r($products);exit;


        foreach($products as $key=>$value) {

            $sku = $value['additional'];
            $product_id = $sku['product_id'];

            $product = $this->productRepository->find($product_id);

            // $super_attribute = $product->super_attributes;

           

            $productViewHelper = new \Webkul\Product\Helpers\ConfigurableOption();
            $attributes = $productViewHelper->getConfigurationConfig($product);

            //  print_r($product);exit;

            // $options = [];
            // foreach($attributes as $k=>$v){
            //  $options = [
            //      'attribute_id' => $v['attribute_id'],
            //      'option_id' => $v['option_id']
            //  ];
            // }
 
             $product_variants = $product->variants->toArray();
             // $product['variants'] = $variants;
 
 

            // foreach($product_variants as $variant){

            // }

            // print_r($product_id);exit;

            // $product = $this->productRepository->find($product_id)->toArray();

            foreach($product_variants as $variant){


               // print_r($variant['id']);exit;
                $res = $this->Odoo->get_product_variant($variant['id']);
                print_r($res);exit;
            }



             //汇总attribute数据 start
            $option_data = [];
            foreach($attributes['attributes'] as $k=>$v){
                $option['name'] = $v['label'];
                $option['value'] = [];
                foreach($v['options'] as $key=>$value){
                    $option['value'][] = $value['label'];
                }

                $option_data[] = $option;
            }
            $res = $this->create_attribute($option_data);
            print_r($option_data);exit;
            //汇总attribute数据 end


            //汇总图片数据 start
            $image_data = [];
            foreach($attributes['variant_images'] as $m=>$n){

                $image = [];
                foreach($n as $x=>$y){
                    $image['src'] = $y['small_image_url'];
                    $image['id'] = $m;
                    $image['position'] = 1;
                    $image['variant_ids'] = $value['additional']['selected_configurable_option'];
                }

                $image_data[] = $image;
            }
            print_r($image_data);exit;
            //汇总图片数据 end
         



        
        }


        print_r($product);exit;

        $product = $this->productRepository->find($product[0]['product_id']);

        

        $variants = $product->variants;

        var_dump($attributes);exit;




        print_r($products);exit;

        $q_ty = 0;
        $line_items = [];

        // print_r($products);exit;

        foreach($products as $key=>$product) {
            $sku = $product['additional'];

            $line_item = [];
            $line_item['variant_id'] = $sku['selected_configurable_option'];
            $line_item ['order_id'] = $order_id;
            $line_item['product_id'] = $sku['product_id'];
            $line_item ['product_uom_qty'] = $product['qty_ordered'];
            $line_item ['price_unit'] = $product['price'];
            // $q_ty += $product['qty_ordered'];
            // $line_item ['currency_id'] = getenv('USA_CURRENCY');
            $line_item ['name'] = $product->name;
           

            array_push($line_items, $line_item);
        }

        // print_r($line_items);exit;


     

// 
        // var_dump($res);exit;

    //    $res = $this->Odoo->getOrder($order_id);
    //    var_dump($res);exit;

       



        $customer_data = [
            'name'=>  $shipping_address->first_name .$shipping_address->last_name,
            'email'=> $shipping_address->email,
            'phone'=> $shipping_address->phone,
            'street'=>$shipping_address->address2,
            #'street2'=> order['shipping_address']['address2'][0],
            'city'=>$shipping_address->city,
            'zip'=>$shipping_address->postcode,
            'country_code'=> $shipping_address->country,
            // 'state_id'=> $shipping_address->state,
            'state_id'=> 1,
            // 'country_id'=> country_id[0],
            'website_id'=>  getenv('USA_WAREHOUSE_ID'),
            'lang'=> getenv('USA_LANG'),
            'category_id'=> [8],
            'type'=> 'delivery',
        # 'category_id'=> 8,
        ];

       

        $order_data = [
        
            'partner_id'=> $order['customer_id'],
            'origin'=>  $order['id'],
            'date_order'=> '2021-06-01',
            'website_id'=> 5,
            'state'=> 'sale',
            'create_date'=>date('Y-m-d H:i:s', strtotime($order['created_at'])),
            'invoice_status'=> 'to invoice',
            "currency_id"=> $order['order_currency_code'],
            'amount_total'=>$order['grand_total'],
            'amount_tax'=>$order['tax_amount'],
            'name'=>getenv('USA_ORDER_PREFIX').'-'.$order['id'],
            'warehouse_id'=> getenv('USA_WAREHOUSE_ID'),
            'company_id'=> 5,

            # 'payment_term_id'=> 1,
            # 'order_line'=> order_lines,
                # 'partner_invoice_id'=> int(customer_id),
                # 'partner_shipping_id'=> int(customer_id),
                # 'picking_policy'=> 'direct',
                # 'pricelist_id'=> 1,
                # 'company_id'=> 1,
                # 'warehouse_id'=> 1,
              #  'note'=> order['note'], 
               #  'warehouse_id'=> 1,


            ];




            $product = [
              
                    'name'=> product_data['name'],
                    'description'=> product_data['description'],
                    'list_price'=> product_data['price'],
                    'compare_list_price'=> compare_at_price,
                    'type'=> 'consu',
                    'default_code'=> product_default_code,
                    'barcode'=> str(product_data['id']),
                    'website_id'=> website_id,
                    "responsible_id"=> 17,
                    "is_storable"=> True,
                
            
            ];




            // var_dump($shipping_address->country);exit;
            // $country_status = $this->Odoo->searchCountry($shipping_address->country);//判断国家

            // $state_status = $this->Odoo->stateSearch($shipping_address->state);//判断省份

            var_dump($customer_data);exit;
        //   $res1  = $this->Odoo->createCustomer($customer_data);
        //   $res2 = $this->Odoo->createOrder($order_data);
          $res3 = $this->Odoo->createOrderLine($line_item);

          var_dump($res3);exit;

        // print_r($order_data);exit;
        
    }


    //搜索查询attribute
    protected function create_attribute($option_data)
    {
       

        $attribute_line_ids = [];
        foreach($option_data as $key=>$value){

            $value_ids = [];

            $attribute['name'] = $value['name'];
            $attribute['value'] = $value['value'];


            $attribute_info = $this->Odoo->searchAttributeId($attribute['name']);

            $attribute_id = $attribute_info['id'];

            // print_r($attribute_info);exit;

            if(!empty($attribute_info)){
               
                foreach($value['value'] as $k=>$v){
                  $attributeValue = $this->Odoo->searchAttributeValueId($v,$attribute_info['id']);

                  $attribute_value_id = $attributeValue['id'];
                  array_push($value_ids,$attribute_value_id);

                  $attribute_line_ids[$k]['value_ids'] = $value_ids;
                  $attribute_line_ids[$k]['attribute_id'] = $attribute_id;


                }
            }
        }

        print_r($attribute_line_ids);exit;
       
    }
 
    
}