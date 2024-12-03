<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $include = explode(',', $request->input('include', []));
        $paginate = $request->input('paginate', 10);
        $productQuery = Product::query();

        if(!empty($include)) {
            $productQuery->with($include);
        }

        return ProductResource::collection($productQuery->paginate($paginate));
    }
}
