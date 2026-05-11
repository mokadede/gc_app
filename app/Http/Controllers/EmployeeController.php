<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::whereIn('role', ['admin', 'kasir'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'nullable|email|max:150|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,kasir',
            'address' => 'nullable|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        return response()->json($user, 201);
    }

    public function show(User $employee)
    {
        return response()->json($employee);
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'string|max:150',
            'phone' => 'string|max:20|unique:users,phone,' . $employee->id,
            'email' => 'nullable|email|max:150|unique:users,email,' . $employee->id,
            'password' => 'nullable|string|min:6',
            'role' => 'in:admin,kasir',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $employee->update($validated);
        return response()->json($employee);
    }

    public function destroy(User $employee)
    {
        if ($employee->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete yourself'], 403);
        }
        $employee->update(['is_active' => false]);
        return response()->json(['message' => 'Employee deactivated']);
    }
}
