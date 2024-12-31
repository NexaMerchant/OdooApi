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

class UnInstall extends CommandInterface 

{
    protected $signature = 'OdooApi:uninstall';

    protected $description = 'Uninstall OdooApi an app';

    public function getAppVer() {
        return config("OdooApi.ver");
    }

    public function getAppName() {
        return config("OdooApi.name");
    }

    public function handle()
    {
        if (!$this->confirm('Do you wish to continue?')) {
            // ...
            $this->error("App OdooApi UnInstall cannelled");
            return false;
        }
    }
}