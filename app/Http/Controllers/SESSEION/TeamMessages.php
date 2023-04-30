<?php

namespace App\Http\Controllers\SESSEION;

use App\Http\Controllers\Controller;
use App\Http\Requests\getRoomMessages;
use App\Http\Requests\SendMessageRequest;
use App\Models\Message;
use App\Models\Team;
use App\Models\TeamRoom;
use Illuminate\Http\Request;

class TeamMessages extends Controller
{
    public function studentsendMessage(SendMessageRequest $request)
    {
        $sender_id = $request->user()->code;
        $room_id = $request['room_id'];
        $content = $request['textContent'];

        Message::create([
            'room_id' => $room_id,
            'sender_id' => $sender_id,
            'content' => $content
        ]);

        return response('message sended succesfully', 200);
    }


    public function teacherSendMessage(SendMessageRequest $request)
    {


        $sender_id = $request->user('')->code;
        $room_id = $request['room_id'];
        $content = $request['textContent'];

        Message::create([
            'room_id' => $room_id,
            'sender_id' => $sender_id,
            'content' => $content
        ]);

        return response('message sended succesfully', 200);

    }



    public function getMessages(Request $request, $id_room)
    {


        // return  response($id_room)   ;

        $user_id = $request->user();
        $room_id = $id_room;

        // ! validate if room is available for student

        $team = Team::select('id')
            ->where('member_1', $user_id->id)
            ->orWhere('member_2', $user_id->id)
            ->orWhere('supervisor_id', $user_id->id)
            ->get()
            ->first();

        // return $team ;
        $messages = Message::select('messages.content', 'students.name as student_name', 'teachers.name as teacher_name', 'messages.created_at')
            ->leftJoin('students', 'messages.sender_id', '=', 'students.code')
            ->leftJoin('teachers', 'messages.sender_id', '=', 'teachers.code')
            ->join('team_rooms', 'team_rooms.id', '=', 'messages.room_id')
            ->where('team_rooms.id', $room_id)
            ->where('team_id', $team->id)
            ->orderBy('created_at', 'asc')
            ->get();



        return response($messages, 200);




    }


}
