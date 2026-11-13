<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\AvatarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('id')->get();

        return view('users', ['users' => $users]);
    }

    public function create(): View
    {
        return view('users.create', ['user' => new User()]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create($this->attributes($request));

        return to_route('users')->with('success', 'User account created. You can now edit it to add a profile picture.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user, AvatarService $avatars): RedirectResponse
    {
        $attributes = $this->attributes($request, $user);
        $previousAvatar = $user->avatar;
        $newAvatar = $request->hasFile('avatar') ? $avatars->store($request->file('avatar')) : null;

        if ($newAvatar) {
            $attributes['avatar'] = $newAvatar;
        }

        try {
            $user->update($attributes);
        } catch (Throwable $exception) {
            // Do not leave a new file behind if saving the database row fails.
            $avatars->delete($newAvatar);

            throw $exception;
        }

        // Keep the existing picture until the replacement is saved successfully.
        if ($newAvatar && $previousAvatar !== $newAvatar) {
            $avatars->delete($previousAvatar);
        }

        return to_route('users')->with('success', 'User account updated successfully.');
    }

    private function attributes(UserRequest $request, ?User $user = null): array
    {
        $data = $request->validated();
        $attributes = [
            'name' => $data['full_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role'] ?? $user?->role ?? 'Cashier',
            'status' => $data['status'] ?? $user?->status ?? 'Active',
        ];

        if (! empty($data['password'])) {
            // User's existing hashed cast hashes the password before persistence.
            $attributes['password'] = $data['password'];
        }

        return $attributes;
    }
}
