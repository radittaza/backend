<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BankTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     * Dapat diakses oleh semua pengguna (atau pengguna terotentikasi).
     */
    public function index()
    {
        $banks = BankTransfer::select('id', 'name_bank', 'number')->get();

        return response()->json([
            'statusCode' => 200,
            'data' => $banks,
        ]);
    }

    /**
     * Store a newly created resource in storage. (Admin only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_bank' => 'required|string|max:255',
            'number' => 'required|string|max:50|unique:bank_transfers,number',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = Auth::id(); // Admin yang sedang login

        $bankTransfer = BankTransfer::create($data);

        return response()->json(
            [
                'statusCode' => 201,
                'message' => 'Bank transfer created successfully',
                // Mengembalikan data lengkap termasuk user_id dan timestamp (createdAt, updatedAt)
                'data' => $bankTransfer->load('user:id,username'), // Memuat admin yang membuat
            ],
            201,
        );
    }

    /**
     * Display the specified resource. (Admin only)
     * Ini method yang kamu tanyakan, bro.
     */
    public function show(BankTransfer $bankTransfer)
    {
        return response()->json([
            'statusCode' => 200,
            'message' => 'Bank transfer retrieved successfully',
            'data' => $bankTransfer->load('user:id,username,email'),
        ]);
    }

    /**
     * Update the specified resource in storage. (Admin only)
     */
    public function update(Request $request, BankTransfer $bankTransfer)
    {
        $validator = Validator::make($request->all(), [
            'name_bank' => 'sometimes|required|string|max:255',
            'number' => 'sometimes|required|string|max:50|unique:bank_transfers,number,' . $bankTransfer->id,

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bankTransfer->update($validator->validated());

        return response()->json([
            'statusCode' => 200,
            'message' => 'Bank transfer updated successfully',
            'data' => $bankTransfer->fresh()->load('user:id,username'),
        ]);
    }

    /**
     * Remove the specified resource from storage. (Admin only)
     */
    public function destroy(BankTransfer $bankTransfer)
    {
        $activeBookingsCount = $bankTransfer
            ->bookings()
            ->whereNotIn('rental_status', ['completed', 'cancelled'])
            ->count();

        if ($activeBookingsCount > 0) {
            return response()->json(
                [
                    'statusCode' => 400,
                    'message' => 'Cannot delete bank transfer option. It is used in active or pending bookings.',
                ],
                400,
            );
        }

        $bankTransfer->delete();

        return response()->json([
            'statusCode' => 200,
            'message' => 'Bank transfer deleted successfully',
        ]);
    }
}
