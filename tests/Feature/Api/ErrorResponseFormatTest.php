<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ErrorResponseFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_api_request_returns_unified_error_envelope(): void
    {
        $response = $this->getJson('/api/v1/customers');

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 401)
            ->assertJsonStructure([
                'success',
                'message',
                'code',
            ]);
    }

    public function test_forbidden_api_request_returns_unified_error_envelope(): void
    {
        $guest = User::factory()->create();
        $guest->assignRole(Role::firstOrCreate(['name' => 'Guest']));
        Sanctum::actingAs($guest);

        $response = $this->getJson('/api/v1/customers');

        $response->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 403)
            ->assertJsonStructure([
                'success',
                'message',
                'code',
            ]);
    }

    public function test_validation_error_returns_unified_error_envelope_with_errors_object(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
                'code',
            ])
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
            ]);
    }

    public function test_model_not_found_returns_unified_error_envelope(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'Admin']));
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/testers/999999');

        $response->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Resource not found')
            ->assertJsonPath('code', 404)
            ->assertJsonStructure([
                'success',
                'message',
                'code',
            ]);
    }
}
