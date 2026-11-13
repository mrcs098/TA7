@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
    <a class="back-link" href="{{ route('users') }}">&larr; User accounts</a>
    <div class="page-heading form-heading"><p class="eyebrow">YOUR PEOPLE / {{ $user->account_code }}</p><h1>Edit a <em>user.</em></h1><p class="lead">Update {{ $user->name }}'s details and profile picture.</p></div>
    <section class="form-panel" aria-label="Edit user">
        @include('partials.form-errors')
        <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            @include('users.form')
            <fieldset class="avatar-fieldset">
                <legend>Profile picture</legend>
                <div class="avatar-upload">
                    <img class="profile-preview" src="{{ $user->avatar_url }}" alt="Current profile picture for {{ $user->name }}" width="96" height="96">
                    <div class="form-field">
                        <label for="avatar">Choose a new picture <span class="optional">Optional</span></label>
                        <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/gif,image/webp" aria-describedby="avatar-help @error('avatar') avatar-error @enderror" @error('avatar') aria-invalid="true" @enderror>
                        <p id="avatar-help" class="field-hint">JPG, PNG, GIF, or WebP. Maximum 2 MB and 4096 &times; 4096 pixels. Pictures are cropped to a square. Leave empty to keep the current picture.</p>
                        @error('avatar')<p id="avatar-error" class="field-error">{{ $message }}</p>@enderror
                        @if ($errors->any())<p class="field-hint">If you selected a new picture, please choose it again before saving.</p>@endif
                    </div>
                </div>
            </fieldset>
            <div class="form-actions"><button class="button" type="submit">Save changes</button><a class="button button-secondary" href="{{ route('users') }}">Cancel</a></div>
        </form>
    </section>
@endsection
