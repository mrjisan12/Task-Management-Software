<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\UserProfile;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $company = app(CompanyContext::class)->current($request->user());
        $profile = $this->profile($request);
        $this->syncCompanyName($profile, $company);

        return view('employee.profile.show', [
            'user' => $request->user()->load('profile'),
            'profile' => $profile,
            'company' => $company,
        ]);
    }

    public function edit(Request $request): View
    {
        $company = app(CompanyContext::class)->current($request->user());
        $profile = $this->profile($request);
        $this->syncCompanyName($profile, $company);

        return view('employee.profile.edit', [
            'user' => $request->user()->load('profile'),
            'profile' => $profile,
            'company' => $company,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->profile($request);

        $validated = $request->validate([
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'age' => ['nullable', 'integer', 'min:13', 'max:120'],
            'designation' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($profile->profile_photo_path) {
                Storage::disk('public')->delete($profile->profile_photo_path);
            }

            $validated['profile_photo_path'] = $request->file('profile_photo')->store('profiles', 'public');
        }

        unset($validated['profile_photo']);

        $profile->update($validated);

        return redirect()
            ->route('profile.show')
            ->with('status', 'Profile updated successfully.');
    }

    private function profile(Request $request): UserProfile
    {
        return $request->user()->profile()->firstOrCreate();
    }

    private function syncCompanyName(UserProfile $profile, ?Company $company): void
    {
        if ($company && $profile->company_name !== $company->name) {
            $profile->forceFill(['company_name' => $company->name])->save();
        }
    }
}
