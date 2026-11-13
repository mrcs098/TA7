<p class="form-note">Fields marked <span aria-hidden="true">*</span> are required.</p>
<div class="form-grid">
    <div class="form-field">
        <label for="full_name">Full name <span aria-hidden="true">*</span></label>
        <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $user->name) }}" maxlength="255" autocomplete="name" required @error('full_name') aria-invalid="true" aria-describedby="full_name-error" @enderror>
        @error('full_name')<p id="full_name-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="username">Username <span aria-hidden="true">*</span></label>
        <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" maxlength="50" autocomplete="username" required aria-describedby="username-help @error('username') username-error @enderror" @error('username') aria-invalid="true" @enderror>
        <p id="username-help" class="field-hint">Letters, numbers, dots, dashes, and underscores.</p>
        @error('username')<p id="username-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field form-field-wide">
        <label for="email">Email address <span aria-hidden="true">*</span></label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
        @error('email')<p id="email-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="role">Role</label>
        <select id="role" name="role" @error('role') aria-invalid="true" aria-describedby="role-error" @enderror>
            @foreach (\App\Models\User::ROLES as $role)
                <option value="{{ $role }}" @selected(old('role', $user->role ?? 'Cashier') === $role)>{{ $role }}</option>
            @endforeach
        </select>
        @error('role')<p id="role-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="status">Account status</label>
        <select id="status" name="status" @error('status') aria-invalid="true" aria-describedby="status-error" @enderror>
            @foreach (['Active', 'Inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $user->status ?? 'Active') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error('status')<p id="status-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="password">{{ $user->exists ? 'New password' : 'Password' }} @if (! $user->exists)<span aria-hidden="true">*</span>@else<span class="optional">Optional</span>@endif</label>
        <input id="password" name="password" type="password" minlength="8" maxlength="128" autocomplete="new-password" @if (! $user->exists) required @endif aria-describedby="password-help @error('password') password-error @enderror" @error('password') aria-invalid="true" @enderror>
        <p id="password-help" class="field-hint">At least 8 characters. @if ($user->exists) Leave empty to keep the current password. @endif</p>
        @error('password')<p id="password-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="password_confirmation">Confirm {{ $user->exists ? 'new password' : 'password' }} @if (! $user->exists)<span aria-hidden="true">*</span>@endif</label>
        <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" maxlength="128" autocomplete="new-password" @if (! $user->exists) required @endif @error('password_confirmation') aria-invalid="true" aria-describedby="password_confirmation-error" @enderror>
        @error('password_confirmation')<p id="password_confirmation-error" class="field-error">{{ $message }}</p>@enderror
    </div>
</div>
