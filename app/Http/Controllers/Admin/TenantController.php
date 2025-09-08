<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller
{
	public function __construct()
	{
		$this->middleware(['auth', 'role:admin']);
	}

	public function index(Request $request)
	{
		$tenants = Tenant::orderBy('name')->paginate(20);
		if ($request->ajax() || $request->wantsJson()) {
			return response()->json(['success' => true, 'data' => $tenants]);
		}
		return view('admin.tenants.index', compact('tenants'));
	}

	public function create()
	{
		return view('admin.tenants.create');
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			'name' => 'required|string|max:255',
			'domain' => 'required|string|max:255|unique:tenants,domain',
			'is_active' => 'boolean',
		]);
		$validated['is_active'] = (bool)($validated['is_active'] ?? true);
		Tenant::create($validated);
		return redirect()->route('admin.tenants.index')->with('success', 'Tenant created.');
	}

	public function edit(Tenant $tenant)
	{
		return view('admin.tenants.edit', compact('tenant'));
	}

	public function update(Request $request, Tenant $tenant)
	{
		$validated = $request->validate([
			'name' => 'required|string|max:255',
			'domain' => 'required|string|max:255|unique:tenants,domain,' . $tenant->id,
			'is_active' => 'boolean',
		]);
		$tenant->update($validated);
		return redirect()->route('admin.tenants.index')->with('success', 'Tenant updated.');
	}

	public function destroy(Tenant $tenant)
	{
		$tenant->delete();
		return redirect()->route('admin.tenants.index')->with('success', 'Tenant deleted.');
	}

	public function toggleActive(Tenant $tenant): JsonResponse
	{
		$tenant->is_active = !$tenant->is_active;
		$tenant->save();
		return response()->json(['success' => true, 'message' => 'Tenant status updated.', 'data' => $tenant]);
	}
}


