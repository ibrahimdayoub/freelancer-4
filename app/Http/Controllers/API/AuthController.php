<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Route: http://127.0.0.1:8000/api/sign-up
     * Method: post
     * Takes: user information
     * Returns: user
     * Accessable: by any one
     */
    public function signUp(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'first_name'=>['required','string','max:50'],
            'last_name'=>['required','string','max:50'],
            'email'=>['required','string','max:100','email','unique:users','unique:admins'],
            'password'=>['required','string','min:8'],
        ]);

        if($validator->fails())
        {
            return response()->json([
                'validation_errors'=>$validator->messages(),
            ],400);
        }
        else
        {
            $user=new User;
            $user->first_name=$request->input('first_name');
            $user->last_name=$request->input('last_name');
            $user->email=$request->input('email');
            $user->password=Hash::make($request->input('password'));
            $user->save();

            $token=$user->createToken($user->email.'User_Token',['server:user'])->plainTextToken;

            return response()->json([
                'token'=>$token,
                'user'=>$user,
                'role'=>'User',
                'message'=>'Registered Successfully',
            ],201);
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/sign-in
     * Method: post
     * Takes: user information
     * Returns: user
     * Accessable: by any one
     */
    public function signIn(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'email'=>['required','string','max:100','email'],
            'password'=>['required','string','min:8'],
        ]);

        if($validator->fails())
        {
            return response()->json([
                'validation_errors'=>$validator->messages(),
            ],400);
        }
        else
        {
            $user=User::where('email',$request->email)->first();
            $admin=Admin::where('email',$request->email)->first();

            if( (! $user || ! Hash::check($request->password,$user->password))
              &&(! $admin   || ! Hash::check($request->password,$admin->password))
            )
            {
                return response()->json([
                    'message'=>'Invalid Credentials',
                ],401);
            }
            else if($user)
            {
                $token=$user->createToken($user->email.'_User_Token',['server:user'])->plainTextToken;

                return response()->json([
                    'token'=>$token,
                    'user'=>$user,
                    'role'=>'User',
                    'message'=>'Logged In Successfully',
                ],200);
            }
            else if($admin)
            {
                $token=$admin->createToken($admin->email.'_Admin_Token',['server:admin'])->plainTextToken;

                return response()->json([
                    'token'=>$token,
                    'admin'=>$admin,
                    'role'=>'Admin',
                    'message'=>'Logged In Successfully',
                ],200);
            }
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/sign-out
     * Method: post
     * Takes: no thing
     * Returns: no thing
     * Accessable: by admin and user role
     */
    public function signOut()
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'message'=>'Logged Out Successfully'
        ],200);
    }
}
