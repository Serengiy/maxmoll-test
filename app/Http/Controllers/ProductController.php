<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $include = $request->input('include', '');
        $paginate = $request->input('paginate', 10);
        $productQuery = Product::query();

        if(!empty($include)) {
            $productQuery->with(explode(',', $include));
        }

        return ProductResource::collection($productQuery->paginate($paginate));
    }
}
