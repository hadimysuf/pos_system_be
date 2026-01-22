<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * 📃 List semua supplier (aktif + tidak aktif)
     */
    public function index()
    {
        return Supplier::orderBy('name')->get();
    }

    /**
     * ➕ Tambah supplier baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $supplier = Supplier::create($request->all());

        return response()->json([
            'message' => 'Supplier berhasil ditambahkan',
            'data'    => $supplier
        ], 201);
    }

    /**
     * 📌 Detail 1 supplier
     */
    public function show(Supplier $supplier)
    {
        return $supplier;
    }

    /**
     * ✏️ Update supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $supplier->update($request->all());

        return response()->json([
            'message' => 'Supplier berhasil diperbarui',
            'data'    => $supplier
        ]);
    }

    public function activate(Supplier $supplier)
    {
        if ($supplier->is_active) {
            return response()->json([
                'message' => 'Supplier sudah aktif'
            ], 400);
        }

        $supplier->update(['is_active' => true]);

        return response()->json([
            'message' => 'Supplier berhasil diaktifkan kembali',
            'data' => $supplier
        ]);
    }


    /**
     * 🗑 Nonaktifkan supplier (soft delete manual)
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->update(['is_active' => false]);

        return response()->json([
            'message' => 'Supplier berhasil dinonaktifkan'
        ]);
    }
}
