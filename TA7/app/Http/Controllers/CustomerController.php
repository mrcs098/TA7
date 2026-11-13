<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = Customer::orderBy('id')->get();

        return view('customers', ['customers' => $customers]);
    }

    public function create(): View
    {
        return view('customers.create', ['customer' => new Customer()]);
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        Customer::create($this->attributes($request));

        return to_route('customers')->with('success', 'Customer account created successfully.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->attributes($request, $customer));

        return to_route('customers')->with('success', 'Customer account updated successfully.');
    }

    private function attributes(CustomerRequest $request, ?Customer $customer = null): array
    {
        $data = $request->validated();

        // TFA7 names the form field full_name; TA6 stores that value as name.
        return [
            'name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => array_key_exists('phone', $data) ? ($data['phone'] ?? '') : ($customer?->phone ?? ''),
            'address' => array_key_exists('address', $data) ? ($data['address'] ?? '') : ($customer?->address ?? ''),
            'status' => $data['status'] ?? $customer?->status ?? 'Active',
        ];
    }
}
