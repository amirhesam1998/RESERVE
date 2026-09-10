<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserve;
use App\Models\Seat;
use App\Models\User;
use App\Models\Showtime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use League\CommonMark\Extension\Table\TableRow;
use PHPUnit\Event\Code\Throwable as CodeThrowable;
use Throwable;

class Cart extends Controller
{
   /*  public function showCartItem(User $user)
    {
        try {
            $cart = $user->cart;
            if (!$cart) {
                throw new Exception('No cart availble');
            }

            $cartItems = $cart->cartsItems()->get();

            return response()->json([
                'cart itams' => $cartItems
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    } */


    public function seatAddToCart(Request $request)
    {
        $validated = $request->validate([
            'seats' => ['required', 'array', 'min:1'],
            'seats.*.seat_id' => ['required', 'integer', 'exists:seats,id'],
            'seats.*.session_id' => ['required', 'integer', 'exists:showtimes,id']
        ]);
        try {

            $user = Auth::user();

            DB::transaction(function () use ($validated, $user) {
                $cart = $user->cart ?? $user->cart()->create([]);
                $reserves = [];

                foreach ($validated['seats'] as $seatData) {
                    $seat = Seat::findOrFail($seatData['seat_id']);

                    $session= Showtime::findOrFail($seatData['session_id']);
                    if(!$session){
                        throw new Exception("No valid session");
                    }

                    $salon=$seat->section->floor->salon;
                    if(!$salon){
                        throw new Exception("This session is not related to this salon");                        
                    }

                    $product = $seat->product;
                    if (!$product) {
                        throw new Exception("Seat #{$seat->id} has no associated product.");
                    }

                    $existing = Reserve::where('product_id', $product->id)->where('showtime_id', $session->id)->first();
                    if ($existing) {
                        $isExpired = $existing->status === 'pending' && $existing->expires_at && $existing->expires_at->isPast();

                        if ($existing->status === 'confirmed' || !$isExpired) {
                            throw new Exception("Seat #{$seat->id} is no longer available.");
                        }

                        $existing->delete();
                    }

/*                      $alreadyInCart = $cart->cartsItems()->where('product_id', $product->id)->exists();
                    if ($alreadyInCart) {
                        throw new \Exception("Seat #{$seat->id} is already in the cart.");
                    } */

                    $price = $product->prices()->first();
                    if (!$price) {
                        throw new \Exception("No price found for seat #{$seat->id}.");
                    }

/*                     $price->inventory-=1;
                    $price->save(); */

                    $created = $cart->cartsItems()->create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'price_id' => $price->id,
                        'showtime_id' => $session->id,
                        'quantity' => 1,
                        'unit_price' => $price->price
                    ]);
                    if(!$created){
                        throw new Exception("Couldnt add to cart try again later");
                    }

/*                     if ($created) {
                        $seat->status = 'reserved';
                        $seat->save();
                    } */

                    $reserves[] = Reserve::create([
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                        'showtime_id' => $session->id,
                        'price' => $price->price,
                        'status' => 'pending',
                        'expires_at' => now()->addMinute(10)
                    ]);
                    if(!$reserves){
                        throw new Exception("Error Processing Request");
                    }
                }
            });

            return response()->json([
                'message' => 'Seat added to cart and reserved successfully.',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function removeFromCart(Request $request)
    {
        try {
            $validated = $request->validate([
                'item_id' => ['integer', 'required', 'exists:carts_items,id'],
            ]);

            $user = Auth::user();

            DB::transaction(function () use ($user, $validated) {
                $cart = $user->cart;
                if(!$cart || $cart->cartsItems()->count()===0){
                    throw new Exception("Your cart is empty");                
                }

                $cartItem = $cart->cartsItems()->where('id', $validated['item_id'])->first();
                if(!$cartItem){
                    throw new Exception("There is no cart item");
            
                }

                $product = $cartItem->product;
                if(!$product){
                    throw new Exception("There is no related product");
                }

                $prices=$product->prices()->get();
                if($prices->isEmpty()){
                    throw new Exception("There are no related prices");
                }

/*                 foreach($prices as $price){
                    $price->inventory+=1;
                    $price->save();
                } */

                $user->reserves()->where('product_id', $product->id)->where('showtime_id', $cartItem['showtime_id'])->delete();

/*                 $seat = $product->seat;
                if ($seat) {
                    $seat->status = 'available';
                    $seat->save();
                } */

                $cartItem->delete();

            });

            return response()->json([
                'message'=> "Item removed from cart successfully"
            ]);
        }catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
   
    }
}
