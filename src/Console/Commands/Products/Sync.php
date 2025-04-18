<?php
namespace NexaMerchant\OdooApi\Console\Commands\Products;

use Illuminate\Console\Command;
use Obuchmann\OdooJsonRpc\Odoo;
use Obuchmann\OdooJsonRpc\Odoo\Request\Arguments\Domain;

class Sync extends Command
{
    protected $signature = 'OdooApi:products:sync {--prod-id=}';

    protected $description = 'Sync products from the main database to the shop database odoo:products:sync {--prod-id=}';

    private $host;
    private $username;
    private $api_key;
    private $database;

    public function handle()
    {
        $this->info('Syncing products...');

        $prod_id = $this->option('prod-id');

        
        // Get the product Detail
        $localProduct = \Webkul\Product\Models\Product::where('id', $prod_id)->where('type','configurable')->first();

        if(!$localProduct) {
            $this->error('Product not found');
            return;
        }



        $this->host = config('OdooApi.host');
        $this->username = config('OdooApi.username');
        $this->password = config('OdooApi.password');
        $this->database = config('OdooApi.db');

        // Connect to Odoo
        $odoo = new Odoo(new Odoo\Config($this->database, $this->host, $this->username, $this->password));
        $odoo->connect();

        // search website
        $website = $odoo->model('website')
            ->where('name', '=', "shop.hatmeo.com")
            ->first();
        if(!$website) {
            $this->error('Website not found');
            return;
        }

        $this->info('Connected to Odoo '. $localProduct->sku);

        $odooProductTemplate = $odoo->find('product.template', 246);
        //var_dump($odooProductTemplate);
        $odooProduct = $odoo->model('product.product')->where('product_tmpl_id', '=', $odooProductTemplate->id)->get();
        var_dump($odooProduct);
        exit;
        $product = $odoo->model('product.template')
            ->where('default_code', '=', $localProduct->sku)
            ->first();

        // $partners = $odoo->model('product.template')
        //     ->limit(5)
        //     ->orderBy('id', 'desc')
        //     ->get();

        var_dump($product);

        // var_dump($partners);

        
    }
}