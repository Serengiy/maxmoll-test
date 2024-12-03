<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockMovementResource;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementsController extends Controller
{
    public function index(Request $request)
    {
        $paginate = $request->input('paginate', 10);

        $stockMovements = StockMovement::query()->filter($request->all());
        return StockMovementResource::collection($stockMovements->paginate($paginate));
    }
}
