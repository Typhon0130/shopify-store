<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sku;
use Illuminate\Http\Request;

class SkuController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'checkRole']);
    }


    // Get all sku
    public function index()
    {
        $products = Product::with('sku')->paginate(30);

        return view('sku', [
             'title'   => 'SKU List',
             'products'=> $products
            ]
        );
    }
}