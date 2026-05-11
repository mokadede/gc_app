<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('creator')->orderBy('expense_date', 'desc');

        if ($request->month) {
            $query->whereMonth('expense_date', $request->month);
        }
        if ($request->year) {
            $query->whereYear('expense_date', $request->year);
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }

        $expenses = $query->get();

        // Summary
        $totalAmount = $expenses->sum('amount');
        $byCategory = $expenses->groupBy('category')->map(function ($items, $cat) {
            return [
                'category' => $cat,
                'label' => Expense::categoryLabels()[$cat] ?? $cat,
                'total' => $items->sum('amount'),
                'count' => $items->count(),
            ];
        })->values();

        return response()->json([
            'expenses' => $expenses,
            'summary' => [
                'total_amount' => $totalAmount,
                'total_count' => $expenses->count(),
                'by_category' => $byCategory,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:deterjen,pewangi,plastik,listrik_air,gaji,sewa,transportasi,lainnya',
            'description' => 'nullable|string|max:255',
            'used_by' => 'nullable|string|max:100',
            'amount' => 'required|integer|min:1',
            'expense_date' => 'required|date',
        ]);

        $validated['created_by'] = $request->user()->id;
        $expense = Expense::create($validated);

        return response()->json($expense->load('creator'), 201);
    }

    public function show(Expense $expense)
    {
        return response()->json($expense->load('creator'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'category' => 'string|in:deterjen,pewangi,plastik,listrik_air,gaji,sewa,transportasi,lainnya',
            'description' => 'nullable|string|max:255',
            'used_by' => 'nullable|string|max:100',
            'amount' => 'integer|min:1',
            'expense_date' => 'date',
        ]);

        $expense->update($validated);
        return response()->json($expense->load('creator'));
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->json(['message' => 'Expense deleted']);
    }
}
