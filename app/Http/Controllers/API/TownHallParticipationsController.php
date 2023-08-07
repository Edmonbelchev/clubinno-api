<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\TownHall;

use App\Models\Performer;
use App\Models\TownMessages;
use Illuminate\Http\Request;
use App\Models\TownConversations;
use App\Http\Controllers\Controller;
use App\Models\TownHallParticipations;
use App\Models\TownParticipationNotes;

class TownHallParticipationsController extends Controller
{
    public function signedPerformersByMonth(Request $request, $date){
        $start = Carbon::parse($date)->startOfMonth()->setTime(00, 00, 00);
        $end = Carbon::parse($date)->endOfMonth()->setTime(23, 59, 59);

        $records = TownHallParticipations::where('town_hall_id', $request->id)
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
         
             $unreadMessages = TownMessages::where('sender_type', 2)
                 ->where('sender_id', $performerId)
                 ->where('conversation_id', function ($query) use ($participationId) {
                    $query->select('id')
                    ->from('town_conversations')
                    ->where('participation_id', $participationId)
                    ->limit(1)
                    ->first();
                 })
                 ->where('readed', 0)
                 ->get();

             $conversation = TownConversations::select('id')
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

        $records = TownHallParticipations::where('town_hall_id', $request->id)
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

    public function sendMessage(Request $request, $id){
        TownHallParticipations::where('id', $id)->update(['message' => $request->message]);

        $model = TownHallParticipations::select('message')->where('id', $id)->pluck('message')->first();

        return response()->json([
            'status' => true,
            'value'  => $model,
            'message' => __('Успешно добавихте съобщение!')
        ]);
    }

    public function sendHonorarium(Request $request, $id){
        // $validator = Validator::make($request->all(), [
        //     'honorarium' => ['required', 'numeric', 'min:100'],
        // ]);

        // if ($validator->fails()) {
        //     // return errors as JSON response
        //     return response()->json(['errors' => $validator->errors()]);
        // }

        TownHallParticipations::where('id', $id)->update(['honorarium' => $request->honorarium]);

        $model = TownHallParticipations::select('honorarium')->where('id', $id)->pluck('honorarium')->first();

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
        
        $model = TownHallParticipations::where('id', $id)->first();

        if($model->active == 0){
            $model->where('id', $id)->update(['active' => 1]);

            /* GET USER TYPE */
            $town = TownHall::where('id', $model->town_hall_id)->with('user')->first();

             /* CREATE CONVERSATION */
            $conversation = TownConversations::create([
                'town_hall_id' => $model->town_hall_id,
                'performer_id' => $model->performer_id,
                'participation_id' => $id,
                'date' => $model->date             
            ]);

            $date = Carbon::parse($model->date)->format('d.m.Y');
            $time = Carbon::parse($model->created_at)->format('H:i');

            $message = htmlspecialchars("<div class='invite-message'><b>ПОКАНА ЗА УЧАСТИЕ</b> $date в $time ч.</div>",  ENT_QUOTES, 'UTF-8');

            if($message){
                /* CREATE INVITE MESSAGE */
                TownMessages::create([
                    'conversation_id' => $conversation->id,
                    'sender_id'       => $model->town_hall_id,
                    'sender_type'     => $town->user[0]->type,
                    'message'         => $message,         
                ]);
            }

            if($request->message){
                /* SEND A MESSAGE */
                $firstMessage = TownMessages::create([
                    'conversation_id' => $conversation->id,
                    'sender_id'   => $model->town_hall_id,
                    'sender_type' => $town->user[0]->type,
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
        TownHallParticipations::where('id', $id)
        ->update([
            'time' => Carbon::parse($request->time)->format('H:i:s'),
            'date' => $request->date,
        ]);

        return response()->json([
            'status' => true,
        ]);
    }

    public function updateTime(Request $request, $id){
        TownHallParticipations::where('id', $id)->update([
            'time' => Carbon::parse($request->time)->format('H:i:s'),
        ]);

        return response()->json([
            'status' => true,
        ]);
    }

    public function destroy(Request $request, $id){
            TownHallParticipations::where('id', $id)
            ->update(['deleted_at' => Carbon::now()]);

            $conversation = TownConversations::select('id', 'town_hall_id', 'participation_id')
            ->where('participation_id', $id)
            ->first();

            if($conversation){
                $message = TownMessages::create([
                    'conversation_id' => $conversation->id,
                    'sender_id'   => $conversation->town_hall_id,
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
        $note = TownParticipationNotes::create([
            'participation_id' => $id,
            'note' => $request->note,
            'user_id' => $request->user_id,
            'user_type' => $request->user_type,
        ]);

        if($request->user_type == 4){
            $sender = TownHall::where('id', $request->sender_id)->first();
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