<?php

namespace App\Http\Controllers\API;

use Image;

use App\Models\Performer;
use App\Models\PerformerMedia;
use Illuminate\Http\Request;
use App\Models\UserPerformers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PerformerController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required'],
            'name'    => ['required', 'string', 'max:128'],
            'youtube' => ['required', 'string', 'max:128'],
            'spotify' => ['required', 'string', 'max:128'],
            'phone'   => ['required', 'string', 'max:128'],
            'genre'   => ['required'],
            'image'   => ['file', 'mimes:jpg,jpeg,webp,png', 'max: 3048']
        ]);

        if ($validator->fails()) {
            // return errors as JSON response
            return response()->json(['errors' => $validator->errors()]);
        }

        if($request->hasFile("image")){
            $image = $request->file('image');
            $filename = time().'.'.$image->getClientOriginalExtension();
            $image_path = public_path('images/user_images/'.$filename);
            Image::make($image)->fit(300, 300)->save($image_path);
            $path = asset('public/images/user_images/') . '/' . $filename;
        }

        $genreArray = explode(',', $request->genre);

        $model = Performer::create([
            'name'      => $request->name,
            'youtube'   => $request->youtube,
            'spotify'   => $request->spotify,
            'phone'     => $request->phone,
            'genre'     => implode(',', $genreArray),
            'image'     => $path ?? ''
        ]);

        if ($model) {
            UserPerformers::create(
                [
                    'user_id' => $request->user_id,
                    'performer_id' => $model->id
                ]
            );
        }

        return response()->json([
            'status' => true,
            'model'  => $model,
        ]);
    }

    public function uploadMedia(Request $request, $id)
    {
        try {
            $model['images'] = [];

            $performer = Performer::where('id', $id)->first();

            foreach ($request->file('images') as $media) {
                $mime_type = $media->getMimeType();
                if (strpos($mime_type, 'image') === 0) {
                    // handle image file
                    $filename = time() . '.' . $media->getClientOriginalExtension();
                    $media_path = public_path('images/user_images/media/' . $filename);
                    Image::make($media)->save($media_path);
                    $path = asset('public/images/user_images/media') . '/' . $filename;
            
                    $userMedia = new PerformerMedia();
                    $userMedia->performer_id = $performer->id;
                    $userMedia->media = $path;
                    $userMedia->save();
                } elseif (strpos($mime_type, 'video') === 0) {
                    // handle video file
                    $filename = time() . '.' . $media->getClientOriginalExtension();
                    $media_path = public_path('videos/user_videos/media/' . $filename);
                    $media->move(public_path('videos/user_videos/media/'), $filename);
                    $path = asset('public/videos/user_videos/media') . '/' . $filename;
            
                    $userMedia = new PerformerMedia();
                    $userMedia->performer_id = $performer->id;
                    $userMedia->media = $path;
                    $userMedia->save();
                }

                $store_medias[] = [
                    'id'    => $userMedia->id,
                    'media' => $userMedia->media
                ];
            }

            return response()->json([
                'status' => true,
                'medias'  => $store_medias,
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
            ], 500);
        }
    }

    public function deleteMedia($id)
    {
        try {
            PerformerMedia::find($id)->delete();

            return response()->json([
                'status' => true
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
            ], 500);
        }
    }

    public function search(Request $request){
        $objects = Performer::where('name', 'like', "%$request->name%")->paginate(10);
        // Check if the 'page' query parameter is present in the request
        $request->query('page', 1);

        return response()->json([
            'status' => true,
            'objects'  => $objects,
        ]);
    }

    public function createTempPerformer(Request $request){
        try {
            $model = Performer::create([
                'name'      => $request->name,
                'phone'     => $request->phone,
            ]);

            return response()->json([
                'status' => true,
                'model' => $model
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
            ], 500);
        }
    }
}
