<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use App\Models\Price;
use App\Models\Product;
use App\Models\Reserve;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckOut extends Controller
{
    public function checkOut(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception('No User');
        }

        $validated = $request->validate([
            'address' => ['required', 'string', 'min:3'],
            'city' => ['required', 'string'],
            'postal_code' => ['required', 'digits:5'],
        ]);

        try {
            $totalPrice = DB::transaction(function () use ($validated, $user) {
                $cart = $user->cart;
                if (!$cart || $cart->cartsItems()->count() === 0) {
                    throw new Exception('Cart is empty');
                }

                $order = $user->orders()->create([
                    'city' => $validated['city'],
                    'address' => $validated['address'],
                    'postal_code' => $validated['postal_code']
                ]);
                if (!$order) {
                    throw new Exception('Coudnt create order');
                }

                $items = $cart->cartsItems()->get();
                $totalPrice = 0;

                foreach ($items as $item) {
                    $price = Price::findOrFail($item['price_id']);
                    $product = Product::findOrFail($item['product_id']);

                    $seat = $product->seat;
                    $seat->status = 'sold';
                    $seat->save();

                    foreach ($user->reserves()->get() as $reserve) {
                        $reserve->status = 'confirmed';
                        $reserve->save();
                    }


                    /*                     $reserve = Reserve::where('product_id', $product->id)->where('user_id', $user->id)->where('status', 'pending')->first();
                    if (!$reserve) {
                        $reserve->update([
                            'status' => 'confirmed',
                            'expires_at' => null,
                            'confirmed_at' => now()
                        ]);
                    } */

                    $attributevalues = $price->attribute_values()->get();
                    $attributeSnapshot = $attributevalues->map(function ($av) {
                        return "{$av->attribute->name}: {$av->value}";
                    })->implode(', ');

                    $itemTotal = $item->unit_price * $item->quantity;
                    $totalPrice += $itemTotal;

                    $order->orderItems()->create([
                        'product_title' => $product['name'],
                        'attributes_snapshot' => $attributeSnapshot,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $itemTotal
                    ]);
                }
                $cart->cartsItems()->delete();

                return $totalPrice;
            });
            return response()->json([
                'message' => 'paymant finished successfully',
                'total price' => $totalPrice
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
