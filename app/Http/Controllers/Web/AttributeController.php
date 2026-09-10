<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('attributes', ['view-attributes']);

        $attributes = Attribute::with('attributeValues')->latest()->get();

        return view('Attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('attributes', ['create-attributes']);
        
        return view('Attributes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('attributes', ['create-attributes']);

        try {
            $validated = $request->validate([
                'attributes' => ['required', 'array'],
                'attributes.*.name' => ['required', 'string', 'max:256'],
                'attributes.*.is_color' => ['nullable', 'boolean'],
                'attributes.*.values' => ['required', 'array'],
                'attributes.*.values.*' => ['string', 'required'],
                'attributes.*.colors' => ['nullable', 'array'],
                'attributes.*.colors.*' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/']
            ]);

            DB::transaction(function () use ($validated) {

                foreach ($validated['attributes'] as $item) {

                    $isColor = !empty($item['is_color']);

                    $attribute = Attribute::create([
                        'name' => $item['name'],
                        'is_color' => $isColor
                    ]);

                    $hasColor = isset($item['rgb']) && $item['is_color'];

                    foreach ($item['values'] as $index => $value) {
                        $attribute->attributeValues()->create([
                            'value' => $value,
                            'color_code' => $isColor ? ($item['colors'][$index]) : null
                        ]);
                    }
                }
            });

            return redirect()->route('attributes')->with('success', 'Attribute and values saved successfully');
        } catch (Throwable $e) {
            dd($e->getMessage(), $e->getFile(), $e->getLine());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Attribute $attribute)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attribute $attribute)
    {
        $this->authorize('attributes', ['update-attributes']);

        $attribute->load('attributeValues');
        return view('attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attribute $attribute)
    {
        $this->authorize('attributes', ['update-attributes']);

        try {
            $validated = $request->validate([

                'name' => ['required', 'string', 'max:256'],
                'is_color' => ['nullable', 'boolean'],
                'values' => ['required', 'array'],
                //  'values.*' => ['string', 'required'],
                'colors' => ['nullable', 'array'],
                'colors.*' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            ]);

            DB::transaction(function () use ($attribute, $validated) {
                $isColor = !empty($validated['is_color']);

                $attribute->update([
                    'name' => $validated['name'],
                    'is_color' => $isColor
                ]);

                $SubmitedIds = [];

                foreach ($validated['values'] as $valData) {
                    if (!empty($valData['id'])) {
                        $attribute->attributeValues()->where('id', $valData['id'])->update([
                            'value' => $valData['value'],
                            'color_code' => $isColor ? ($valData['color'] ?? null) : null
                        ]);

                        $SubmitedIds[] = $valData['id'];
                    } else {
                        $newValue = $attribute->attributeValues()->create([
                            'value' => $valData['value'],
                            'color_code' => $isColor ? ($valData['color'] ?? null) : null
                        ]);

                        $SubmitedIds[] = $newValue->id;
                    }
                }

                $attribute->attributeValues()->whereNotIn('id', $SubmitedIds)->delete();
            });

            return redirect()->route('attributes')->with('success', 'Attribute Updated successfully');
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong try again later');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attribute $attribute)
    {
        $this->authorize('attributes', ['delete-attributes']);

        try {
            $attribute->delete();

            return redirect()->route('attributes')->with('success', 'Attribute Updated successfully');
        } catch (Throwable $e) {
            return back()->with('error', 'Somthing went wrong try again later');
        }
    }
}
