<?php

namespace App\Http\Controllers;

use App\Models\CompanyMembership;
use App\Services\CompanyJoinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyJoinController extends Controller
{
    public function store(Request $request, CompanyJoinService $companyJoinService): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        $result = $companyJoinService->joinWithCode($request->user(), $validated['code']);

        if ($result instanceof CompanyMembership) {
            return redirect()
                ->route('employee.dashboard')
                ->with('status', 'You joined the company successfully.');
        }

        return redirect()
            ->route('employee.dashboard')
            ->with('status', 'Your join request has been sent for approval.');
    }
}
