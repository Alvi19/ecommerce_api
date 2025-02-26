<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Invoices",
 *     description="API untuk mengelola invoice"
 * )
 */
class InvoiceController extends Controller
{
    /**
     * Generate invoice untuk order tertentu.
     *
     * @OA\Post(
     *     path="/api/invoices/{orderId}",
     *     tags={"Invoices"},
     *     summary="Generate invoice berdasarkan order",
     *     description="Membuat invoice berdasarkan order yang diberikan.",
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="ID order",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice berhasil dibuat",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="total_amount", type="number", format="float", example=100.50),
     *             @OA\Property(property="invoice_date", type="string", format="date", example="2025-02-26")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order tidak ditemukan"
     *     )
     * )
     */
    public function generateInvoice($orderId, Request $request)
    {
        $order = Order::with('product')->findOrFail($orderId);

        // Hitung total harga (harga produk * jumlah)
        $totalAmount = $order->product->price * $order->quantity;

        $invoice = Invoice::create([
            'order_id' => $orderId,
            'total_amount' => $totalAmount,
            'invoice_date' => now(),
        ]);

        return response()->json($invoice);
    }
}
