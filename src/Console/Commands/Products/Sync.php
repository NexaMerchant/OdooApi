<?php
namespace NexaMerchant\OdooApi\Console\Commands\Products;

use Illuminate\Console\Command;

class Sync extends Command
{
    protected $signature = 'odoo:products:sync {--prod-id=}';

    protected $description = 'Sync products from the main database to the shop database odoo:products:sync {--prod-id=}';

    public function handle()
    {
        $this->info('Syncing products...');

        $prod_id = $this->option('prod-id');

        var_dump($prod_id);

        // connect to the odoo database
        $odoo = new \NexaMerchant\OdooApi\Helper\Odoo();

        //var_dump($odoo->getProducts());

        var_dump($odoo->getProduct($prod_id));

        // get the products from the main database
    }
}