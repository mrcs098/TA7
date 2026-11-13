@extends('layouts.app')
@section('title', 'User Accounts')
@section('content')
    <div class="page-heading directory-heading">
        <div><p class="eyebrow">YOUR PEOPLE / TEAM</p><h1>User <em>accounts.</em></h1><p class="lead">Meet the team behind your day-to-day operations.</p></div>
        <div class="heading-actions"><span class="count-pill">{{ $users->count() }} users</span><a class="button" href="{{ route('users.create') }}">+ Create user</a></div>
    </div>
    <section class="table-panel" aria-labelledby="user-list-heading">
        <div class="table-heading"><h2 id="user-list-heading">Team directory</h2><span class="muted">Staff accounts &amp; assigned roles</span></div>
        <div class="table-scroll">
            <table>
                <caption class="sr-only">User accounts with profile pictures, username, email, role, status, and edit links</caption>
                <thead><tr><th scope="col">Account ID</th><th scope="col">Team member</th><th scope="col">Username</th><th scope="col">Email address</th><th scope="col">Role</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="record-id">{{ $user->account_code }}</td>
                            <td><div class="person"><img class="avatar avatar-image" src="{{ $user->avatar_url }}" alt="Profile picture for {{ $user->name }}" width="36" height="36" loading="lazy"><strong>{{ $user->name }}</strong></div></td>
                            <td>{{ $user->username }}</td><td>{{ $user->email }}</td><td><span class="role-label">{{ $user->role }}</span></td>
                            <td><span class="badge {{ $user->status === 'Active' ? 'badge-active' : 'badge-inactive' }}"><span aria-hidden="true">&bull;</span> {{ $user->status }}</span></td>
                            <td><a class="edit-link" href="{{ route('users.edit', $user) }}" aria-label="Edit {{ $user->name }}">Edit &rarr;</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-state">No user accounts yet. <a href="{{ route('users.create') }}">Create your first user.</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer"><span>Showing {{ $users->count() }} users</span><a href="{{ route('customers') }}">View customer accounts &rarr;</a></div>
    </section>
@endsection
