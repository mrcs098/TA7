<p class="form-note">Fields marked <span aria-hidden="true">*</span> are required.</p>
<div class="form-grid">
    <div class="form-field">
        <label for="full_name">Full name <span aria-hidden="true">*</span></label>
        <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $customer->name) }}" maxlength="255" autocomplete="name" required @error('full_name') aria-invalid="true" aria-describedby="full_name-error" @enderror>
        @error('full_name')<p id="full_name-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="email">Email address <span aria-hidden="true">*</span></label>
        <input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" maxlength="255" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
        @error('email')<p id="email-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="phone">Phone number <span class="optional">Optional</span></label>
        <input id="phone" name="phone" type="tel" value="{{ old('phone', $customer->phone) }}" maxlength="30" autocomplete="tel" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
        @error('phone')<p id="phone-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field">
        <label for="status">Account status</label>
        <select id="status" name="status" @error('status') aria-invalid="true" aria-describedby="status-error" @enderror>
            @foreach (['Active', 'Inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $customer->status ?? 'Active') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error('status')<p id="status-error" class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="form-field form-field-wide">
        <label for="address">Address <span class="optional">Optional</span></label>
        <textarea id="address" name="address" rows="3" maxlength="255" autocomplete="street-address" @error('address') aria-invalid="true" aria-describedby="address-error" @enderror>{{ old('address', $customer->address) }}</textarea>
        @error('address')<p id="address-error" class="field-error">{{ $message }}</p>@enderror
    </div>
</div>
