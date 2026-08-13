<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Support\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CompanySwitchController extends Controller
{
    public function __invoke(Request $request, Company $company, CompanyContext $companyContext): RedirectResponse
    {
        if (! $companyContext->userBelongsToCompany($request->user(), $company->id)) {
            throw ValidationException::withMessages([
                'company' => 'You are not an active member of this company.',
            ]);
        }

        $companyContext->set($company);

        return redirect()
            ->route('employee.dashboard')
            ->with('status', 'Active company switched to '.$company->name.'.');
    }
}
