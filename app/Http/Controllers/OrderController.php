<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="API untuk mengelola pesanan"
 * )
 */
class OrderController extends Controller
{
    /**
     * Membuat pesanan baru.
     *
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Membuat order baru",
     *     security={{ "bearerAuth":{} }},
     *     description="Membuat pesanan baru berdasarkan produk dan jumlah yang diminta.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order berhasil dibuat",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Order berhasil dibuat"),
     *             @OA\Property(property="order", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="product_id", type="integer", example=3),
     *                 @OA\Property(property="quantity", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Stok produk tidak mencukupi"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product = Product::findOrFail($request->product_id);

        // Cek apakah stok cukup
        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Stok produk tidak mencukupi'
            ], 400);
        }

        // Kurangi stok produk
        $product->stock -= $request->quantity;
        $product->save();

        // Buat order baru
        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Order berhasil dibuat',
            'order' => $order
        ], 201);
    }

    /**
     * Mengupdate status order.
     *
     * @OA\Put(
     *     path="/api/orders/{id}/status",
     *     tags={"Orders"},
     *     summary="Memperbarui status order",
     *     description="Memperbarui status pesanan berdasarkan ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID order",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", example="completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status berhasil diperbarui",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Status berhasil diperbarui"),
     *             @OA\Property(property="order", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="completed")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order tidak ditemukan"
     *     )
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        $order->update(['status' => $request->status]);

        return response()->json(['message' => 'Status berhasil diperbarui', 'order' => $order]);
    }
}
