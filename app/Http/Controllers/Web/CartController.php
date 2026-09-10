<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Throwable;
use Illuminate\Http\Request;

class CartController extends Controller
{
        public function showCartItem(User $user)
    {
        try {
            $cart = $user->cart;
            if (!$cart) {
                throw new Exception('No cart availble');
            }

            $cartItems = $cart->cartsItems()->get();

            return view('carts.single', compact('cartItems'));

        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
