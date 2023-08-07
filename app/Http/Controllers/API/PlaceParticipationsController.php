<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Performer;
use Illuminate\Http\Request;
use App\Models\PlaceMessages;
use App\Models\EntertainmentPlace;
use App\Models\PlaceConversations;
use App\Models\PlaceParticipations;
use App\Http\Controllers\Controller;
use App\Models\PlaceParticipationNotes;

class PlaceParticipationsController extends Controller
{
    public function signedPerformersByMonth(Request $request, $date){
        $start = Carbon::parse($date)->startOfMonth()->setTime(00, 00, 00);
        $end = Carbon::parse($date)->endOfMonth()->setTime(23, 59, 59);

        $records = PlaceParticipations::where('entertainment_id', $request->id)
        ->whereBetween('date', [$start, $end])
        ->where('deleted_at', null)
        ->with('performer')
        ->orderBy('date')
        ->orderBy('time')
        ->get();

        /* CHECK FOR UNREAD MESSAGES SEND FROM PERFORMER */
        $unreadMessages = [];

        foreach ($records as $record) {
            $performerId = $record->performer->id;
            $participationId = $record->id;
        
            $unreadMessages = PlaceMessages::where('sender_type', 2)
                ->where('sender_id', $performerId)
                ->where('conversation_id', function ($query) use ($participationId) {
                    $query->select('id')
                        ->from('place_conversations')
                        ->where('participation_id', $participationId)
                        ->limit(1)
                        ->first();
                })
                ->where('readed', 0)
                ->get();
            
            $conversation = PlaceConversations::select('id')
            ->where('participation_id', $participationId)
            ->latest()
            ->pluck('id')
            ->first();

            $record->unread_messages = $unreadMessages->isNotEmpty();
            $record->conversation = $conversation;
            $record->note = $record->latestNote($request->user_id);
        }

        return response()->json([
            'status' => true,
            'records'  => $records,
            'start' => $start,
            'end' => $end
        ]);
    }

    public function editParticipation(Request $request, $date){
        $start = Carbon::parse($date)->startOfMonth()->setTime(00, 00, 00);
        $end = Carbon::parse($date)->endOfMonth()->setTime(23, 59, 59);

        $requestedDate = $request->date;
        $requestedDate = Carbon::createFromFormat('F j, Y', $requestedDate);
        $requestedDate = $requestedDate->format('Y-m-d');

        $records = PlaceParticipations::where('entertainment_id', $request->id)
        ->whereBetween('date', [$start, $end])
        ->where('deleted_at', null)
        ->with('performer')
        ->orderBy('date')
        ->orderBy('time')
        ->get();

        foreach ($records as $record) {
            $record->available = $record->performer->id != $request->performer_id ?? false;
            $record->current = $record->date == $requestedDate && $record->performer->id == $request->performer_id ?? false;
        }

        return response()->json([
            'status' => true,
            'records'  => $records,
            'start' => $start,
            'end' => $end
        ]);
    }

    public function sendNote(Request $request, $id){
        PlaceParticipations::where('id', $id)->update(['notes' => $request->notes]);

        $model = PlaceParticipations::select('notes')->where('id', $id)->pluck('notes')->first();

        return response()->json([
            'status' => true,
            'value'  => $model,
            'message' => __('Успешно добавихте бележка!')
        ]);
    }

    public function sendMessage(Request $request, $id){
        PlaceParticipations::where('id', $id)->update(['message' => $request->message]);

        $model = PlaceParticipations::select('message')->where('id', $id)->pluck('message')->first();

        return response()->json([
            'status' => true,
            'value'  => $model,
            'message' => __('Успешно добавихте съобщение!')
        ]);
    }

    public function sendHonorarium(Request $request, $id){
        PlaceParticipations::where('id', $id)->update(['honorarium' => $request->honorarium]);

        $model = PlaceParticipations::select('honorarium')->where('id', $id)->pluck('honorarium')->first();

        return response()->json([
            'status' => true,
            'value'  => $model,
            'message' => __('Успешно добавихте хонорар!')
        ]);
    }

    public function manageInvite(Request $request, $id){
        /*  Activity status
            0 - Send invite
            1 - Pending invite
            2 - Active
        */
        
        $model = PlaceParticipations::where('id', $id)->first();

        if($model->active == 0){
            $model->where('id', $id)->update(['active' => 1]);
            
            /* GET USER TYPE */
            $type = EntertainmentPlace::where('id', $model->entertainment_id)->with('user')->pluck('type')->first();

            /* CREATE CONVERSATION */
            $conversation = PlaceConversations::create([
                'entertainment_id' => $model->entertainment_id,
                'performer_id' => $model->performer_id,
                'participation_id' => $id,
                'date' => $model->date             
            ]);

            $date = Carbon::parse($model->date)->format('d.m.Y');
            $time = Carbon::parse($model->created_at)->format('H:i');

            $message = htmlspecialchars("<div class='invite-message'><b>ПОКАНА ЗА УЧАСТИЕ</b> $date в $time ч.</div>",  ENT_QUOTES, 'UTF-8');
            if($message){
                /* CREATE INVITE MESSAGE */
                PlaceMessages::create([
                    'conversation_id' => $conversation->id,
                    'sender_id'       => $model->entertainment_id,
                    'sender_type'     => $type,
                    'message'         => $message,         
                ]);
            }

            if($request->message){
                /* SEND A MESSAGE */
                $firstMessage = PlaceMessages::create([
                    'conversation_id' => $conversation->id,
                    'sender_id'   => $model->entertainment_id,
                    'sender_type' => $type,
                    'message'     => $request->message,
                    'created_at'  => Carbon::parse(now())->addSeconds(1)       
                ]);
            }

            return response()->json([
                'status' => true,
                'model'  => $model,
                'conversation' => $conversation,
                'message' => __('Поканата е изпратена!'),
                'messages' => array(
                    'welcome_message' => $message ?? '',
                    'message' => $firstMessage->message ?? ''
                ) 
            ]);
        }
    }

    public function update(Request $request, $id){
        PlaceParticipations::where('id', $id)
        ->update([
            'time' => Carbon::parse($request->time)->format('H:i:s'),
            'date' => $request->date,
        ]);

        return response()->json([
            'status' => true,
        ]);
    }

    public function updateTime(Request $request, $id){
        PlaceParticipations::where('id', $id)->update([
            'time' => Carbon::parse($request->time)->format('H:i:s'),
        ]);

        return response()->json([
            'status' => true,
        ]);
    }

    public function destroy(Request $request, $id){
            PlaceParticipations::where('id', $id)
            ->update(['deleted_at' => Carbon::now()]);

            $conversation = PlaceConversations::select('id', 'entertainment_id', 'participation_id')
            ->where('participation_id', $id)
            ->first();

            if($conversation){
                $message = PlaceMessages::create([
                    'conversation_id' => $conversation->id,
                    'sender_id'   => $conversation->entertainment_id,
                    'sender_type' => 1,
                    'message'     => $request->message,
                    'created_at'  => Carbon::parse(now())->addSeconds(1)       
                ]);
            }

            return response()->json([
                'status'  => true,
                'deleted' => true,
                'message' => $message ?? '',
                'id'      => $id,
            ]);
    }

    public function addNote(Request $request, $id){
        $note = PlaceParticipationNotes::create([
            'participation_id' => $id,
            'note' => $request->note,
            'user_id' => $request->user_id,
            'user_type' => $request->user_type,
        ]);

        if($request->user_type == 1){
            $sender = EntertainmentPlace::where('id', $request->sender_id)->first();
        }else{
            $sender = Performer::where('id', $request->sender_id)->first();
        }

        return response()->json([
            'status'  => true,
            'date' => Carbon::parse($note->created_at)->format('d.m.Y'),
            'time' => Carbon::parse($note->created_at)->format('H:i'),
            'message' => array(
                'message' => $note->note
            ),
            'sender' => $sender
        ]);
    }
}
