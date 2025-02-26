<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Product",
 *     title="Product",
 *     description="Product model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Laptop Gaming"),
 *     @OA\Property(property="price", type="number", format="float", example=15000000),
 *     @OA\Property(property="stock", type="integer", example=10)
 * )
 */
class ProductController extends Controller
{
    /**
     * Mendapatkan daftar produk.
     *
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Mendapatkan daftar produk",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Pencarian berdasarkan nama produk",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar produk",
     *         @OA\JsonContent(type="array",
     *             @OA\Items(ref="#/components/schemas/Product")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $data = $request->input('q');
        $payload = Product::where('name', 'like', "%$data%")->get();

        return response()->json([
            'status' => true,
            'message' => 'List Data Product',
            'data' => $payload
        ], 200);
    }

    /**
     * Menambahkan produk baru.
     *
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Menambahkan produk baru",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price", "stock"},
     *             @OA\Property(property="name", type="string", example="Laptop"),
     *             @OA\Property(property="price", type="number", example=15000000),
     *             @OA\Property(property="stock", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Produk berhasil ditambahkan",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $product = Product::create($request->all());
        return response()->json([
            'message' => 'Data Product berhasil ditambahkan',
            'product' => $product
        ], 201);
    }

    /**
     * Mendapatkan detail produk.
     *
     * @OA\Get(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Mendapatkan detail produk",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID produk",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail produk",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produk tidak ditemukan"
     *     )
     * )
     */
    public function show(Product $product)
    {
        return response()->json([
            'status' => true,
            'message' => 'Data Product',
            'data' => $product
        ]);
    }

    /**
     * Memperbarui produk.
     *
     * @OA\Put(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Memperbarui produk",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID produk",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Monitor"),
     *             @OA\Property(property="price", type="number", example=2000000),
     *             @OA\Property(property="stock", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produk berhasil diperbarui"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'stock' => 'sometimes|integer',
        ]);

        $product->update($request->only(['name', 'price', 'stock']));

        return response()->json([
            'message' => 'Data Product berhasil diperbarui',
            'product' => $product
        ]);
    }

    /**
     * Menghapus produk.
     *
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Menghapus produk",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID produk",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produk berhasil dihapus"
     *     )
     * )
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product tidak ditemukan'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Data Product berhasil dihapus'
        ]);
    }
}
