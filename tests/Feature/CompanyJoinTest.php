<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyJoinRequest;
use App\Models\User;
use App\Services\CompanyJoinService;
use App\Support\CompanyContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyJoinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for in-memory database feature tests.');
        }

        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_can_join_open_company_with_code(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create([
            'name' => 'Open Company',
            'slug' => 'open-company',
            'code' => 'OPN-12345',
            'join_mode' => 'open',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('company.join'), [
            'code' => 'opn-12345',
        ]);

        $response->assertRedirect(route('employee.dashboard'));
        $this->assertDatabaseHas('company_memberships', [
            'company_id' => $company->id,
            'user_id' => $user->id,
            'status' => 'active',
        ]);
        $this->assertSame($company->id, session(CompanyContext::SESSION_KEY));
    }

    public function test_approval_required_company_creates_join_request(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create([
            'name' => 'Approval Company',
            'slug' => 'approval-company',
            'code' => 'APR-12345',
            'join_mode' => 'approval_required',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('company.join'), [
            'code' => 'APR-12345',
        ]);

        $response->assertRedirect(route('employee.dashboard'));
        $this->assertDatabaseHas('company_join_requests', [
            'company_id' => $company->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('company_memberships', [
            'company_id' => $company->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_join_request_can_be_approved_into_active_membership(): void
    {
        $reviewer = User::factory()->create();
        $reviewer->assignRole('company_admin');
        $user = User::factory()->create();
        $company = Company::query()->create([
            'name' => 'Approval Company',
            'slug' => 'approval-company',
            'code' => 'APR-12345',
            'join_mode' => 'approval_required',
            'status' => 'active',
        ]);

        $joinRequest = CompanyJoinRequest::query()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'code_used' => $company->code,
            'status' => 'pending',
        ]);

        app(CompanyJoinService::class)->approve($joinRequest, $reviewer);

        $this->assertDatabaseHas('company_join_requests', [
            'id' => $joinRequest->id,
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
        ]);
        $this->assertDatabaseHas('company_memberships', [
            'company_id' => $company->id,
            'user_id' => $user->id,
            'status' => 'active',
        ]);
    }

    public function test_user_cannot_switch_to_company_they_do_not_belong_to(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create([
            'name' => 'Other Company',
            'slug' => 'other-company',
            'code' => 'OTH-12345',
            'join_mode' => 'open',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('company.switch', $company));

        $response->assertSessionHasErrors('company');
    }

    public function test_duplicate_membership_is_rejected(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create([
            'name' => 'Open Company',
            'slug' => 'open-company',
            'code' => 'OPN-12345',
            'join_mode' => 'open',
            'status' => 'active',
        ]);

        $company->memberships()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($user)->from(route('employee.dashboard'))->post(route('company.join'), [
            'code' => $company->code,
        ]);

        $response->assertRedirect(route('employee.dashboard'));
        $response->assertSessionHasErrors('code');
    }
}
