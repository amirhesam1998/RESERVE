<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageUploadService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {}

    public function uploadMany(array $files, string $folder, $imageable): void
    {
        $existingCount=$imageable->images()->count();

        foreach($files as $index => $file){
            $path=$file->store($folder, 'public');

            $imageable->images()->create([
            'path' => $path,
            'disk' => 'public',
            'order' => $existingCount + $index,
            'is_primary' => false
            ]);  
        }
    }

    public function uploadPrimary(UploadedFile $file, string $folder, $imageable){
        $imageable->images()->where('is_primary', true)->update(['is_primary'=> false]);

        $path=$file->store($folder, 'public');

        return $imageable->images()->create([
            'path' => $path,
            'disk' => 'public',
            'order' => $imageable->images()->count(),
            'is_primary' => true
        ]);

    }

    public function delete(Image $image){
        Storage::disk($image->disk)->delete($image->path);

        $image->delete();
    }

    public function setPrimary(Image $image){
        $image->imageable->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);
    }
}
