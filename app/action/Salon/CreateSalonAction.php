<?php

namespace App\Action\Salon;

use App\Models\Salon;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateSalonAction
{
    public function execute(Salon $salon, array $salonData)
    {
        try {
            DB::transaction(function () use ($salon, $salonData) {

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

                        $seatsToInsert = [];
                        foreach ($sectionData['seats'] ?? [] as $seatData) {
                            $seatsToInsert[] = [
                                'section_id' => $section->id, // ارتباط با بخش
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
                        }
                    }
                }
            });

            return $salon->load('floors.sections.seats');
        } catch (Throwable $e) {
            return response()->json($e->getMessage());
        }
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
