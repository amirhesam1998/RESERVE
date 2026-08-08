<?php

namespace App\Http\Controllers\Api;



use App\Action\Salon\CreateSalonAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\CreateRequest;
use App\Http\Requests\Salon\SaveLayoutRequest;
use App\Models\Salon;
use Illuminate\Support\Facades\DB;
use Throwable;

class SalonController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequest $request)
    {
        $this->authorize('salons', ['create-salons']);

        try {

            $salon = DB::transaction(function () use ($request) {

                $salon = Salon::create($request->only(['name', 'address']));

                $pivotData = [];
                $pivotData[$request->main_category] = ['is_main' => true];


                foreach ($request->child_categories ?? [] as $child) {
                    $pivotData[$child] = ['is_main' => false];
                }

                $salon->categories()->sync($pivotData);

                return $salon;
            });

            return response()->json([
                'message' => 'Salon created successfully',
                'salon_id' => $salon->id
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => 'somthing went wrong, try again later'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Salon $salon)
    {
        try {
            $salon->load([
                'categories',
                'floors.sections.seats'
            ]);

            return response()->json([
                'data' => $salon
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => 'somthing went wrong, try again later'], 500);
        }
    }

    // ================== SAVE LAYOUT ========================
    public function saveLayout(Salon $salon, SaveLayoutRequest $request)
    {
        $this->authorize('salons', ['create-salons']);

        try {
            $action = new CreateSalonAction;
            $salon = $action->execute($salon, $request->all());

            return response()->json([
                'message' => 'Layout saved successfully',
                'data' => $salon
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'somthing went wrong, try again later'
            ], 500);
        }
    }
}
