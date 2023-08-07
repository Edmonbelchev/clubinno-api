<?php

namespace App\Http\Controllers\API;
use Image;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EntertainmentPlace;
use App\Http\Controllers\Controller;
use App\Models\UserEntertainmentPlaces;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class EntertainmentPlaceController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => ['required'],
            'name'    => ['required', 'string', 'max:128'],
            'city'    => ['required', 'string', 'max:128'],
            'address' => ['required', 'string', 'max:128'],
            'phone'   => ['required', 'string', 'max:128'],
            'type'    => ['required', 'in:1,2,3,4'],
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

        $model = EntertainmentPlace::create([
            'name'      => $request->name,
            'city'      => $request->city,
            'address'   => $request->address,
            'phone'     => $request->phone,
            'type'      => $request->type,
            'image'     => $path ?? ''
        ]);

        if($model){
            UserEntertainmentPlaces::create([
                'user_id' => $request->user_id,
                'entertainment_place_id' => $model->id
                ]
            );
        }

        return response()->json([
            'status' => true,
            'model'  => $model,
        ]);
    }

    public function show(Request $request, $id)
    {
        $mainProfile = EntertainmentPlace::where('id', $id)->first();
        $isMainUser = $mainProfile->mainUser()->contains($request->user_id) ?? false;
        $personnel = $mainProfile->personnel();
        $user = User::where('id', $request->user_id)->with('entertainment_places')->first();

        return response()->json([
            'status'      => true,
            'profiles'    => $user->entertainment_places,
            'mainProfile' => $mainProfile,
            'isMainUser'  => $isMainUser,
            'personnel'   => $personnel
        ]);
    }

    public function profiles($id){
        /* RETRIEVE USER */
        $profiles = User::where('id', $id)->with('entertainment_places')->first();

        /* RETRIEVE ENTERTAINMENT PLACE */
        // $place_obj = EntertainmentPlace::where('id', $id)->first();
        // $isMainUser = $place_obj->mainUser()->contains($id) ?? false;
        // $personnel = $place_obj->personnel();

        return response()->json([
            'status'     => true,
            'profiles'       => $profiles,
            // 'place_obj'  => $place_obj,
            // 'isMainUser' => $isMainUser,
            // 'personnel'  => $personnel
        ]);
    }
    
    public function edit($id){
        $profile = EntertainmentPlace::where('id', $id)->first();

        return response()->json([
            'status'  => true,
            'profile' => $profile,
        ]);
    }

    public function update(Request $request, $id){
        $model = EntertainmentPlace::where('id', $id)->first();

        $request->validate([
            'name'    => ['required', 'string', 'max:128'],
            'city'    => ['required', 'string', 'max:128'],
            'address' => ['required', 'string', 'max:128'],
            'phone'   => ['required', 'string', 'max:128'],
            'type'    => ['required', 'in:1,2,3,4'],
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ]);

        if($request->hasFile("image")){
            $image = $request->file('image');
            $filename = time().'.'.$image->getClientOriginalExtension();
            $image_path = public_path('images/user_images/'.$filename);
            Image::make($image)->fit(300, 300)->save($image_path);
            $path = asset('public/images/user_images/') . '/' . $filename;
        }else {
            $path = $model->image;
        }

        $model->update([
            'name'    => $request->name,
            'city'    => $request->city,
            'address' => $request->address,
            'phone'   => $request->phone,
            'type'    => $request->type,
            'image'   => $path
        ]);

        return response()->json([
            'status'  => true,
        ]);
    }

    public function createPersonnel(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => '5', // PERSONNEL
            'phone' => $request->phone
        ]);

        UserEntertainmentPlaces::create([
            'user_id' => $user->id,
            'entertainment_place_id' => $id
            ]
        );

        return response()->json([
            'status'  => true,
            'user' => $user,
        ]);
    }
}
