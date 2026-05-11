<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        return response()->json(Voucher::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|integer|min:1',
            'min_order' => 'nullable|integer|min:0',
            'max_discount' => 'nullable|integer|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ]);

        $voucher = Voucher::create($validated);
        return response()->json($voucher, 201);
    }

    public function show(Voucher $voucher)
    {
        return response()->json($voucher);
    }

    public function update(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'code' => 'string|max:50|unique:vouchers,code,' . $voucher->id,
            'name' => 'string|max:150',
            'description' => 'nullable|string',
            'type' => 'in:percentage,fixed',
            'value' => 'integer|min:1',
            'min_order' => 'nullable|integer|min:0',
            'max_discount' => 'nullable|integer|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
        ]);

        $voucher->update($validated);
        return response()->json($voucher);
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return response()->json(['message' => 'Voucher deleted']);
    }

    public function check(Request $request)
    {
        $code = $request->query('code');
        $voucher = Voucher::where('code', $code)
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->first();

        if (!$voucher) {
            return response()->json(['message' => 'Voucher tidak valid atau sudah kadaluarsa'], 404);
        }

        if ($voucher->usage_limit && $voucher->used_count >= $voucher->usage_limit) {
            return response()->json(['message' => 'Kuota voucher sudah habis'], 400);
        }

        return response()->json($voucher);
    }
}
