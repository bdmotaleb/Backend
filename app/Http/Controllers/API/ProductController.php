<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Image;
use Exception;

class ProductController extends Controller
{
    /**
     * ProductController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $products = Product::orderBy('id', 'DESC')->get();

        return sendResponse(ProductResource::collection($products), 'Product retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|min:5|max:255',
            'price'       => 'required',
            'image'       => 'required|string',
            'description' => 'required|string|min:10'
        ]);

        if ($validator->fails()) return sendError('Validation Error.', $validator->errors(), 422);

        try {
            $image = $this->image_name_generator($request->image);

            $product = Product::create([
                'title'       => $request->title,
                'price'       => $request->price,
                'image'       => $image,
                'description' => $request->description
            ]);

            Image::make($request->image)->resize(100, 100)->save(public_path('uploads/products/') . $image);

            $success = new ProductResource($product);
            $message = 'Yay! A product has been successfully created.';
        } catch (Exception $e) {
            $success = [];
            $message = 'Oops! Unable to create a new product.';
        }

        return sendResponse($success, $message);
    }

    public function image_name_generator($data)
    {
        $file = explode(';', $data);
        $file = explode('/', $file[0]);
        $ex   = end($file);

        return rand(11111, 99999) . date('ymdhis.') . $ex;
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (is_null($product)) return sendError('Product not found.');

        return sendResponse(new ProductResource($product), 'Product retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|min:5|max:255',
            'price'       => 'required',
            'image'       => 'required|string',
            'description' => 'required|string|min:10'
        ]);

        if ($validator->fails()) return sendError('Validation Error.', $validator->errors(), 422);

        try {
            $product->title       = $request->title;
            $product->price       = $request->price;
            $product->description = $request->description;

            if ($request->image !== $product->image) {
                $image          = $this->image_name_generator($request->image);
                $product->image = $image;
                Image::make($request->image)->resize(100, 100)->save(public_path('uploads/products/') . $image);
            }

            $product->save();

            $success = new ProductResource($product);
            $message = 'Yay! Product has been successfully updated.';
        } catch (Exception $e) {
            $success = [];
            $message = 'Oops, Failed to update the product.';
        }

        return sendResponse($success, $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product)
    {
        try {
            if (file_exists(public_path('uploads/products/') . $product->image)) unlink(public_path('uploads/products/') . $product->image);
            $product->delete();
            return sendResponse([], 'The product has been successfully deleted.');
        } catch (Exception $e) {
            return sendError('Oops! Unable to delete product.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeItems(Request $request)
    {
        $sl = 0;
        foreach ($request->data as $id) {
            $product = Product::find($id);
            if (file_exists(public_path('uploads/products/') . $product->image)) unlink(public_path('uploads/products/') . $product->image);
            $product->delete();
            $sl++;
        }
        $success = $sl > 0;

        return response()->json(['success' => $success, 'total' => $sl], 200);
    }

}
