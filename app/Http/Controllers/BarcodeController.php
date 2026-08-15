<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BarcodeController extends Controller
{
   public function print(Request $request)
    {
        $products = json_decode($request->products, true);
        
        // Cek apakah checkbox dicentang (default false/tidak)
        $showPrice = $request->has('show_price'); 

        if (empty($products)) {
            return redirect()->back();
        }

        // Kirim variabel $showPrice ke view
        return view('products.print-barcode', compact('products', 'showPrice'));
    }
}