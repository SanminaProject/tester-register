<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_customers_endpoint(): void
    {
        $this->getJson('/api/v1/customers')->assertUnauthorized();
    }

    public function test_user_without_required_role_cannot_create_customer(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/customers', [
            'company_name' => 'No Role Corp',
            'address' => 'No role address',
            'contact_person' => 'No Role User',
            'phone' => '1234567890',
            'email' => 'norele@example.com',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_perform_customer_crud_flow(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $createResponse = $this->postJson('/api/v1/customers', [
            'company_name' => 'Acme Inc',
            'address' => '1 Infinite Loop',
            'contact_person' => 'John Doe',
            'phone' => '+1-555-1234',
            'email' => 'contact@acme.example',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.company_name', 'Acme Inc');

        $customerId = $createResponse->json('data.id');

        $this->getJson("/api/v1/customers/{$customerId}")
            ->assertOk()
            ->assertJsonPath('data.id', $customerId)
            ->assertJsonPath('data.company_name', 'Acme Inc');

        $this->putJson("/api/v1/customers/{$customerId}", [
            'company_name' => 'Acme Updated',
            'phone' => '+1-555-9999',
        ])
            ->assertOk()
            ->assertJsonPath('data.company_name', 'Acme Updated');

        $this->deleteJson("/api/v1/customers/{$customerId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('tester_customers', [
            'id' => $customerId,
        ]);
    }

    public function test_customer_create_enforces_validation_rules(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/customers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'company_name',
                'address',
                'contact_person',
                'phone',
                'email',
            ]);
    }

    private function createAdminUser(): User
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $user = User::factory()->create();
        $user->assignRole($adminRole);

        return $user;
    }
}
