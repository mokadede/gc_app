<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $services = Service::with('category')->where('is_active', true)->get();
        return response()->json($services);
    }

    public function show(Service $service)
    {
        return response()->json($service->load('category'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:150',
            'unit' => 'required|in:kg,pcs',
            'duration_days' => 'nullable|integer',
            'duration_label' => 'nullable|string|max:50',
            'difficulty' => 'nullable|in:normal,hard,sexy',
            'size' => 'nullable|in:S,M,L,XL',
            'price_min' => 'required|integer',
            'price_max' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $service = Service::create($validated);
        return response()->json($service, 201);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'category_id' => 'exists:service_categories,id',
            'name' => 'string|max:150',
            'unit' => 'in:kg,pcs',
            'duration_days' => 'nullable|integer',
            'duration_label' => 'nullable|string|max:50',
            'difficulty' => 'nullable|in:normal,hard,sexy',
            'size' => 'nullable|in:S,M,L,XL',
            'price_min' => 'integer',
            'price_max' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $service->update($validated);
        return response()->json($service);
    }

    public function destroy(Service $service)
    {
        $service->update(['is_active' => false]);
        return response()->json(['message' => 'Service disabled']);
    }
}
