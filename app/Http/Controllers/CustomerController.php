<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view-customers');

        $customers = Customer::with('customer_type')->orderBy('name')->get();

        return Inertia::render('Customers/Index', [
            'customers' => $customers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-customer');

        $customer_types = CustomerType::all();

        return Inertia::render('Customers/Create', [
            'customerTypes' => $customer_types
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-customer');

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers',
            'customer_type_id' => 'required|exists:customer_types,id',
        ]);

        Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'customer_type_id' => $request->customer_type_id
        ]);

        return redirect()->route('konsumen.index')->with('success', 'Konsumen berhasil ditambahkan.');
    }

    public function quickStore(Request $request)
    {
        $this->authorize('create-customer');

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers',
            'customer_type_id' => 'required|exists:customer_types,id',
        ]);

        try {
            $customer = Customer::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'customer_type_id' => $request->customer_type_id
            ]);

            $data = Customer::with('customer_type')->find($customer->id);

            return response()->json([
                'success' => true,
                'message' => 'Tambah konsumen berhasil',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah konsumen',
                'data' => $th
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('edit-customer');

        $customer = Customer::with('customer_type')->find($id);
        $customer_types = CustomerType::all();

        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
            'customerTypes' => $customer_types
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('edit-customer');

        $request->validate([
            'name' => 'string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers,phone,' . $id,
            'customer_type_id' => 'required|exists:customer_types,id',
        ]);

        $customer = Customer::find($id);
        $customer->name = $request->get('name');
        $customer->phone = $request->get('phone');
        $customer->customer_type_id = $request->get('customer_type_id');

        $customer->save();

        return redirect()->route('konsumen.index')->with('success', 'Konsumen berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-customer');

        $customer = Customer::find($id);
        $customer->delete();

        return redirect()->route('konsumen.index')->with('success', 'Tipe konsumen berhasil dihapus.');
    }
}
