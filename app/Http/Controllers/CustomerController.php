<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\AuditLogService;
use App\Support\EncryptedId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(): View
    {
        return view('customers.index', [
            'customers' => Customer::query()->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string'],
        ]);

        $customer = Customer::query()->create($data);

        $this->auditLogService->record(
            action: 'customer.created',
            user: $request->user(),
            loggable: $customer,
            context: ['name' => $customer->name],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Customer created.');
    }

    public function update(Request $request, string $customerId): RedirectResponse
    {
        $customer = Customer::query()->findOrFail(EncryptedId::decode($customerId));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email,'.$customer->id],
            'address' => ['required', 'string'],
        ]);

        $customer->update($data);

        $this->auditLogService->record(
            action: 'customer.updated',
            user: $request->user(),
            loggable: $customer,
            context: ['name' => $customer->name],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Customer updated.');
    }

    public function destroy(Request $request, string $customerId): RedirectResponse
    {
        $customer = Customer::query()->withCount('orders')->findOrFail(EncryptedId::decode($customerId));

        if ($customer->orders_count > 0) {
            return back()->withErrors(['customer' => 'Cannot delete customer with existing orders.']);
        }

        $customerName = $customer->name;
        $customer->delete();

        $this->auditLogService->record(
            action: 'customer.deleted',
            user: $request->user(),
            loggable: null,
            context: ['name' => $customerName],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('status', 'Customer deleted.');
    }
}
