<x-layouts.app>
    <div class="grid">
        <section class="panel span-12 profile-edit-panel">
            <div class="section-head">
                <div>
                    <h1 class="title">Edit Profile</h1>
                    <p class="subtitle">Keep your work profile current. Company is set from your active workspace.</p>
                </div>
                <a class="button secondary" href="{{ route('profile.show') }}">View Profile</a>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="profile-form">
                @csrf
                @method('PUT')

                <div class="profile-photo-editor">
                    <div class="avatar xl">
                        @if ($profile->photoUrl())
                            <img src="{{ $profile->photoUrl() }}" alt="{{ $user->name }}">
                        @else
                            <span>{{ str($user->name)->substr(0, 1)->upper() }}</span>
                        @endif
                    </div>
                    <div>
                        <label class="label" for="profile_photo">Profile Photo</label>
                        <input class="input file-input @error('profile_photo') error-input @enderror" id="profile_photo" name="profile_photo" type="file" accept="image/*">
                        <p class="hint">JPG, PNG, or WEBP up to 2MB.</p>
                        @error('profile_photo') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="profile-company-note">
                    <span class="badge success">{{ $company?->name ?? 'No active company' }}</span>
                    <span class="muted">This comes from the company you joined.</span>
                </div>

                <div class="form-grid">
                    <div class="field">
                        <label class="label" for="name">Name</label>
                        <input class="input @error('name') error-input @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <span class="label">Email</span>
                        <div class="readonly-field">{{ $user->email }}</div>
                    </div>

                    <div class="field">
                        <label class="label" for="designation">Designation</label>
                        <input class="input @error('designation') error-input @enderror" id="designation" name="designation" value="{{ old('designation', $profile->designation) }}">
                        @error('designation') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="age">Age</label>
                        <input class="input @error('age') error-input @enderror" id="age" name="age" type="number" min="13" max="120" value="{{ old('age', $profile->age) }}">
                        @error('age') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="phone">Phone</label>
                        <input class="input @error('phone') error-input @enderror" id="phone" name="phone" value="{{ old('phone', $profile->phone) }}">
                        @error('phone') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field span-form-2">
                        <label class="label" for="location">Location</label>
                        <input class="input @error('location') error-input @enderror" id="location" name="location" value="{{ old('location', $profile->location) }}">
                        @error('location') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field span-form-2">
                        <label class="label" for="bio">Bio</label>
                        <textarea class="input @error('bio') error-input @enderror" id="bio" name="bio" style="height: 120px; padding-top: 10px;">{{ old('bio', $profile->bio) }}</textarea>
                        @error('bio') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="field">
                    <button class="button" type="submit">Save Profile</button>
                </div>
            </form>
        </section>
    </div>
</x-layouts.app>
