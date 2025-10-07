<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class ProductObserver
{
    public function created(Product $product)
    {
        AuditLog::create([
            'user_id'    => Auth::user()->id ?? 1, // guaranteed fallback for tinker / no auth
            'action'     => 'created',
            'table_name' => 'products',
            'record_id'  => $product->id,
            'old_data'   => null,
            'new_data'   => json_encode($product->getAttributes()),
        ]);
    }

    public function updated(Product $product)
    {
        $changes = $product->getChanges();

        AuditLog::create([
            'user_id'    => Auth::user()->id ?? 1,
            'action'     => 'updated',
            'table_name' => 'products',
            'record_id'  => $product->id,
            'old_data'   => json_encode($product->getOriginal()),
            'new_data'   => json_encode($changes),
        ]);
    }

    public function deleted(Product $product)
    {
        AuditLog::create([
            'user_id'    => Auth::user()->id ?? 1,
            'action'     => 'deleted',
            'table_name' => 'products',
            'record_id'  => $product->id,
            'old_data'   => json_encode($product->getAttributes()),
            'new_data'   => null,
        ]);
    }
}
