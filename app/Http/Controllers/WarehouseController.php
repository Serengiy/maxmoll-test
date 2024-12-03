<?php

namespace App\Http\Controllers;

use App\Http\Resources\WareHouseResource;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WarehouseController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return WareHouseResource::collection(Warehouse::all());
    }
}
