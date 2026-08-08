<?php

namespace App\Action\Salon;

use App\Models\Floor;
use App\Models\Salon;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Throwable;

class UpdateSalonLayoutAction
{
    public function execute(Salon $salon, array $data)
    {
        return DB::transaction(function () use ($salon, $data) {
            /* $salon->update(Arr::only($data, ['name', 'address', 'image'])); */

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
        $incomingFloorData = collect($floorsData)->pluck('id')->filter()->all();
        $salon->floors()->whereNotIn('id', $incomingFloorData)->delete();

        foreach ($floorsData as $floorData) {
            $floor = $salon->floors()->updateOrCreate(
                ['id' => $floorData['id'] ?? null],
                [
                    'name' => $floorData['name'],
                    'position' => $floorData['position'] ?? 0
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
            $section = $floor->sections()->updateOrCreate(
                ['id' => $sectionData['id'] ?? null],
                [
                    'name' => $sectionData['name'],
                    'position' => $sectionData['position'] ?? 0,
                    'image' => $sectionData['image'] ?? null
                ]
            );

            if (isset($sectionData['seats'])) {
                $this->syncSeats($section, $sectionData['seats']);
            }
        }
    }

    protected function syncSeats(Section $section, array $SeatsData)
    {
        $incomingSeatsData = collect($SeatsData)->pluck('id')->filter()->all();
        $section->seats()->whereNotIn('id', $incomingSeatsData)->delete();

        foreach ($SeatsData as $seatData) {
            $seat = $section->seats()->updateOrCreate(
                ['id' => $seatData['id'] ?? null],
                Arr::except($seatData, ['id'])
            );
        }
    }
}
