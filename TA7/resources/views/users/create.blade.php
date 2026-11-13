@extends('layouts.app')
@section('title', 'Create User')
@section('content')
    <a class="back-link" href="{{ route('users') }}">&larr; User accounts</a>
    <div class="page-heading form-heading"><p class="eyebrow">YOUR PEOPLE / NEW TEAM MEMBER</p><h1>Create a <em>user.</em></h1><p class="lead">Add someone to your team directory. A profile picture can be added after saving.</p></div>
    <section class="form-panel" aria-label="Create user">
        @include('partials.form-errors')
        <form method="POST" action="{{ route('users.store') }}" novalidate>
            @csrf
            @include('users.form')
            <div class="form-actions"><button class="button" type="submit">Create user</button><a class="button button-secondary" href="{{ route('users') }}">Cancel</a></div>
        </form>
    </section>
@endsection
