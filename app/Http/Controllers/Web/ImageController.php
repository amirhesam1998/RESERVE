<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Salon;
use App\Models\Shop;
use App\Models\Image;
use Exception;
use Throwable;

class ImageController extends Controller
{
    protected array $alloweTypes=[
        'salon' => Salon::class,
        'product' => Product::class
    ];

    public function store(Request $request, string $type, int $id, ImageUploadService $uploader){
        $validated=$request->validate([
            'images' => ['nullable', 'array', 'min:1'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'primary_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']
        ]);

        try{

            $modelClass=$this->resolveType($type);
            $imageable=$modelClass::findOrFail($id);

            if($request->hasFile('primary_image')){
                $uploader->uploadPrimary($request->file('primary_image'), $type . 's', $imageable);
            }

            if($request->hasFile('images')){
                $uploader->uploadMany($request->file('images'), $type . 's', $imageable);
            }

            return response()->json([
                'message' => 'images uploaded successfully',
                'images' => $imageable->images()->get() 
            ]);

        }catch(Throwable $e){
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }

    }

    public function resolveType(string $type): string
    {
        if(!isset($this->alloweTypes[$type])){
            throw new Exception("Invalid entity type: {$type}");
        }

        return $this->alloweTypes[$type];
    }

    public function delete(int $id, ImageUploadService $deleter){
        try{
            $image=Image::findOrFail($id);
            if(!$image){
                throw new Exception("There is no related image");        
            }

            $deleter->delete($image);

            return response()->json([
                'message' => 'Image deleted successfully',
            
            ]);

        }catch(Throwable $e){
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function setPrimary(int $id, ImageUploadService $setPrimary){
        try{
            $image=Image::findOrFail($id);
            if(!$image){
                throw new Exception("No related Image");
            }

            $setPrimary->setPrimary($image);

            return response()->json([
                'message' => 'primary image changed successfully'
            ]);

        }catch(Throwable $e){
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}
