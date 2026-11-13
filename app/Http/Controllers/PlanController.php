<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::query()
            ->withCount(['tenantPlans as tenants_count' => fn ($q) => $q->where('status', 'active')->whereHas('tenant')])
            ->oldest()
            ->get();

        $topPlans = $plans->take(3);
        $mostPopularId = $plans->where('tenants_count', '>', 0)->sortByDesc('tenants_count')->first()?->id;

        return view('plans.index', compact('plans', 'topPlans', 'mostPopularId'));
    }

    public function create(): View
    {
        return view('plans.form', ['mode' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        Plan::create([
            'name' => $data['name'],
            'status' => strtolower($data['status']),
            'price' => $data['price'],
            'billing_cycle' => strtolower($data['cycle']),
            'max_branches' => $data['max_branches'] ?? null,
            'max_students' => $data['max_students'] ?? null,
            'features' => $data['features'] ?? [],
        ]);

        return redirect()->route('plans.index')->with('success', 'Plan created.');
    }

    public function show(int $id): View
    {
        $plan = Plan::withTrashed()
            ->withCount(['tenantPlans as tenants_count' => fn ($q) => $q->where('status', 'active')->whereHas('tenant')])
            ->findOrFail($id);

        $subscribers = $plan->tenantPlans()
            ->where('status', 'active')
            ->whereHas('tenant')
            ->with('tenant')
            ->latest('starts_at')
            ->get();

        return view('plans.show', compact('plan', 'subscribers'));
    }

    public function edit(int $id): View
    {
        $plan = Plan::findOrFail($id);

        return view('plans.form', ['mode' => 'edit', 'plan' => $plan]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $plan = Plan::findOrFail($id);
        $data = $this->validateData($request);

        $plan->update([
            'name' => $data['name'],
            'status' => strtolower($data['status']),
            'price' => $data['price'],
            'billing_cycle' => strtolower($data['cycle']),
            'max_branches' => $data['max_branches'] ?? null,
            'max_students' => $data['max_students'] ?? null,
            'features' => $data['features'] ?? [],
        ]);

        return redirect()->route('plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $plan = Plan::findOrFail($id);
        $plan->delete();

        return response()->json(['message' => 'Plan archived.']);
    }

    public function archived(): View
    {
        $plans = Plan::onlyTrashed()
            ->withCount(['tenantPlans as tenants_count' => fn ($q) => $q->where('status', 'active')->whereHas('tenant')])
            ->latest('deleted_at')
            ->get();

        return view('plans.archived', compact('plans'));
    }

    public function restore(int $id): JsonResponse
    {
        $plan = Plan::onlyTrashed()->findOrFail($id);
        $plan->restore();

        return response()->json(['message' => 'Plan restored.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:Active,Inactive'],
            'price' => ['required', 'numeric', 'min:0'],
            'cycle' => ['required', 'string', 'in:Monthly,Quarterly,Yearly'],
            'max_branches' => ['nullable', 'integer', 'min:1'],
            'max_students' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string'],
        ]);
    }
}
