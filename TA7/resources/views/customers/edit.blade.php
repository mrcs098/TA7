@extends('layouts.app')
@section('title', 'Edit Customer')
@section('content')
    <a class="back-link" href="{{ route('customers') }}">&larr; Customer accounts</a>
    <div class="page-heading form-heading"><p class="eyebrow">YOUR PEOPLE / {{ $customer->account_code }}</p><h1>Edit a <em>customer.</em></h1><p class="lead">Keep {{ $customer->name }}'s details up to date.</p></div>
    <section class="form-panel" aria-label="Edit customer">
        @include('partials.form-errors')
        <form method="POST" action="{{ route('customers.update', $customer) }}" novalidate>
            @csrf
            @method('PUT')
            @include('customers.form')
            <div class="form-actions"><button class="button" type="submit">Save changes</button><a class="button button-secondary" href="{{ route('customers') }}">Cancel</a></div>
        </form>
    </section>
@endsection
