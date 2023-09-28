<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Exception;

class ProductController extends Controller
{
    /**
     * Route: http://127.0.0.1:8000/api/view-products
     * Method: get
     * Takes: no thing
     * Returns: all products
     * Accessable: by admin and user roles
     */
    public function viewProducts()
    {
        $products = Product::all();
        $result = [];

        for ($i = 0; $i < count($products); $i++) {
            $products[$i]->sizes = json_decode($products[$i]->sizes);
            array_push($result, $products[$i]);
        }

        $products = $result;

        return response()->json([
            'products' => $products,
            'message' => 'Products Fetched Successfully',
        ], 200);
    }

    /**
     * Route: http://127.0.0.1:8000/api/add-product
     * Method: post
     * Takes: product information
     * Returns: product
     * Accessable: by admin role
     */
    // 02 Add Product (Admin)
    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100', 'unique:products'],
            'sizes' => ['required', 'array', 'min:1', 'max:7'],
            'sizes.*' => ['required', 'string', 'in:sm,md,lg,xl,2xl,3xl,4xl'],
            'price' => ['required', 'integer', 'min:1'],
            'path' => ['required', 'file']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'validation_errors' => $validator->messages(),
            ], 400);
        } else {
            $product = new Product;
            $product->name = $request->input('name');
            $product->sizes = json_encode($request->input('sizes'));
            $product->price = $request->input('price');

            if ($request->hasFile('path')) {
                $file = $request->file('path');
                $extension = $file->getClientOriginalExtension();

                if ($extension === "jpg" || $extension === "png") {
                    $filename = time() . '.' . $extension;
                    $file->move('Uploads/Products/', $filename);
                    $product->path = 'Uploads/Products/' . $filename;
                } else {
                    return response()->json([
                        'message' => 'We need image file (.jpg or .png)',
                    ], 400);
                }
            } else {
                return response()->json([
                    'message' => 'We need image file (.jpg or .png)',
                ], 400);
            }

            $product->save();
            $product->sizes = json_decode($product->sizes);
            return response()->json([
                'product' => $product,
                'message' => 'Product Added Successfully',
            ], 201);
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/view-product/{id}
     * Method: get
     * Takes: product id
     * Returns: product
     * Accessable: by admin and user roles
     */
    public function viewProduct($id)
    {
        $product = Product::find($id);

        if ($product) {
            $product->sizes = json_decode($product->sizes);
            return response()->json([
                'product' => $product,
                'message' => 'Product Fetched Successfully',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Product Is Not Found',
            ], 404);
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/update-product/{id}
     * Method: post
     * Takes: product information, product id
     * Returns: product
     * Accessable: by admin role
     */
    public function updateProduct(Request $request, $id)
    {
        $validationArray = [
            'sizes' => ['required', 'array', 'min:1', 'max:7'],
            'sizes.*' => ['required', 'string', 'in:sm,md,lg,xl,2xl,3xl,4xl'],
            'price' => ['required', 'integer', 'min:1'],
            'path' => ['required', 'file']
        ];

        $pro_e = Product::find($id);

        if ($pro_e && $pro_e->name == $request->input('name')) {
            $validationArray['name'] = ['required', 'string', 'max:100'];
        } else {
            $validationArray['name'] = ['required', 'string', 'max:100', 'unique:products'];
        }

        $validator = Validator::make($request->all(), $validationArray);

        if ($validator->fails()) {
            return response()->json([
                'validation_errors' => $validator->messages(),
            ], 400);
        } else {
            $product = Product::find($id);
            if ($product) {
                $product->name = $request->input('name');
                $product->sizes = json_encode($request->input('sizes'));
                $product->price = $request->input('price');

                if ($request->hasFile('location')) {
                    $path = $product->location;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                    $file = $request->file('location');
                    $extension = $file->getClientOriginalExtension();

                    if ($extension === "jpg" || $extension === "png") {
                        $filename = time() . '.' . $extension;
                        $file->move('Uploads/Products/', $filename);
                        $product->location = 'Uploads/Products/' . $filename;
                    } else {
                        return response()->json([
                            'message' => 'We need image file (.jpg or .png)',
                        ], 400);
                    }
                } else {
                    $product->location = $product->location;
                }

                $product->save();
                $product->sizes = json_decode($product->sizes);
                return response()->json([
                    "product" => $product,
                    'message' => 'Product Updated Successfully',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No Product Id Found',
                ], 404);
            }
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/delete-product/{id}
     * Method: delete
     * Takes: product id
     * Returns: no thing
     * Accessable: by admin role
     */
    public function deleteProduct($id)
    {
        $product = Product::find($id);
        if ($product) {
            $path = $product->product->location;
            if (File::exists($path)) {
                File::delete($path);
            }

            $product->delete();

            return response()->json([
                'message' => 'Product Deleted Successfully'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Product Is Not Found',
            ], 404);
        }
    }

    /**
     * Route: http://127.0.0.1:8000/api/search_product/{key}
     * Method: post
     * Takes: no thing
     * Returns: matching products
     * Accessable: by admin and user roles
     */
    public function searchProduct($key)
    {
        $products = Product::where('name', 'LIKE', '%' . $key . '%')->get();
        $result = [];

        for ($i = 0; $i < count($products); $i++) {
            $products[$i]->sizes = json_decode($products[$i]->sizes);
            array_push($result, $products[$i]);
        }

        $products = $result;

        return response()->json([
            'product' => $products,
            'message' => 'Products Fetched Successfully',
        ], 200);
    }

    /**
     * Route: http://127.0.0.1:8000/api/pay_products
     * Method: post
     * Takes: products
     * Returns: no thing
     * Accessable: by user role
     */
    public function payProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart' => ['required', 'array'],
            'cart.*' => ['required', 'array'],
            'cart.*.product_name' => ['required', 'string', 'max:100'],
            'cart.*.product_size' => ['required', 'string', 'in:sm,md,lg,xl,2xl,3xl,4xl'],
            'cart.*.product_price' => ['required', 'integer', 'min:1'],
            'cart.*.product_quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'validation_errors' => $validator->messages(),
            ], 400);
        } else {
            $cart = $request->input('cart');
            $total_price = 0;

            for ($i = 0; $i < count($cart); $i++) {
                $total_price += $cart[$i]['product_price'] * $cart[$i]['product_quantity'];
            }

            $user = User::find(auth()->user()->id);

            $oldBalance = $user->balance;
            $newBalance = $oldBalance - $total_price;

            if ($newBalance >= 0) {
                $user->balance = $newBalance;
                $user->save();

                $payment = new Payment;
                $payment->cart = json_encode($cart);
                $payment->total_price = $total_price;
                $payment->user_id = auth()->user()->id;
                $payment->satus = "active";
                $payment->save();

                $payment->cart = json_decode($payment->cart);

                return response()->json([
                    'old-balance' => $oldBalance . '$',
                    'new-balance' => $newBalance . '$',
                    'payment' => $payment,
                    'message' => 'Payment completed Successfully',
                ], 200);

            } else {
                return response()->json([
                    'your-balance' => $oldBalance . '$',
                    'required-balance' => ($total_price - $oldBalance) . '$',
                    'message' => 'No enough money in your account'
                ], 400);
            }
        }
    }
}
