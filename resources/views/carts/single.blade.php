<x-layout title="Cart Items">
    <main class="flex-grow container mx-auto px-6 py-10">
        <div class="max-w-3xl mx-auto space-y-6">
            <h2 class="text-3xl font-bold mb-6">Your Cart</h2>

            @if ($cartItems->isEmpty())
                <p class="text-gray-500">Your cart is empty.</p>
            @else
                @foreach ($cartItems as $cartItem)
                    @php
                        $product = $cartItem->product;
                        $price = $cartItem->price;
                        $attributeValues = $price?->attribute_values()->get();
                        $showtime = $cartItem->showTime;

                    @endphp

                    <div class="bg-white p-6 rounded-lg shadow flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-semibold mb-1">{{ $product->name }}</h3>

                            @if ($attributeValues && $attributeValues->isNotEmpty())
                                <p class="text-sm text-gray-500 mb-1">
                                    @foreach ($attributeValues as $av)
                                        {{ $av->attribute->name }}: {{ $av->value }}@if (!$loop->last)
                                            ,
                                        @endif
                                    @endforeach
                                </p>
                            @endif

                            @if ($showtime)
                                <p class="text-sm text-gray-500 mb-1">
                                    {{ $showtime->start_time->format('H:i') }} -
                                    {{ $showtime->end_time->format('H:i') }} سانس
                                </p>
                            @endif

                            <p class="text-sm text-gray-500">
                                تعداد: {{ $cartItem->quantity }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-lg font-semibold">
                                {{ number_format($cartItem->unit_price * $cartItem->quantity) }} تومان
                            </p>

                            {{--                             <form {{-- action="{{ route('cart.remove', $cartItem->id) }}" method="post" class="mt-2">
                                @csrf
                                @method('delete')
                                <button type="submit" class="text-red-500 text-sm hover:underline">
                                    Remove
                                </button>
                                --}}
                            </form>
                        </div>
                    </div>
                @endforeach

                <div class="bg-white p-6 rounded-lg shadow flex justify-between items-center">
                    <span class="text-lg font-semibold">Total</span>
                    <span class="text-xl font-bold">
                        {{ number_format($cartItems->sum(fn($item) => $item->unit_price * $item->quantity)) }} تومان
                    </span>
                </div>

                <a {{-- href="{{  route('checkout') }}" --}}
                    class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 transition">
                    Proceed to Checkout
                </a>
            @endif
        </div>
    </main>
</x-layout>
