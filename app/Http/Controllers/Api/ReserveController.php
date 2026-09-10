<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Reserve;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReserveController extends Controller
{
    public function confirmReservation(Request $request)
    {
        try {
            $validated = $request->validate([
                ''
            ]);

            $totalPrice = 0;

            DB::transaction(function () use ($validated) {
                foreach ($validated['cart_items'] as $cartItem) {
                    $totalPrice += $cartItem['quantity'] * $cartItem['unit_price'];

                    $user->reserves()->create([$validated['product_id']]);

                    $product = Product::findOrFail($validated['product_id']);
                    $seat = $product->seat;
                    if (!$seat) {
                        throw new Exception('Seat Dosent exists');
                    }

                    $seat->status = 'sold';
                    $seat->save();
                }

                return response()->json([
                    'message' => 'reserve confirmed successfully',
                ]);
            });
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
