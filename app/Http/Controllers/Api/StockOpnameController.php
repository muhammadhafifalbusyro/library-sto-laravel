<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\StockOpname;

class StockOpnameController extends Controller
{
    public function searchBook($isbn)
    {
        $book = Book::where('isbn_issn', $isbn)
            ->orWhere('item_code', 'like', "%{$isbn}%")
            ->first();

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        // Check if THIS SPECIFIC item_code is already verified
        $existingOpname = StockOpname::where('book_id', $book->id)
            ->where('item_code', $isbn)
            ->where('status', 'verified')
            ->with('user') // Eager load user to show who verified it
            ->first();

        return response()->json([
            'book' => $book,
            'scanned_code' => $isbn,
            'status' => $existingOpname ? 'verified' : 'pending_scan',
            'verified_by' => $existingOpname ? $existingOpname->user->name : null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'item_code' => 'required|string',
            'status' => 'required|string',
            'condition' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Double check before saving to prevent race conditions
        $isVerified = StockOpname::where('book_id', $request->book_id)
            ->where('item_code', $request->item_code)
            ->where('status', 'verified')
            ->exists();

        if ($isVerified) {
             return response()->json(['message' => 'This specific item code is already verified.'], 400);
        }

        $commission = \App\Models\Setting::where('key', 'commission')->first();
        $commissionValue = $commission ? (float)$commission->value : 0;

        $opname = StockOpname::create([
            'user_id' => auth()->id(),
            'book_id' => $request->book_id,
            'item_code' => $request->item_code,
            'status' => $request->status,
            'condition' => $request->condition,
            'notes' => $request->notes,
            'earned_commission' => $commissionValue,
        ]);

        return response()->json([
            'message' => 'Stock opname recorded successfully',
            'data' => $opname,
            'earned_commission' => $commissionValue
        ]);
    }
}
