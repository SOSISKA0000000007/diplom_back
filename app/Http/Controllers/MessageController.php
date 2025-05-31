<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $listingId = $request->query('listing_id');
        $messages = Message::where('listing_id', $listingId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $listing = Listing::findOrFail($request->listing_id);
        if ($listing->user_id !== $request->receiver_id) {
            return response()->json(['message' => 'Неверный получатель'], 422);
        }

        $message = Message::create([
            'listing_id' => $request->listing_id,
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        return response()->json(['message' => $message], 201);
    }
}
