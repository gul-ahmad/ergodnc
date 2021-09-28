<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfficeImageController extends Controller
{

    public function store(Office $office): JsonResource
    {
       abort_unless(auth()->user()->tokenCan('office.update'),
            Response::HTTP_FORBIDDEN
        ); 

        $this->authorize('update', $office);

        request()->validate([
            'image' => ['file', 'max:5000', 'mimes:jpg,png']
        ]);

        $path = request()->file('image')->storePublicly('/', ['disk' => 'public']);

        $image = $office->images()->create([
            'path' => $path
        ]);

        return ImageResource::make($image);
    }


    public function delete(Office $office, Image $image)
    {
        abort_unless(auth()->user()->tokenCan('office.update'),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize('update', $office);

         //checking here that the image is not attached to office 
         //commented this part as we have done implicit binding i-e in the Route by image:id
           //Route::delete('/offices/{office}/images/{image:id}', [\App\Http\Controllers\OfficeImageController::class, 'delete'])->middleware(['auth:sanctum', 'verified']);

      /*   throw_if($image->resource_type != 'office' || $image->resource_id != $office->id,
            ValidationException::withMessages(['image' => 'Cannot delete this image.'])
        ); */

        throw_if($office->images()->count() == 1,
            ValidationException::withMessages(['image' => 'Cannot delete the only image.'])
        );

        throw_if($office->featured_image_id == $image->id,
            ValidationException::withMessages(['image' => 'Cannot delete the featured image.'])
        );
        

        //removed disk public as the laravel default storage is local
        //so we are using it and not specifying it
        //so we easily switch to S3 storage on Production
        //and to public on staging 
        //we change this line from local to public in .env FILESYSTEM_DRIVER=public
        //Storage::disk('public')->delete($image->path);

        Storage::delete($image->path);

        $image->delete();
    }



}
