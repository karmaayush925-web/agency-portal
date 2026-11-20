<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_invoice_and_record_audit_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $clientUser = User::factory()->create(['role' => 'client']);
        $client = Client::create([
            'user_id' => $clientUser->id,
            'company_name' => 'Test Corp',
            'contact_person' => 'John Doe',
            'email' => $clientUser->email,
            'address' => 'Jaipur, India',
        ]);

        $response = $this->actingAs($admin)->post(route('invoices.store'), [
            'client_id' => $client->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'tax_rate' => 18,
            'items' => [
                [
                    'description' => 'Backend Architecture',
                    'quantity' => 2,
                    'unit_price' => 500,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'client_id' => $client->id,
            'subtotal' => 1000.00,
            'tax_amount' => 180.00,
            'total_amount' => 1180.00,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'invoice.created',
            'user_id' => $admin->id,
        ]);
    }

    public function test_client_cannot_access_admin_audit_logs(): void
    {
        $clientUser = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($clientUser)->get(route('audit-logs.index'));

        $response->assertStatus(403);
    }

    public function test_client_cannot_view_another_clients_invoice_idor_protection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $clientOneUser = User::factory()->create(['role' => 'client']);
        $clientTwoUser = User::factory()->create(['role' => 'client']);

        $clientOne = Client::create([
            'user_id' => $clientOneUser->id,
            'company_name' => 'Alpha Ltd',
            'contact_person' => 'Alice Smith',
            'email' => $clientOneUser->email,
            'address' => 'Delhi',
        ]);

        Client::create([
            'user_id' => $clientTwoUser->id,
            'company_name' => 'Beta Ltd',
            'contact_person' => 'Bob Jones',
            'email' => $clientTwoUser->email,
            'address' => 'Mumbai',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-9999',
            'client_id' => $clientOne->id,
            'created_by' => $admin->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'subtotal' => 200,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total_amount' => 200,
            'status' => 'sent',
        ]);

        $response = $this->actingAs($clientTwoUser)->get(route('invoices.show', $invoice));

        $response->assertStatus(403);
    }

    public function test_sanctum_api_authentication_and_scoped_invoice_listing(): void
    {
        $admin = User::factory()->create([
            'email' => 'apiadmin@agency.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
        ]);

        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => 'apiadmin@agency.com',
            'password' => 'secret123',
            'device_name' => 'phpunit',
        ]);

        $loginResponse->assertOk()
            ->assertJsonStructure(['token_type', 'access_token', 'user']);

        $token = $loginResponse->json('access_token');

        $invoicesResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/invoices');

        $invoicesResponse->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);
    }
}