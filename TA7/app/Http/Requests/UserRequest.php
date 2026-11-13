<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // This is the model resolved from the URL, never an ID from form input.
        $user = $this->route('user');

        return [
            'username' => ['required', 'string', 'max:50', 'regex:/\A[A-Za-z0-9_.-]+\z/', Rule::unique('users', 'username')->ignore($user)],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'max:128', 'confirmed'],
            'role' => ['sometimes', 'required', Rule::in(User::ROLES)],
            'status' => ['sometimes', 'required', Rule::in(['Active', 'Inactive'])],
            'avatar' => $user
                ? ['bail', 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096']
                : ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Please enter a username.',
            'username.unique' => 'This username is already taken. Choose another one.',
            'username.regex' => 'Use letters, numbers, dots, dashes, and underscores only.',
            'full_name.required' => 'Please enter the user\'s full name.',
            'email.unique' => 'This email address is already used by another user.',
            'password.confirmed' => 'The password confirmation does not match.',
            'avatar.image' => 'Please choose a valid image file.',
            'avatar.mimes' => 'Choose a JPG, PNG, GIF, or WebP image.',
            'avatar.max' => 'The profile picture must be 2 MB or smaller.',
            'avatar.dimensions' => 'Choose a picture no larger than 4096 by 4096 pixels.',
            'avatar.uploaded' => 'The picture could not be uploaded. Choose an image no larger than 2 MB.',
        ];
    }

    public function attributes(): array
    {
        return ['full_name' => 'full name', 'avatar' => 'profile picture'];
    }
}
