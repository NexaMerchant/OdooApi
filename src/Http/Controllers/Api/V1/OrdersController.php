<?php
namespace NexaMerchant\OdooApi\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use Illuminate\Foundation\Validation\ValidatesRequests;

use NexaMerchant\OdooApi\Http\Controllers\Api\Controller;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        return response()->json();
    }

    public function show(Request $request, $id)
    {
        return response()->json();
    }
}