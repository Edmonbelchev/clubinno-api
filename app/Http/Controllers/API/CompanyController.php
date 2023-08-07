<?php

namespace App\Http\Controllers\API;
use Image;

use App\Models\Companies;
use Illuminate\Http\Request;
use App\Models\UserCompanies;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
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
            'entry_access' => ['required'],
            'image'   => 'required|image|mimes:jpeg,png,jpg,gif|max:3048',
        ]);

        if ($validator->fails()) {
            // return errors as JSON response
            return response()->json(['errors' => $validator->errors()]);
        }

        if ($request->hasFile("image")) {
            $image      = $request->file('image');
            $filename   = time() . '.' . $image->getClientOriginalExtension();
            $image_path = public_path('images/user_images/' . $filename);

            Image::make($image)->resize(300, 300)->save($image_path);
            $path       = asset('public/images/user_images/') . '/' . $filename;
        }

        $model = Companies::create([
            'name'      => $request->name,
            'youtube'   => $request->youtube,
            'spotify'   => $request->spotify,
            'phone'     => $request->phone,
            'entry_access' => $request->antry_access,
            'image'     => $path ?? ''
        ]);

        if ($model) {
            UserCompanies::create(
                [
                    'user_id' => $request->user_id,
                    'company_id' => $model->id
                ]
            );
        }

        return response()->json([
            'status' => true,
            'model'  => $model,
        ]);
    }
}

?>