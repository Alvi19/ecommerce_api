<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    // protected $fillable = ['user_id', 'product_id', 'quantity', 'status'];
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateTotal()
    {
        // Ambil harga produk yang terkait dengan order
        if ($this->product) {
            return $this->product->price * $this->quantity;
        }

        return 0; // Jika produk tidak ditemukan, total 0
    }
}
