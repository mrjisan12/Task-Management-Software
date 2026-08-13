<x-layouts.app>
    <div class="auth-wrap">
        <section class="auth-shell">
            <div class="auth-intro">
                <div>
                    <p class="auth-kicker">Task Management</p>
                    <h1 class="auth-heading">Sign in to your company workspace.</h1>
                    <p class="auth-copy">
                        Track assigned work, complete tasks, follow progress, and keep the team leaderboard moving from one focused dashboard.
                    </p>
                </div>

                <div class="auth-stats" aria-label="Workspace highlights">
                    <div class="auth-stat">
                        <strong>Teams</strong>
                        <span>Company workspaces</span>
                    </div>
                    <div class="auth-stat">
                        <strong>Tasks</strong>
                        <span>Assigned workflows</span>
                    </div>
                    <div class="auth-stat">
                        <strong>XP</strong>
                        <span>Points and badges</span>
                    </div>
                </div>
            </div>

            <div class="auth-panel">
                <div class="auth-panel-header">
                    <h2 class="title">Welcome Back</h2>
                    <p class="subtitle">Use your work account to continue.</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div class="field">
                        <label class="label" for="email">Email</label>
                        <input class="input @error('email') error-input @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                        @error('email') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" x-data="{ showPassword: false }">
                        <label class="label" for="password">Password</label>
                        <div class="password-wrap">
                            <input class="input @error('password') error-input @enderror" id="password" name="password" x-bind:type="showPassword ? 'text' : 'password'" required autocomplete="current-password">
                            <button class="password-toggle" type="button" x-on:click="showPassword = ! showPassword" x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'">
                                <svg x-show="! showPassword" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <svg x-show="showPassword" x-cloak viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="m3 3 18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M10.6 6.2A10 10 0 0 1 12 6c6 0 9.5 6 9.5 6a16.2 16.2 0 0 1-3 3.6M7.5 7.5C4.4 9.1 2.5 12 2.5 12s3.5 6 9.5 6c1.6 0 3-.4 4.2-1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9.9 9.9A3 3 0 0 0 14.1 14.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        @error('password') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field-row">
                        <label class="checkbox-label" for="remember">
                            <input id="remember" type="checkbox" name="remember" value="1">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <div class="auth-actions">
                        <button class="button full" type="submit">Login</button>
                    </div>
                </form>

                <div class="demo-logins">
                    <p class="demo-logins-title">Demo logins</p>
                    <div class="demo-account">
                        <div>
                            <strong>Employee</strong>
                            <span>jisan@ilabs360.com</span>
                        </div>
                        <span>password</span>
                    </div>
                    <div class="demo-account">
                        <div>
                            <strong>Company Admin</strong>
                            <span>cadmin@ilabs360.com</span>
                        </div>
                        <span>password</span>
                    </div>
                    <div class="demo-account">
                        <div>
                            <strong>Super Admin</strong>
                            <span>sadmin@ilabs360.com</span>
                        </div>
                        <span>password</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
