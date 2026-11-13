@extends('layouts.app')
@section('title', 'Create Customer')
@section('content')
    <a class="back-link" href="{{ route('customers') }}">&larr; Customer accounts</a>
    <div class="page-heading form-heading"><p class="eyebrow">YOUR PEOPLE / NEW CUSTOMER</p><h1>Create a <em>customer.</em></h1><p class="lead">Add a customer and keep their contact details close at hand.</p></div>
    <section class="form-panel" aria-label="Create customer">
        @include('partials.form-errors')
        <form method="POST" action="{{ route('customers.store') }}" novalidate>
            @csrf
            @include('customers.form')
            <div class="form-actions"><button class="button" type="submit">Create customer</button><a class="button button-secondary" href="{{ route('customers') }}">Cancel</a></div>
        </form>
    </section>
@endsection
