<?php

namespace NexaMerchant\OdooApi\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use NexaMerchant\OdooApi\Http\Controllers\Api\Controller;

class WebhookController extends Controller
{
    // order 
    public function order(Request $request) {
        $data = [];
        $data['code'] = 200;
        $data['message'] = "success";
        Log::info('Order odoo Webhook', $request->all());
        return response()->json($data);
    }

    // product
    public function product(Request $request) {
        
        $data = $request->all();

        //$values = json_decode($data['values'], true);

        Log::info('Product odoo Webhook', $data);

        return response()->json($data);

        $product = \NexaMerchant\OdooApi\Models\Products::where('product_id', $values['id'])->first();
        if($product) {
            $product->name = $values['name'];
            $product->default_code = $values['default_code'];
            $product->barcode = $values['barcode'];
            $product->type = $values['type'];
            $product->list_price = $values['list_price'];
            $product->standard_price = $values['standard_price'];
           // $product->cost = $values['cost'];
            $product->qty_available = $values['qty_available'];
            $product->uom_id = $values['uom_id'];
            $product->categ_id = $values['categ_id'];
            $product->taxes_id = $values['taxes_id'];
            $product->description = $values['description'];
            $product->seo_name = $values['seo_name'];
            $product->currency_id = $values['currency_id'];
            $product->values = json_encode($values);
            $product->save();
        } else {
            $product = new \NexaMerchant\OdooApi\Models\Products();
            $product->name = $values['name'];
            $product->product_id = $values['id'];
            $product->default_code = $values['default_code'];
            $product->barcode = $values['barcode'];
            $product->type = $values['type'];
            $product->list_price = $values['list_price'];
            $product->standard_price = $values['standard_price'];
        //    $product->cost = $values['cost'];
            $product->qty_available = $values['qty_available'];
            $product->uom_id = $values['uom_id'];
            $product->categ_id = $values['categ_id'];
            $product->taxes_id = $values['taxes_id'];
            $product->description = $values['description'];
            $product->seo_name = $values['seo_name'];
            //$product->currency_id = $values['currency_id'];
            $product->values = $values;
            $product->save();
        }

        return response()->json($data);
    }

    // customer
    public function customer(Request $request) {
        $data = [];
        $data['code'] = 200;
        $data['message'] = "success";
        return response()->json($data);
    }

    // invoice
    public function invoice(Request $request) {
        $data = [];
        $data['code'] = 200;
        $data['message'] = "success";
        return response()->json($data);
    }

    // payment
    public function payment(Request $request) {
        $data = [];
        $data['code'] = 200;
        $data['message'] = "success";
        return response()->json($data);
    }

    // refund
    public function refund(Request $request) {
        $data = [];
        $data['code'] = 200;
        $data['message'] = "success";
        return response()->json($data);
    }

    // stock
    public function stock(Request $request) {
        $data = [];
        $data['code'] = 200;
        $data['message'] = "success";
        return response()->json($data);
    }

    // delivery
    public function delivery(Request $request) {
        $data = [];
        $data['code'] = 200;
        $data['message'] = "success";
        return response()->json($data);
    }

    
}