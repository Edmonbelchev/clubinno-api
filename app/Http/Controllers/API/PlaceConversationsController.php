<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\PlaceMessages;
use App\Models\Performer;
use Illuminate\Http\Request;
use App\Models\EntertainmentPlace;
use App\Http\Controllers\Controller;

class PlaceConversationsController extends Controller
{
    public function store(Request $request, $id)
    {
        $message = PlaceMessages::create([
            'conversation_id' => $id,
            'sender_id' => $request->sender_id,
            'sender_type' => $request->sender_type,
            'message' => $request->message
        ]);

        if($request->sender_type == 1){
            $sender = EntertainmentPlace::where('id', $request->sender_id)->first();
        }else{
            $sender = Performer::where('id', $request->sender_id)->first();
        }

        return response()->json([
            'status'  => true,
            'message' => $message,
            'date' => Carbon::parse($message->created_at)->format('d.m.Y'),
            'time' => Carbon::parse($message->created_at)->format('H:i'),
            'sender'  => $sender
        ]);
    }

    public function updateMessage(Request $request, $id)
    {
        $ids = $request->input('ids', []);

        // Update the 'readed' column for the IDs in the array
        PlaceMessages::where('conversation_id', $id)->whereIn('id', $ids)->update(['readed' => 1]);

        return response()->json(['message' => 'Read status updated successfully']);
    }
}
