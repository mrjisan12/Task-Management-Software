<x-layouts.app>
    <div class="grid">
        <section class="panel span-12 profile-hero">
            <div class="profile-main">
                <div class="avatar xl">
                    @if ($profile->photoUrl())
                        <img src="{{ $profile->photoUrl() }}" alt="{{ $user->name }}">
                    @else
                        <span>{{ str($user->name)->substr(0, 1)->upper() }}</span>
                    @endif
                </div>
                <div>
                    <h1 class="title">{{ $user->name }}</h1>
                    <p class="subtitle">{{ $profile->designation ?: 'No designation added' }}</p>
                    <div class="profile-tags">
                        <span class="badge">{{ $user->email }}</span>
                        <span class="badge warning">{{ $roleDisplay }}</span>
                        <span class="badge success">{{ $company?->name ?? 'No active company' }}</span>
                    </div>
                </div>
            </div>
            <a class="button" href="{{ route('profile.edit') }}">Edit Profile</a>
        </section>

        <section class="panel span-8 profile-card">
            <div class="section-head">
                <div>
                    <h2 class="section-title">About</h2>
                    <p class="subtitle">Short personal work summary.</p>
                </div>
            </div>
            <p class="profile-bio">{{ $profile->bio ?: 'No bio added yet.' }}</p>
        </section>

        <section class="panel span-4 profile-card">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Details</h2>
                    <p class="subtitle">Optional contact info.</p>
                </div>
            </div>
            <div class="list">
                <div class="row">
                    <span class="muted">Age</span>
                    <strong>{{ $profile->age ?: 'Not set' }}</strong>
                </div>
                <div class="row">
                    <span class="muted">Company</span>
                    <strong>{{ $company?->name ?? 'Not joined' }}</strong>
                </div>
                <div class="row">
                    <span class="muted">User Type</span>
                    <strong>{{ $roleDisplay }}</strong>
                </div>
                <div class="row">
                    <span class="muted">Phone</span>
                    <strong>{{ $profile->phone ?: 'Not set' }}</strong>
                </div>
                <div class="row">
                    <span class="muted">Location</span>
                    <strong>{{ $profile->location ?: 'Not set' }}</strong>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
