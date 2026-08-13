<x-layouts.app>
    <div class="grid">
        <section class="panel span-4" style="grid-column-start: 5;">
            <h1 class="title">Create Account</h1>
            <p class="subtitle">Register first, then join a company with its code.</p>

            <form method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="field">
                    <label class="label" for="name">Name</label>
                    <input class="input" id="name" name="name" value="{{ old('name') }}" required autofocus>
                    @error('name') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label class="label" for="email">Email</label>
                    <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required>
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label class="label" for="password">Password</label>
                    <input class="input" id="password" name="password" type="password" required>
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label class="label" for="password_confirmation">Confirm Password</label>
                    <input class="input" id="password_confirmation" name="password_confirmation" type="password" required>
                </div>

                <div class="field">
                    <button class="button" type="submit">Create Account</button>
                    <a class="button secondary" href="{{ route('login') }}">Login</a>
                </div>
            </form>
        </section>
    </div>
</x-layouts.app>
