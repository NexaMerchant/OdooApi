<?php
/**
 * 
 * This file is auto generate by Nicelizhi\Apps\Commands\Create
 * @author Steve
 * @date 2024-12-13 17:27:40
 * @link https://github.com/xxxl4
 * 
 */
namespace NexaMerchant\OdooApi\Console\Commands;

use NexaMerchant\Apps\Console\Commands\CommandInterface;

class Install extends CommandInterface 

{
    protected $signature = 'OdooApi:install';

    protected $description = 'Install OdooApi an app';

    public function getAppVer() {
        return config("OdooApi.ver");
    }

    public function getAppName() {
        return config("OdooApi.name");
    }

    public function handle()
    {
        $this->info("Install app: OdooApi");
        if (!$this->confirm('Do you wish to continue?')) {
            // ...
            $this->error("App OdooApi Install cannelled");
            return false;
        }
    }
}