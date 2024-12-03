<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\OrderCreateRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $paginate = $request->input('paginate', 10);
        $orderQuery = Order::query();

        if($include = $request->input('include')) {
            $orderQuery->with(explode(',', $include));
        }

        $orderQuery->filter($request->all());

        return OrderResource::collection($orderQuery->paginate($paginate));
    }

    public function store(OrderCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $customer = $data['customer'];

        DB::beginTransaction();
        try {
            foreach ($data['order_items'] as $orderItem) {
                $product = Product::query()->with('stocks')->where('id', $orderItem['product_id'])->first();

                $requiredCount = $orderItem['count'];
                $totalStock = $product->stocks->sum('stock');

                if ($totalStock < $requiredCount) {
                    return response()->json([
                        'message' =>
                            'Not enough stock for product ' . $product->name .
                            ' Required: ' . $requiredCount .
                            ' Available: ' . $totalStock,
                    ], 400);
                }

                $remainingCount = $requiredCount;
                logger()->info('Remaining stock: ' . $product->stocks->sum('stock'));
                foreach ($product->stocks as $stock) {
                    if ($remainingCount <= 0) {
                        break;
                    }

                    $availableStock = $stock->stock;
                    $deducted = min($availableStock, $remainingCount);

                    $order = Order::query()->create([
                        'customer' => $customer,
                        'status' => Order::STATUS_ACTIVE,
                        'warehouse_id' => $stock->warehouse_id,
                    ]);

                    $order->product()->attach($product->id, [
                        'count' => $deducted,
                    ]);

                    $stock->decrement('stock', $deducted);
                    $remainingCount -= $deducted;
                }
                logger()->info('Remaining stock after loop: ' . $product->stocks->sum('stock') . ' Required: ' . $requiredCount);
            }
            DB::commit();
            return response()->json(['message' => 'Order created successfully!'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function updateItem(int $id, OrderUpdateRequest $request): JsonResponse
    {
        $order = Order::query()->findOrFail($id);
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $newProduct = Product::query()->findOrFail($data['order_item']['product_id']);
            $productOld = $order->product->first();
            $stock = $productOld->stocks
                ->where('warehouse_id', $order->warehouse_id);


            $countOld = $order->product->first()->pivot->count;
            $countNew = $data['order_item']['count'];

            if(!$stock->first()){
                Stock::query()->create([
                    'product_id' => $productOld->id,
                    'warehouse_id' => $order->warehouse_id,
                    'stock' => $countOld
                ]);
            }else{
                $stock->first()->increment('stock', $countOld);
            }

            if($newProduct->stocks->sum('stock') < $countNew){
                return response()->json(['message' => 'Not enough stock for product ' . $newProduct->name], 400);
            }


            $remainingCount = $countNew;
            logger()->info('Remaining stock: ' . $newProduct->stocks->sum('stock'));
            foreach ($newProduct->stocks as $stock) {
                if ($remainingCount <= 0) {
                    break;
                }

                $availableStock = $stock->stock;
                $deducted = min($availableStock, $remainingCount);

                $order = Order::query()->create([
                    'customer' => $data['customer'],
                    'status' => Order::STATUS_ACTIVE,
                    'warehouse_id' => $stock->warehouse_id,
                ]);

                $order->product()->attach($newProduct->id, [
                    'count' => $deducted,
                ]);

                $stock->decrement('stock', $deducted);
                $remainingCount -= $deducted;
            }
            logger()->info('Remaining stock after loop: ' . $newProduct->stocks->sum('stock') . ' Required: ' . $countNew);
            return response()->json(['message' => 'Order updated successfully!']);
        }catch (Exception $e){
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function complete(int $id): JsonResponse
    {
        $order = Order::query()->findOrFail($id);
        $order->update(['status' => Order::STATUS_COMPLETED]);
        return response()->json(['message' => 'Order completed successfully!']);
    }

    public function cancel(int $id): JsonResponse
    {
        $order = Order::query()->findOrFail($id);

        DB::beginTransaction();
        try{
            $order->update(['status' => Order::STATUS_CANCELLED]);
            $count = $order->product->first()->pivot->count;
            $order->product()->updateExistingPivot(
                $order->product->first()->id,
                ['count' => 0 - $count]
            );

            $product = $order->product->first();

            logger()->info(
                'Order cancelled with count: ' . $count .
                ' Product: ' . $product->name.
                ' Product id: ' . $product->id .
                ' Stock: ' . $product->stocks->sum('stock')
            );

            $stock = $product->stocks
                ->where('warehouse_id', $order->warehouse_id);


            if(!$stock->first()){
                Stock::query()->create([
                    'product_id' => $product->id,
                    'warehouse_id' => $order->warehouse_id,
                    'stock' => $count
                ]);
            }else{
                $stock->first()->increment('stock', $count);
            }

            $product->refresh();
                logger()->info('Product: ' . $product->name . ' Stock after increment: ' . $product->stocks->sum('stock'));

            DB::commit();
            return response()->json(['message' => 'Order cancelled successfully!']);
        }catch (Exception $e){
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }

    }

    public function restore(int $id): JsonResponse
    {
        $order = Order::query()->findOrFail($id);

        if($order->status !== Order::STATUS_CANCELLED)
            return response()->json([
                'message' => 'Only cancelled orders can be restored!',
            ], 400);

        $count = abs($order->product->first()->pivot->count);
        $product = $order->product->first();
        $totalStock = $product->stocks->sum('stock');

        if ($count > $totalStock)
            return response()->json([
                'message' => 'Not enough stock to restore order!',
            ], 400);

        DB::beginTransaction();

        try {
            logger()->info(
                'Order restoring with count: ' . $count .
                ' Product: ' . $product->name .
                ' Product id: ' . $product->id .
                ' Stock: ' . $product->stocks->sum('stock')
            );
            $remainingCount = $count;
            foreach ($product->stocks as $stock) {
                if ($remainingCount <= 0) {
                    break;
                }

                $availableStock = $stock->stock;
                $deducted = min($availableStock, $remainingCount);

                $stock->decrement('stock', $deducted);
                $remainingCount -= $deducted;
            }

            $order->refresh();
            $order->update(['status' => Order::STATUS_ACTIVE]);

            logger()->info(
                'Product: ' . $product->name .
                ' Stock after decrement: ' . $product->stocks->sum('stock')
            );
            DB::commit();

            return response()->json([
                'message' => 'Order restored successfully!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }

    }
}
