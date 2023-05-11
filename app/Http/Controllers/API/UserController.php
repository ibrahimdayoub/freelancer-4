<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Route: http://127.0.0.1:8000/api/view-users
     * Method: get
     * Takes: no thing
     * Returns: all users
     * Accessable: by admin role
     */
    public function viewUsers()
    {
        $users=User::all();

        return response()->json([
            'users'=>$users,
        ],200);
    }

    /**
     * Route: http://127.0.0.1:8000/api/add-user
     * Method: post
     * Takes: user information
     * Returns: user
     * Accessable: by admin role
     */
    public function addUser(Request $request)
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

            return response()->json([
                'message'=>'User Added Successfully',
            ],201);
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/view-user/{id}
     * Method: get
     * Takes: user id
     * Returns: user
     * Accessable: by admin and user roles
     */
    public function viewUser($id)
    {
        $user=User::find($id);

        if($user)
        {
            return response()->json([
                'user'=>$user,
                'message'=>'User Fetched Successfully',
            ],200);
        }
        else
        {
            return response()->json([
                'message'=>'User Is Not Found',
            ],404);
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/update-user/{id}
     * Method: post
     * Takes: user information, user id
     * Returns: user
     * Accessable: by admin and user roles
     */
    public function updateUser(Request $request,$id)
    {
        $validationArray=[
            'first_name'=>['required','string','max:50'],
            'last_name'=>['required','string','max:50'],
            'password'=>['required','string','min:8'],
        ];

        $user_e=User::find($id);

        if($user_e && $user_e->email==$request->input('email'))
        {
            $validationArray['email']=['required','string','max:100','email','unique:admins'];
        }
        else
        {
            $validationArray['email']=['required','string','max:100','email','unique:users','unique:admins'];
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
            $user=User::find($id);
            if($user)
            {
                $user->first_name=$request->input('first_name');
                $user->last_name=$request->input('last_name');
                $user->email=$request->input('email');
                $user->password = $request->input('password')==="useOldPassword" ? $user->password : Hash::make($request->input('password'));

                if(auth()->user()->id==$id && auth()->user()->tokenCan('server:user'))
                {
                    $user->save();
                    return response()->json([
                        'message'=>'Your Account Updated Successfully',
                    ],200);
                }
                else if(auth()->user()->tokenCan('server:admin'))
                {
                    $user->save();
                    return response()->json([
                        'message'=>'User Updated Successfully',
                    ],200);
                }
                else
                {
                    return response()->json([
                        'message'=>'No Permission To Update Process',
                    ],401);
                }
            }
            else
            {
                return response()->json([
                    'message'=>'User Is Not Found',
                ],404);
            }
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/delete-user/{id}
     * Method: delete
     * Takes: user id
     * Returns: no thing
     * Accessable: by admin and user roles
     */
    public function deleteUser($id)
    {
        $user=User::find($id);
        if($user)
        {
            if(auth()->user()->id==$id && auth()->user()->tokenCan('server:user'))
            {
                auth()->user()->tokens()->delete();
                $user->delete();
                return response()->json([
                    'message'=>'Your Account Deleted Successfully'
                ],200);
            }
            else if(auth()->user()->tokenCan('server:admin'))
            {
                $user->delete();
                return response()->json([
                    'message'=>'User Deleted Successfully',
                ],200);
            }
            else
            {
                return response()->json([
                    'message'=>'No Permission To Delete Process',
                ],401);
            }
        }
        else
        {
            return response()->json([
                'message'=>'User Is Not Found',
            ],404);
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/increase-balance/{id}
     * Method: put
     * Takes: balance, user id
     * Returns: no thing
     * Accessable: by admin role
     */
    public function increaseBalance(Request $request,$id)
    {
        $validator=Validator::make($request->all(),[
            'balance'=>['required','integer','min:1'],
        ]);

        if($validator->fails())
        {
            return response()->json([
                'validation_errors'=>$validator->messages(),
            ],400);
        }
        else
        {
            $user=User::find($id);
            if($user)
            {
                $oldBalance = $user->balance;
                $newBalance = $oldBalance + $request->input('balance');
                $user->balance = $newBalance;
                $user->save();
                return response()->json([
                    'old-balance'=>$oldBalance.'$',
                    'new-balance'=>$newBalance.'$',
                    'message'=>'Balance added successfully'
                ],200);
            }
            else
            {
                return response()->json([
                    'message'=>'User Is Not Found',
                ],404);
            }
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/decrese-balance/{id}
     * Method: put
     * Takes: total price, user id
     * Returns: no thing
     * Accessable: by user role
     */
    public function decreaseBalance(Request $request,$id)
    {
        $validator=Validator::make($request->all(),[
            'payment'=>['required','integer','min:1'],
        ]);

        if($validator->fails())
        {
            return response()->json([
                'validation_errors'=>$validator->messages(),
            ],400);
        }
        else
        {
            $user=User::find($id);
            if($user)
            {
                $oldBalance = $user->balance;
                $newBalance = $oldBalance - $request->input('payment');

                if($newBalance >= 0)
                {
                    $user->balance = $newBalance;
                    $user->save();
                    return response()->json([
                        'old-balance'=>$oldBalance.'$',
                        'new-balance'=>$newBalance.'$',
                        'message'=>'Payment completed successfully'
                    ],200);
                }
                else{
                    return response()->json([
                        'old-balance'=>$oldBalance.'$',
                        'required-balance'=>($request->input('payment')-$oldBalance).'$',
                        'payment'=>$request->input('payment').'$',
                        'message'=>'No enough money in your account'
                    ],400);
                }
            }
            else
            {
                return response()->json([
                    'message'=>'User Is Not Found',
                ],404);
            }
        }
    }
}
