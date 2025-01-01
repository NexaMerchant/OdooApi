<?php

use Illuminate\Support\Facades\Route;
use NexaMerchant\OdooApi\Http\Controllers\Api\V1\WebhookController;

Route::group(['middleware' => ['api','assign_request_id'], 'prefix' => 'api/v1'], function () {
    Route::prefix('odooapi')->group(function () {

        Route::controller(WebhookController::class)->prefix('webhook')->group(function () {

            Route::post('order', 'order')->name('odooapi.api.webhook.order');
            Route::post('product', 'product')->name('odooapi.api.webhook.product');
            Route::post('customer', 'customer')->name('odooapi.api.webhook.customer');
            Route::post('invoice', 'invoice')->name('odooapi.api.webhook.invoice');
          //  Route::post('payment', 'payment')->name('odooapi.api.webhook.payment');
            Route::post('refund', 'refund')->name('odooapi.api.webhook.refund');
            Route::post('stock', 'stock')->name('odooapi.api.webhook.stock');

        });

    });
});