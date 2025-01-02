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
        $data = [];
        $data['code'] = 200;
        $data['message'] = "success";
        Log::info('Product odoo Webhook', $request->all());
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