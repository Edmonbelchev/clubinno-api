<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8'],
                'type' => ['required', 'in:1,2,3,4'],
            ]);

            if ($validator->fails()) {
                // return errors as JSON response
                return response()->json(['errors' => $validator->errors()]);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => $request->type,
                'phone' => $request->phone
            ]);

            event(new Registered($user));

            Auth::login($user);

            $token = $user->createToken("API TOKEN")->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'user' => $user,
                'token' => $token
            ]);

        } catch (\Throwable $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
            ], 500);
        }
    }
    /**    
    * Login The User
    * @param Request $request
    * @return User
    */
    public function login(Request $request){
        try{
            $validate = Validator::make($request->all(), [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ]);
            }
            
            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                    'errors' => $validate->errors()
                ]);
            }

            $user = User::where('email', $request->email)->first();

            $token = $user->createToken("API TOKEN")->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'user' => $user,
                'token' => $token
                // 'token' => $user->createToken("API TOKEN")->plainTextToken
            ]);

        }catch (\Throwable $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
            ]);
        }
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        $request->session()->invalidate();
    
        // Added this
        $request->session()->regenerateToken();
        $request->session()->token();
        
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
