<?php

namespace App\Models;

use App\Observers\StockObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([StockObserver::class])]
class Stock extends Model
{
    protected $guarded = ['id'];
}
