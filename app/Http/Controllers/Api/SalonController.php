<?php

namespace App\Http\Controllers\Api;



use App\Action\Salon\CreateSalonAction;
use App\Action\Salon\UpdateSalonLayoutAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\CreateRequest;
use App\Http\Requests\Salon\SaveLayoutRequest;
use App\Http\Requests\Salon\UpdateSalonLayoutRequest;
use App\Http\Resources\SalonListResource;
use App\Models\Salon;
use Illuminate\Support\Facades\Gate;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

use function Pest\Laravel\json;

class SalonController extends Controller
{
    public function index()
    {
        $this->authorize('salons', ['view-salons']);

        try {
            $salons = Salon::with('categories')->latest()->get();

            return response()->json([
                'date' => SalonListResource::collection($salons)
            ]);
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, try again later');
        }
    }

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

                $sessionsIds=$request->sessions ?? [];
                $salon->showTimes()->sync($sessionsIds);

                return $salon;
            });
            return response()->json([
                'message' => 'Salon created successfully',
                'salon_id' => $salon->id,
                'token' => $request->cookie('access_token')
            ]);
        } catch (Throwable $e) {
            //return response()->json(['message' => 'somthing went wrong, try again later'], 500);
            return response()->json([
                'message' => $e->getMessage()
            ]);
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
                'floors.sections.seats.product.prices.attribute_values'
            ]);

            return response()->json([
                'data' => $salon
            ]);
        } catch (Throwable $e) {
           // return response()->json(['message' => 'somthing went wrong, try again later'], 500);
           return response()->json([
            'message' => $e->getMessage()
           ]);
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
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function updateLayout(Salon $salon, UpdateSalonLayoutRequest $request)
    {
        $this->authorize('salons', ['edit-salon']);

        try {
            $action = new UpdateSalonLayoutAction;
            $updatedSalon = $action->execute($salon, $request->all());

            return response()->json([
                'message' => 'Salon updated successfully',
                'salon' => $updatedSalon,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
