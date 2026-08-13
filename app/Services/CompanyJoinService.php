<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyJoinRequest;
use App\Models\CompanyMembership;
use App\Models\User;
use App\Support\CompanyContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompanyJoinService
{
    public function __construct(private readonly CompanyContext $companyContext)
    {
    }

    public function joinWithCode(User $user, string $code): CompanyMembership|CompanyJoinRequest
    {
        $normalizedCode = str($code)->trim()->toString();

        $company = Company::query()
            ->whereRaw('LOWER(code) = ?', [str($normalizedCode)->lower()->toString()])
            ->first();

        if (! $company) {
            throw ValidationException::withMessages([
                'code' => 'No company was found for this code.',
            ]);
        }

        if ($company->status !== 'active') {
            throw ValidationException::withMessages([
                'code' => 'This company is not accepting members right now.',
            ]);
        }

        $existingMembership = $company->memberships()
            ->where('user_id', $user->id)
            ->first();

        if ($existingMembership) {
            if ($existingMembership->status === 'active') {
                $this->companyContext->set($company);

                throw ValidationException::withMessages([
                    'code' => 'You are already an active member of this company.',
                ]);
            }

            throw ValidationException::withMessages([
                'code' => 'You already have a membership request for this company.',
            ]);
        }

        if ($company->join_mode === 'closed') {
            throw ValidationException::withMessages([
                'code' => 'This company is currently closed to new members.',
            ]);
        }

        return DB::transaction(function () use ($company, $user, $normalizedCode) {
            if ($company->join_mode === 'open') {
                $membership = $company->memberships()->create([
                    'user_id' => $user->id,
                    'status' => 'active',
                    'joined_at' => now(),
                    'approved_at' => now(),
                ]);

                $user->assignRole('employee');
                $user->profile()->firstOrCreate()->forceFill(['company_name' => $company->name])->save();
                $this->companyContext->set($company);

                return $membership;
            }

            return CompanyJoinRequest::query()->create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'code_used' => $normalizedCode,
                'status' => 'pending',
            ]);
        });
    }

    public function approve(CompanyJoinRequest $joinRequest, User $reviewer, ?string $note = null): CompanyMembership
    {
        if ($joinRequest->status !== 'pending') {
            throw ValidationException::withMessages([
                'join_request' => 'This join request has already been reviewed.',
            ]);
        }

        return DB::transaction(function () use ($joinRequest, $reviewer, $note) {
            $joinRequest->update([
                'status' => 'approved',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_note' => $note,
            ]);

            $membership = $joinRequest->company->memberships()->updateOrCreate(
                ['user_id' => $joinRequest->user_id],
                [
                    'status' => 'active',
                    'joined_at' => now(),
                    'approved_by' => $reviewer->id,
                    'approved_at' => now(),
                ],
            );

            $joinRequest->user->assignRole('employee');
            $joinRequest->user->profile()->firstOrCreate()->forceFill([
                'company_name' => $joinRequest->company->name,
            ])->save();

            return $membership;
        });
    }

    public function reject(CompanyJoinRequest $joinRequest, User $reviewer, ?string $note = null): CompanyJoinRequest
    {
        if ($joinRequest->status !== 'pending') {
            throw ValidationException::withMessages([
                'join_request' => 'This join request has already been reviewed.',
            ]);
        }

        $joinRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ]);

        return $joinRequest;
    }
}
