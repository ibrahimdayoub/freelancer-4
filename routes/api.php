<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ProductController;

//______________________________________________________________________________

Route::post('test',function(Request $request){
    return response()->json([
        'data'=>$request->all(),
    ],200);
});

//______________________________________________________________________________

Route::post('sign-up',[AuthController::class,'signUp']);
Route::post('sign-in',[AuthController::class,'signIn']);

//Products
Route::get('view-products',[ProductController::class,'viewProducts']);
Route::get('view-product/{id}',[ProductController::class,'viewProduct']);
Route::post('search-product/{key}',[ProductController::class,'searchProduct']);

//______________________________________________________________________________

Route::middleware(['auth:sanctum'])->group(function(){
 
    Route::post('sign-out',[AuthController::class,'signOut']);

    //Users
    Route::get('view-user/{id}',[UserController::class,'viewUser']);
    Route::put('update-user/{id}',[UserController::class,'updateUser']);
    Route::delete('delete-user/{id}',[UserController::class,'deleteUser']);
});

//______________________________________________________________________________

Route::middleware(['auth:sanctum','isAdmin'])->group(function(){

    //Admins
    Route::get('view-admins',[AdminController::class,'viewAdmins']);
    Route::post('add-admin',[AdminController::class,'addAdmin']);
    Route::get('view-admin/{id}',[AdminController::class,'viewAdmin']);
    Route::put('update-admin/{id}',[AdminController::class,'updateAdmin']);
    Route::delete('delete-admin/{id}',[AdminController::class,'deleteAdmin']);

    //Users
    Route::get('view-users',[UserController::class,'viewUsers']);
    Route::post('add-user',[UserController::class,'addUser']);
    Route::put('increase-balance/{id}',[UserController::class,'increaseBalance']);

    //Products
    Route::post('add-product',[ProductController::class,'addProduct']);
    Route::put('update-product/{id}',[ProductController::class,'updateProduct']);
    Route::delete('delete-product/{id}',[ProductController::class,'deleteProduct']);

    Route::get('authenticated-admin',function(){
        return response()->json(['message'=>'You Are Authenticated','id'=>auth()->user()->id],200);
    });
});

//______________________________________________________________________________

Route::middleware(['auth:sanctum','isUser'])->group(function(){
 
    //User
    Route::put('decrease-balance/{id}',[UserController::class,'decreaseBalance']);

    Route::get('authenticated-user',function(){
        return response()->json(['message'=>'You Are Authenticated','id'=>auth()->user()->id],200);
    });
});

//______________________________________________________________________________