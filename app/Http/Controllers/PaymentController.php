<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Post(
 *     path="/api/payment",
 *     summary="Proses pembayaran",
 *     description="Endpoint untuk memproses pembayaran dan memperbarui status transaksi",
 *     tags={"Payment"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"order_id", "payment_method", "amount_paid"},
 *             @OA\Property(property="order_id", type="integer", example=1),
 *             @OA\Property(property="payment_method", type="string", example="credit_card"),
 *             @OA\Property(property="amount_paid", type="number", format="float", example=500000.00)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Pembayaran berhasil",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Pembayaran berhasil"),
 *             @OA\Property(property="order_id", type="integer", example=1),
 *             @OA\Property(property="status", type="string", example="completed"),
 *             @OA\Property(property="payment_details", type="object",
 *                 @OA\Property(property="id", type="integer", example=10),
 *                 @OA\Property(property="order_id", type="integer", example=1),
 *                 @OA\Property(property="payment_method", type="string", example="credit_card"),
 *                 @OA\Property(property="amount_paid", type="number", format="float", example=500000.00),
 *                 @OA\Property(property="status", type="string", example="paid"),
 *                 @OA\Property(property="payment_date", type="string", format="date-time", example="2025-02-26T21:01:21.000Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Jumlah pembayaran kurang"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Invoice tidak ditemukan"
 *     )
 * )
 */
class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        // Validasi input
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string',
            'amount_paid' => 'required|numeric|min:1',
        ]);

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // Cari order berdasarkan ID
            $order = Order::findOrFail($request->order_id);

            // Cek apakah sudah ada invoice
            $invoice = Invoice::where('order_id', $order->id)->first();

            if (!$invoice) {
                return response()->json(['message' => 'Invoice tidak ditemukan'], 404);
            }

            // Cek apakah pembayaran cukup
            if ($request->amount_paid < $invoice->total_amount) {
                return response()->json(['message' => 'Jumlah pembayaran kurang'], 400);
            }

            // Simpan data pembayaran ke tabel payments
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $request->payment_method,
                'amount_paid' => $request->amount_paid,
                'status' => 'paid',
                'payment_date' => Carbon::now(),
            ]);

            // Update status order menjadi "completed"
            $order->update([
                'status' => 'completed'
            ]);

            // Commit transaksi jika semuanya berhasil
            DB::commit();

            return response()->json([
                'message' => 'Pembayaran berhasil',
                'order_id' => $order->id,
                'status' => $order->status,
                'payment_details' => $payment
            ]);
        } catch (\Exception $e) {
            // Rollback transaksi jika ada error
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan', 'error' => $e->getMessage()], 500);
        }
    }
}
