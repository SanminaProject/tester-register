<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ListTesterCustomerRequest;
use App\Http\Requests\Api\StoreTesterCustomerRequest;
use App\Http\Requests\Api\UpdateTesterCustomerRequest;
use App\Models\Tester;
use App\Models\TesterCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TesterCustomerController extends ApiController
{
    public function index(ListTesterCustomerRequest $request): JsonResponse
    {
        $this->authorize('viewAny', TesterCustomer::class);

        $validated = $request->validated();
        $query = TesterCustomer::query();

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where('name', 'like', "%{$search}%");
        }

        $customers = $query
            ->orderBy('name')
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

        $validated = $request->validated();
        $customerId = (int) DB::table('tester_customers')->insertGetId([
            'name' => $validated['name'],
        ]);
        $customer = TesterCustomer::query()->findOrFail($customerId);

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

        $validated = $request->validated();

        if ($validated !== []) {
            DB::table('tester_customers')
                ->where('id', $customer->id)
                ->update($validated);
        }

        return $this->success('Customer updated successfully', TesterCustomer::query()->findOrFail($customer->id));
    }

    public function destroy(TesterCustomer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);

        if (Tester::query()->where('owner_id', $customer->id)->exists()) {
            return $this->error('Cannot delete customer with associated testers', 409);
        }

        DB::table('tester_customers')->where('id', $customer->id)->delete();

        return $this->success('Customer deleted successfully');
    }
}
