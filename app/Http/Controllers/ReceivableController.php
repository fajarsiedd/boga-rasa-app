<?php

namespace App\Http\Controllers;

use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReceivableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-receivables');

        $receivablesQuery = Receivable::with('sale.customer.customerType', 'sale.details.product')->orderBy('due_date', 'asc');

        if ($request->has('search') && $request->search != null) {
            $searchTerm = '%' . $request->search . '%';

            $receivablesQuery->whereHas('sale', function ($query) use ($searchTerm) {
                $query->where('code', 'like', $searchTerm)
                    ->orWhereHas('customer', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', $searchTerm);
                    });
            });
        }

        if ($request->has('due_date') && $request->due_date != null) {
            $receivablesQuery->whereDate('due_date', $request->due_date);
        }

        if ($request->has('status') && $request->status != null) {
            if ($request->status == 'lunas') {
                $receivablesQuery->whereHas('sale', function ($query) {
                    $query->whereNotNull('paid_at');
                });
            } else if ($request->status == 'ditunda') {
                $receivablesQuery->whereHas('sale', function ($query) {
                    $query->whereNull('paid_at');
                });
            }
        }

        if ($request->has('paid_at') && $request->paid_at != null) {
            $paid_at = $request->paid_at;

            $receivablesQuery->whereHas('sale', function ($query) use ($paid_at) {
                $query->whereDate('paid_at', $paid_at);
            });
        }

        $receivables = $receivablesQuery->get();

        return Inertia::render('Receivables/Index', [
            'title' => 'Daftar Piutang',
            'receivables' => $receivables
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        $this->authorize('edit-receivable');

        $receivable = Receivable::with('sale.customer.customerType', 'sale.details.product')->find($id);
        $receivable->due_date = Carbon::parse($receivable->due_date)->setTimezone(config('app.timezone'))->format('Y-m-d');

        return Inertia::render('Receivables/Edit', [
            'title' => 'Edit Piutang - ' . $receivable->sale->code,
            'receivable' => $receivable
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('edit-receivable');

        DB::beginTransaction();

        try {
            $receivable = Receivable::with('sale')->find($id);

            $paidAtValue = $request->boolean('is_paid') ? Carbon::now() : null;

            if ($paidAtValue) {
                $receivable->sale->paid_at = $paidAtValue;
                $receivable->sale->save();
            } else {
                $receivable->sale->paid_at = null;
                $receivable->sale->save();
            }

            $receivable->due_date = $request->due_date;
            $receivable->save();

            DB::commit();

            return redirect()->route('piutang.index')->with('success', 'Piutang ' . $receivable->sale->code . ' berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('piutang.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui piutang: ' . $th->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-receivable');

        DB::beginTransaction();

        try {
            $receivable = Receivable::with('sale')->find($id);
            $receivable->delete();

            DB::commit();

            return redirect()->route('piutang.index')
                ->with('success', 'Piutang ' . $receivable->sale->code . ' berhasil dihapus.');
        } catch (\Exception $th) {
            DB::rollBack();

            return redirect()->route('piutang.index')
                ->with('error', 'Terjadi kesalahan saat menghapus piutang: ' . $th->getMessage());
        }
    }
}
