<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * GET /api/platform/companies
     * Lista todas las empresas. Solo Platform Owner (sin scope de tenant).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Company::query()->withCount('foodTrucks')->with('activeSubscription.plan');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $companies = $query->latest()->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => CompanyResource::collection($companies->items()),
            'meta' => [
                'current_page' => $companies->currentPage(),
                'last_page' => $companies->lastPage(),
                'total' => $companies->total(),
                'per_page' => $companies->perPage(),
            ],
        ]);
    }

    /**
     * POST /api/platform/companies
     * Crea una empresa manualmente (Platform Owner). El registro público
     * normal usa AuthController@registerCompany en su lugar.
     */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $data['status'] = $data['status'] ?? 'pending';

        $company = Company::create($data);

        return response()->json([
            'data' => new CompanyResource($company),
            'message' => __('messages.company.created'),
        ], 201);
    }

    /**
     * Genera un slug único a partir del nombre, agregando un sufijo
     * numérico si ya existe (ej: "tacos-del-norte-llc", "tacos-del-norte-llc-2").
     */
    private function generateUniqueSlug(string $name): string
    {
        $base = \Illuminate\Support\Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (Company::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * GET /api/platform/companies/{company}
     * GET /api/company/profile (company-admin ve la suya, ya filtrado por scope)
     */
    public function show(Company $company): JsonResponse
    {
        $company->loadCount('foodTrucks')->load('activeSubscription.plan');

        return response()->json(['data' => new CompanyResource($company)]);
    }

    /**
     * PUT/PATCH /api/platform/companies/{company}
     * PUT/PATCH /api/company/profile
     */
    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $company->update($request->validated());

        return response()->json([
            'data' => new CompanyResource($company),
            'message' => __('messages.company.updated'),
        ]);
    }

    /**
     * DELETE /api/platform/companies/{company}
     * Solo Platform Owner. Soft delete.
     */
    public function destroy(Company $company): JsonResponse
    {
        $company->delete();

        return response()->json(['message' => __('messages.company.deleted')]);
    }

    /**
     * POST /api/platform/companies/{company}/approve
     */
    public function approve(Company $company): JsonResponse
    {
        $company->update([
            'status' => 'active',
            'approved_at' => now(),
        ]);

        return response()->json([
            'data' => new CompanyResource($company),
            'message' => __('messages.company.approved'),
        ]);
    }

    /**
     * POST /api/platform/companies/{company}/suspend
     */
    public function suspend(Company $company): JsonResponse
    {
        $company->update(['status' => 'suspended']);

        return response()->json([
            'data' => new CompanyResource($company),
            'message' => __('messages.company.suspended'),
        ]);
    }

    /**
     * POST /api/platform/companies/{company}/reactivate
     */
    public function reactivate(Company $company): JsonResponse
    {
        $company->update(['status' => 'active']);

        return response()->json([
            'data' => new CompanyResource($company),
            'message' => __('messages.company.reactivated'),
        ]);
    }

    /**
     * GET /api/company/profile
     * El usuario de una empresa consulta los datos de SU PROPIA empresa
     * (no necesita pasar un ID, se resuelve por el usuario autenticado).
     */
    public function myCompany(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        abort_if(! $company, 404, __('messages.company.no_company_assigned'));

        $company->loadCount('foodTrucks')->load('activeSubscription.plan');

        return response()->json(['data' => new CompanyResource($company)]);
    }

    /**
     * PUT/PATCH /api/company/profile
     */
    public function updateMyCompany(UpdateCompanyRequest $request): JsonResponse
    {
        $company = $request->user()->company;

        abort_if(! $company, 404, __('messages.company.no_company_assigned'));

        $company->update($request->validated());

        return response()->json([
            'data' => new CompanyResource($company),
            'message' => __('messages.company.updated'),
        ]);
    }
}
