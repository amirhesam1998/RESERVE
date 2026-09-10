<?php

namespace App\Action\Salon;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\Salon;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CreateSalonAction
{
    public function execute(Salon $salon, array $salonData)
    {
        DB::transaction(function () use ($salon, $salonData) {
            $seatTyprAttribute = Attribute::firstOrCreate(['name' => 'نوع صندلی']);

            foreach ($salonData['floors'] ?? [] as $floorData) {
                $floor = $salon->floors()->create([
                    'name' => $floorData['name'],
                    'position' => $floorData['position'],
                ]);

                foreach ($floorData['sections'] ?? [] as $sectionData) {
                    $section = $floor->sections()->create([
                        'name' => $sectionData['name'],
                        'x' => $sectionData['x'],
                        'y' => $sectionData['y'],
                        'image' => $sectionData['image'] ?? null,
                    ]);

                    foreach ($sectionData['seats'] as $seatData) {
                        $seat = $section->seats()->create([
                            'row' => $seatData['row'],
                            'number' => $seatData['number'],
                            'customText' => $seatData['customText'] ?? null,
                            'x' => $seatData['x'],
                            'y' => $seatData['y'],
                            'status' => $seatData['status'] ?? 'available',
                        ]);

                        $product = Product::create([
                            'name' => "صندلی ردیف {$seat->row} شماره {$seat->number}",
                            'slug' => 'seat-' . $seat->id . '-' . Str::random(6),
                        ]);

                        $seat->update(['product_id' => $product->id]);

                        $price = $product->prices()->create([
                            'price' => $seatData['price'],
                            'discount' => 0,
                            'final_price' => $seatData['price'],
                            'inventory' => 1
                        ]);

                        if (!empty($seatData['type'])) {
                            $typeValue = $seatTyprAttribute->attributeValues()->firstOrCreate([
                                'value' => $seatData['type']
                            ]);

                            $price->attribute_values()->attach($typeValue->id);
                        }
                    }

                    /*                         $seatsToInsert = [];
                        foreach ($sectionData['seats'] ?? [] as $seatData) {
                            $seatsToInsert[] = [
                                'section_id' => $section->id,
                                'row' => $seatData['row'],
                                'number' => $seatData['number'],
                                'customText' => $seatData['customText'] ?? null,
                                'x' => $seatData['x'],
                                'y' => $seatData['y'],
                                'type' => $seatData['type'],
                                'status' => $seatData['status'] ?? 'available',
                                'price' => $seatData['price'],
                            ];
                        }
                        if (!empty($seatsToInsert)) {
                            Seat::insert($seatsToInsert);
                        } */
                }
            }
        });

        return $salon->load('floors.sections.seats');
    }

    /*     protected function syncFloors(Salon $salon, array $floorsData)
    {

        foreach ($floorsData as $floorData) {
            $floor = Floor::create([]);

            $salon->floors()->save($floor);

            if (isset($floorData['sections'])) {
                $this->syncSections($floor, $floorData['sections']);
            }
        }
    }

    protected function syncSections(Floor $floor, array $sectionsData)
    {
        foreach ($sectionsData as $sectionData) {
            $section = Section::create([]);

            $floor->sections()->save($section);


            if (isset($sectionData['seats'])) {
                $this->syncSeats($section, $sectionData['seats']);
            }
        }
    }

    protected function syncSeats(Section $seaction, array $seatsData)
    {
        foreach ($seatsData as $seatData) {
            $seat = Seat::create([]);

            $seaction->seats()->save($seat);
        }
    } */
}
