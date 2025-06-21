<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view-customers');

        $customers = Customer::with('customerType')->orderBy('name')->get();

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

        $customerTypes = CustomerType::all();

        return Inertia::render('Customers/Create', [
            'customerTypes' => $customerTypes
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

        DB::beginTransaction();

        try {
            Customer::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'customer_type_id' => $request->customer_type_id
            ]);

            DB::commit();

            return redirect()->route('konsumen.index')->with('success', 'Konsumen ' . $request->name . ' berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('konsumen.index')
                ->with('error', 'Terjadi kesalahan saat menambahkan konsumen: ' . $th->getMessage())
                ->withInput();
        }
    }

    public function quickStore(Request $request)
    {
        $this->authorize('create-customer');

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers',
            'customer_type_id' => 'required|exists:customer_types,id',
        ]);

        DB::beginTransaction();

        try {
            $customer = Customer::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'customer_type_id' => $request->customer_type_id
            ]);

            $data = Customer::with('customerType')->find($customer->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tambah konsumen berhasil',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            
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

        $customer = Customer::with('customerType')->find($id);
        $customerTypes = CustomerType::all();

        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
            'customerTypes' => $customerTypes
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

        DB::beginTransaction();

        try {
            $customer = Customer::find($id);
            $customer->name = $request->name;
            $customer->phone = $request->phone;
            $customer->customer_type_id = $request->customer_type_id;
            $customer->save();

            DB::commit();

            return redirect()->route('konsumen.index')->with('success', 'Konsumen berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('konsumen.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui konsumen: ' . $th->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-customer');

        DB::beginTransaction();

        try {
            $customer = Customer::find($id);
            $customer->delete();

            DB::commit();

            return redirect()->route('konsumen.index')->with('success', 'Konsumen ' . $customer->name . ' berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('konsumen.index')
                ->with('error', 'Terjadi kesalahan saat menghapus konsumen: ' . $th->getMessage())
                ->withInput();
        }
    }
}
