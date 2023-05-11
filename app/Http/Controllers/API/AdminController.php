<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Route: http://127.0.0.1:8000/api/view-admins
     * Method: get
     * Takes: no thing
     * Returns: all admins
     * Accessable: by admin role
     */
    public function viewAdmins()
    {
        $admins=Admin::all();

        return response()->json([
            'admins'=>$admins,
        ],200);
    }

    /**
     * Route: http://127.0.0.1:8000/api/add-admin
     * Method: post
     * Takes: admin information
     * Returns: admin
     * Accessable: by admin role
     */
    public function addAdmin(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'first_name'=>['required','string','max:50'],
            'last_name'=>['required','string','max:50'],
            'email'=>['required','string','max:100','email','unique:admins','unique:users'],
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
            $admin=new Admin;
            $admin->first_name=$request->input('first_name');
            $admin->last_name=$request->input('last_name');
            $admin->email=$request->input('email');
            $admin->password=Hash::make($request->input('password'));
            $admin->save();

            return response()->json([
                'admin'=>$admin,
                'message'=>'Admin Added Successfully',
            ],201);
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/view-admin/{id}
     * Method: get
     * Takes: admin id
     * Returns: admin
     * Accessable: by admin role
     */
    public function viewAdmin($id)
    {
        $admin=Admin::find($id);

        if($admin)
        {
            return response()->json([
                'admin'=>$admin,
                'message'=>'Admin Fetched Successfully',
            ],200);
        }
        else
        {
            return response()->json([
                'message'=>'Admin Is Not Found',
            ],404);
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/update-admin/{id}
     * Method: post
     * Takes: admin information, admin id
     * Returns: admin
     * Accessable: by admin role
     */
    public function updateAdmin(Request $request,$id)
    {
        $validationArray=[
            'first_name'=>['required','string','max:50'],
            'last_name'=>['required','string','max:50'],
            'password'=>['required','string','min:8'],
        ];

        $admin_e=Admin::find($id);

        if($admin_e && $admin_e->email==$request->input('email'))
        {
            $validationArray['email']=['required','string','max:100','email','unique:users'];
        }
        else
        {
            $validationArray['email']=['required','string','max:100','email','unique:admins','unique:users'];
        }

        $validator=Validator::make($request->all(),$validationArray);

        if($validator->fails())
        {
            return response()->json([
                'validation_errors'=>$validator->messages(),
            ],400);
        }
        else
        {
            $admin=Admin::find($id);
            if($admin)
            {
                $admin->first_name=$request->input('first_name');
                $admin->last_name=$request->input('last_name');
                $admin->email=$request->input('email');
                $admin->password = $request->input('password')==="useOldPassword" ? $admin->password : Hash::make($request->input('password'));

                if(auth()->user()->id==$id)
                {
                    $admin->save();
                    return response()->json([
                        'message'=>'Your Account Updated Successfully',
                    ],200);
                }
                else
                {
                    $admin->save();
                    return response()->json([
                        'message'=>'Admin Updated Successfully',
                    ],200);
                }
            }
            else
            {
                return response()->json([
                    'message'=>'Admin Is Not Found',
                ],404);
            }
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/delete-admin/{id}
     * Method: delete
     * Takes: admin id
     * Returns: no thing
     * Accessable: by admin role
     */
    public function deleteAdmin($id)
    {
        $admin=Admin::find($id);
        if($admin)
        {
            $admins=Admin::all();

            if(count($admins)>1)
            {
                if(auth()->user()->id==$id)
                {
                    auth()->user()->tokens()->delete();
                    $admin->delete();
                    return response()->json([
                        'message'=>'Your Account Deleted Successfully'
                    ],200);
                }
                else
                {
                    $admin->delete();
                    return response()->json([
                        'message'=>'Admin Deleted Successfully',
                    ],200);
                }
            }
            else
            {
                return response()->json([
                    'message'=>'You Are Last Admin In The System, Give Another Admin And Try Latter',
                ],400);
            }
        }
        else
        {
            return response()->json([
                'message'=>'Admin Is Not Found',
            ],404);
        }
    }
}
