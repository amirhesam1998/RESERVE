<?php

namespace App\Action\Salon;

use App\Models\Attribute;
use App\Models\Floor;
use App\Models\Product;
use App\Models\Salon;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;


class UpdateSalonLayoutAction
{
    public function execute(Salon $salon, array $data)
    {
        return DB::transaction(function () use ($salon, $data) {
            $salon->update(Arr::only($data, ['name', 'address', 'image']));

            if (isset($data['main_category']) || isset($data['child_categories'])) {
                $this->syncCategories($salon, $data);
            }

            if (isset($data['floors'])) {
                $this->syncFloors($salon, $data['floors']);
            }

            return $salon->load(['floors.sections.seats']);
        });
    }

    protected function syncCategories(Salon $salon, array $data)
    {
        $categories = [];

        if (!empty($data['main_category'])) {
            $categories[$data['main_category']] = ['is_main' => true];
        }

        foreach ($data['child_categories'] ?? [] as $categoryId) {
            $categories[$categoryId] = ['is_main' => false];
        }

        $salon->categories()->sync($categories);
    }

    protected function syncFloors(Salon $salon, array $floorsData)
    {
        $incomingFloorIds = collect($floorsData)->pluck('id')->filter()->all();
        $salon->floors()->whereNotIn('id', $incomingFloorIds)->delete();

        foreach ($floorsData as $floorData) {
            $floor = $salon->floors()->updateOrCreate(
                ['id' => $floorData['id'] ?? null],
                [
                    'name' => $floorData['name'],
                    'position' => $floorData['position'] ?? 0,
                ]
            );

            if (isset($floorData['sections'])) {
                $this->syncSections($floor, $floorData['sections']);
            }
        }
    }

    protected function syncSections(Floor $floor, array $sectionsData)
    {
        $incomingSectionIds = collect($sectionsData)->pluck('id')->filter()->all();
        $floor->sections()->whereNotIn('id', $incomingSectionIds)->delete();

        foreach ($sectionsData as $sectionData) {
            $payload = [
                'name' => $sectionData['name'],
                'position' => $sectionData['position'] ?? 0,
                'image' => $sectionData['image'] ?? null,
                'x' => $sectionData['x'] ?? 0,
                'y' => $sectionData['y'] ?? 0,
            ];

            $section = $floor->sections()->updateOrCreate(
                ['id' => $sectionData['id'] ?? null],
                $payload
            );

            if (isset($sectionData['seats'])) {
                $this->syncSeats($section, $sectionData['seats']);
            }
        }
    }

    protected function syncSeats(Section $section, array $seatsData)
    {
        $incomingSeatIds = collect($seatsData)->pluck('id')->filter()->all();
        $seatsToDelete = $section->seats()->whereNotIn('id', $incomingSeatIds)->get();
        $productIdsToDelete = $seatsToDelete->pluck('product_id')->filter()->all();
        $seatTypeAttribute = Attribute::firstOrCreate(['name' => 'نوع صندلی']);

        foreach ($seatsToDelete as $deletedProduct) {
            Product::where('id', $deletedProduct->product_id)->delete();
        }

        if (!empty($productIdsToDelete)) {
            Product::whereIn('id', $productIdsToDelete)->delete();
        }

        foreach ($seatsData as $seatData) {
            $payload = [
                'row' => $seatData['row'],
                'number' => $seatData['number'],
                'customText' => $seatData['customText'] ?? null,
                'x' => $seatData['x'],
                'y' => $seatData['y'],
                'status' => $seatData['status'] ?? 'available',
            ];

            $seat = $section->seats()->updateOrCreate(
                ['id' => $seatData['id'] ?? null],
                $payload
            );

            $product = $seat->product;
            if ($product) {
                $product->update([
                    'name' => "صندلی ردیف {$seat->row} شماره {$seat->number}"
                ]);
            } else {
                $product = $seat->product()->create([
                    'name' => "صندلی ردیف {$seat->row} شماره {$seat->number}",
                    'slug' => 'seat-' . $seat->id . '-' . Str::random(6),
                ]);

                $seat->update(['product_id' => $product->id]);
            }

            $price = $product->prices()->first();
            if ($price) {
                $price->update([
                    'price' => $seatData['price'],
                    'final_price' => $seatData['price'],
                ]);

                $cartItems=$price->cartItems()->get();
                foreach($cartItems as $cartItem){
                    $cartItem->unit_price = $price->final_price;
                    $cartItem->save();
                }

            } else {
                $price = $product->prices()->create([
                    'price' => $seatData['price'],
                    'final_price' => $seatData['price'],
                    'inventory' => 1
                ]);
            }

            if (!empty($seatData['type'])) {
                $typeValue = $seatTypeAttribute->attributeValues()->firstOrCreate([
                    'value' => $seatData['type']
                ]);

                $price->attribute_values()->sync([$typeValue->id]);
            } else {
                $price->attribute_values()->sync([]);
            }
        }
    }
}
