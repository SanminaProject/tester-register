<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListTesterCustomerRequest;
use App\Http\Requests\Api\StoreTesterCustomerRequest;
use App\Http\Requests\Api\UpdateTesterCustomerRequest;
use App\Models\TesterCustomer;
use Illuminate\Http\JsonResponse;

class TesterCustomerController extends Controller
{
    /**
     * Get all customers with pagination
     */
    public function index(ListTesterCustomerRequest $request): JsonResponse
    {
        $this->authorize('view', TesterCustomer::class);

        $validated = $request->validated();
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 15);
        $search = (string) ($validated['search'] ?? '');

        $query = TesterCustomer::query();

        if ($search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $customers = $query->forPage($page, $perPage)->get();

        return response()->json([
            'success' => true,
            'message' => 'Customer list retrieved successfully',
            'data' => [
                'items' => $customers,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => (int)ceil($total / $perPage),
                ],
            ],
            'code' => 200,
        ]);
    }

    /**
     * Create a new customer
     */
    public function store(StoreTesterCustomerRequest $request): JsonResponse
    {
        $this->authorize('create', TesterCustomer::class);

        $customer = TesterCustomer::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $customer,
            'code' => 201,
        ], 201);
    }

    /**
     * Get a specific customer
     */
    public function show(TesterCustomer $customer): JsonResponse
    {
        $this->authorize('view', $customer);

        $customer->load('testers');

        return response()->json([
            'success' => true,
            'message' => 'Customer details retrieved successfully',
            'data' => [
                'id' => $customer->id,
                'company_name' => $customer->company_name,
                'address' => $customer->address,
                'contact_person' => $customer->contact_person,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'testers_count' => $customer->testers->count(),
                'testers' => $customer->testers->map(fn($t) => [
                    'id' => $t->id,
                    'model' => $t->model,
                ]),
                'created_at' => $customer->created_at,
            ],
            'code' => 200,
        ]);
    }

    /**
     * Update a customer
     */
    public function update(UpdateTesterCustomerRequest $request, TesterCustomer $customer): JsonResponse
    {
        $this->authorize('update', $customer);

        $customer->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'data' => $customer,
            'code' => 200,
        ]);
    }

    /**
     * Delete a customer
     */
    public function destroy(TesterCustomer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);

        if ($customer->testers()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete customer with associated testers',
                'error' => 'ConflictException',
                'code' => 409,
            ], 409);
        }

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully',
            'code' => 200,
        ]);
    }
}
