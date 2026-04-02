<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ListTesterCustomerRequest;
use App\Http\Requests\Api\StoreTesterCustomerRequest;
use App\Http\Requests\Api\UpdateTesterCustomerRequest;
use App\Models\TesterCustomer;
use Illuminate\Http\JsonResponse;

class TesterCustomerController extends ApiController
{
    public function index(ListTesterCustomerRequest $request): JsonResponse
    {
        $this->authorize('viewAny', TesterCustomer::class);

        $validated = $request->validated();
        $query = TesterCustomer::query();

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query
            ->orderBy('company_name')
            ->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1,
            );

        return $this->paginated('Customers retrieved successfully', $customers);
    }

    public function store(StoreTesterCustomerRequest $request): JsonResponse
    {
        $this->authorize('create', TesterCustomer::class);

        $customer = TesterCustomer::create($request->validated());

        return $this->success('Customer created successfully', $customer, 201);
    }

    public function show(TesterCustomer $customer): JsonResponse
    {
        $this->authorize('view', $customer);

        return $this->success('Customer retrieved successfully', $customer);
    }

    public function update(UpdateTesterCustomerRequest $request, TesterCustomer $customer): JsonResponse
    {
        $this->authorize('update', $customer);

        $customer->update($request->validated());

        return $this->success('Customer updated successfully', $customer->fresh());
    }

    public function destroy(TesterCustomer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);

        if ($customer->testers()->exists()) {
            return $this->error('Cannot delete customer with associated testers', 409);
        }

        $customer->delete();

        return $this->success('Customer deleted successfully');
    }
}
