<?php

namespace App\Http\Controllers\Web;


use App\Action\Salon\UpdateSalonLayoutAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\UpdateSalonInfoRequest;
use App\Http\Requests\Salon\UpdateSalonLayoutRequest;
use App\Models\Salon;
use Throwable;

class SalonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('salons', ['view-salons']);

        try {
            $salons = Salon::with('categories')->latest()->get();

            return view('salons.index', compact('salons'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, try again later');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('salons', ['create-salons']);

        try {
            return view('salons.create');
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, try again later');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Salon $salon)
    {
        $this->authorize('salons', ['edit-salons']);

        try {
            $mainCategory = $salon->categories->firstWhere('pivot.is_main', true);

            $assignedChildCategories = $salon->categories->where('pivot.is_main', false)->pluck('id')->toArray();

            return view('salons.edit', compact('salon', 'mainCategory', 'assignedChildCategories'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, try again later');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSalonInfoRequest $request, Salon $salon)
    {

        $this->authorize('salons', ['edit-salon']);

        try {
            $salon->update($request->only([
                'name',
                'address',
                'image'
            ]));

            $categoryData = [];
            $categoryData[$request->main_category] = ['is_main' => true];

            foreach ($request->child_categories ?? [] as $child) {
                $categoryData[$child] = ['is_main' => false];
            }

            $salon->categories()->sync($categoryData);

            return redirect()->route('salons.index');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Somthing went wrong, try again later');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Salon $salon)
    {
        $this->authorize('salons', ['delete-salon']);

        try {
            $salon->delete();

            return redirect()->route('salons.index');
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, try again later');
        }
    }

    public function layout(Salon $salon)
    {
        $this->authorize('salons', ['create-salons']);

        try {
            return view('salons.layout', compact('salon'));
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong, try again later');
        }
    }

    public function updateLayout(UpdateSalonLayoutRequest $request, Salon $salon)
    {
        $this->authorize('salons', ['edit-salon']);

        try {
            $action = new UpdateSalonLayoutAction;
            $updatedSalon = $action->execute($salon, $request->all());

            return redirect()->route('salons.index')->with('success', 'Salon updated successfully');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Somthing went wrong, try again later');
        }
    }
}
