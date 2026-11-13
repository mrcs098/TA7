@extends('layouts.app')
@section('title', 'Customer Accounts')
@section('content')
    <div class="page-heading directory-heading">
        <div><p class="eyebrow">YOUR PEOPLE / CUSTOMERS</p><h1>Customer <em>accounts.</em></h1><p class="lead">The details that help you stay connected with your customers.</p></div>
        <div class="heading-actions"><span class="count-pill">{{ $customers->count() }} customers</span><a class="button" href="{{ route('customers.create') }}">+ Create customer</a></div>
    </div>
    <section class="table-panel" aria-labelledby="customer-list-heading">
        <div class="table-heading"><h2 id="customer-list-heading">Customer directory</h2><span class="muted">Contact information &amp; account status</span></div>
        <div class="table-scroll">
            <table>
                <caption class="sr-only">Customer accounts with contact details, status, and edit links</caption>
                <thead><tr><th scope="col">Account ID</th><th scope="col">Customer</th><th scope="col">Email address</th><th scope="col">Phone number</th><th scope="col">Address</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="record-id">{{ $customer->account_code }}</td>
                            <td><div class="person"><span class="avatar" aria-hidden="true">{{ mb_substr($customer->name, 0, 1) }}</span><strong>{{ $customer->name }}</strong></div></td>
                            <td>{{ $customer->email }}</td><td>{{ $customer->phone ?: '—' }}</td><td>{{ $customer->address ?: '—' }}</td>
                            <td><span class="badge {{ $customer->status === 'Active' ? 'badge-active' : 'badge-inactive' }}"><span aria-hidden="true">&bull;</span> {{ $customer->status }}</span></td>
                            <td><a class="edit-link" href="{{ route('customers.edit', $customer) }}" aria-label="Edit {{ $customer->name }}">Edit &rarr;</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-state">No customer accounts yet. <a href="{{ route('customers.create') }}">Create your first customer.</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer"><span>Showing {{ $customers->count() }} customers</span><a href="{{ route('users') }}">View user accounts &rarr;</a></div>
    </section>
@endsection
