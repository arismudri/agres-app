<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Lib\ResponseFormatter;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    protected $response;

    public function __construct()
    {
        $this->response = new ResponseFormatter();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = Product::with("variants")->get();
            return $this->response->success("Successfully get all product", $data);
        } catch (Exception $e) {

            return $this->response->fail($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $data = $request->only("name", "sku", "brand", "description", "variants");

            $rule = [
                "name" => ["required", "string", "min:2", "max:100"],
                "sku" => ["required", "string", "min:2", "max:100"],
                "brand" => ["required", "string", "min:2", "max:100"],
                "description" => ["nullable", "string", "max:255"],
                "variants.*" => ["nullable", "array"],
                "variants.*.name" => ["required", "string", "min:2", "max:100"],
                "variants.*.sku" => ["required", "string", "min:2", "max:100"],
                "variants.*.price" => ["nullable", "numeric"],
            ];

            $validate = Validator::make($data, $rule);

            if ($validate->fails()) {
                return $this->response->fail("Validation fail", $validate->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $dataProduct = collect($data)->only("name", "sku", "brand", "description");
            $product = Product::create($dataProduct->toArray());

            $dataVariants = collect($data["variants"] ?? [])->map(function ($item) {
                return new ProductVariant($item);
            });

            $product->variants()->saveMany($dataVariants);
            $product->variants;

            return $this->response->success("Successfully create new product", $product);
        } catch (Exception $e) {

            return $this->response->fail($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $product_id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {

        try {
            $rule = [
                "id" => ["required", "string", Rule::exists("products", "id")->whereNull("deleted_at")],
            ];

            $validate = Validator::make(compact("id"), $rule);

            if ($validate->fails()) {
                return $this->response->fail("Validation fail", $validate->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $product = Product::findOrFail($id);
            $product->variants;

            return $this->response->success("Successfully get product", $product);
        } catch (Exception $e) {

            return $this->response->fail($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $product_id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $id)
    {
        try {
            $data = collect($request->only("name", "sku", "brand", "description", "variants"))->put("id", $id)->toArray();

            $rule = [
                "id" => ["required", "string", Rule::exists("products", "id")->whereNull("deleted_at")],
                "name" => ["required", "string", "min:2", "max:100"],
                "sku" => ["required", "string", "min:2", "max:100"],
                "brand" => ["required", "string", "min:2", "max:100"],
                "description" => ["nullable", "string", "max:255"],
                "variants.*" => ["nullable", "array"],
                "variants.*.name" => ["required", "string", "min:2", "max:100"],
                "variants.*.sku" => ["required", "string", "min:2", "max:100"],
                "variants.*.price" => ["nullable", "numeric"],
            ];

            $validate = Validator::make($data, $rule);

            if ($validate->fails()) {
                return $this->response->fail("Validation fail", $validate->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $product = Product::findOrFail($id);
            $dataProduct = collect($data)->only("name", "sku", "brand", "description");
            $product->update($dataProduct->toArray());

            $dataVariants = collect($data)->only("variants")->map(function ($item) {
                return new ProductVariant($item);
            });

            $product->variants()->saveMany($dataVariants);
            $product->variants;

            return $this->response->success("Successfully update product", $product);
        } catch (Exception $e) {

            return $this->response->fail($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $product_id
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $id)
    {
        try {
            $rule = [
                "id" => ["required", "string", Rule::exists("products", "id")->whereNull("deleted_at")],
            ];

            $validate = Validator::make(compact("id"), $rule);

            if ($validate->fails()) {
                return $this->response->fail("Validation fail", $validate->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $product = Product::findOrFail($id);

            $product->variants()->delete();
            $product->delete();

            return $this->response->success("Successfully delete product", $product);
        } catch (Exception $e) {

            return $this->response->fail($e->getMessage());
        }
    }
}
