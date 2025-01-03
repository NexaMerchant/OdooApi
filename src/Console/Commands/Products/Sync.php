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

        // connect to the odoo database
        $odoo = new \NexaMerchant\OdooApi\Helper\Odoo();
        var_dump($odoo->getProducts());

        // get the products from the main database
    }
}