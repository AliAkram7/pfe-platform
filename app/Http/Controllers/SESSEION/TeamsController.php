<?php

namespace App\Http\Controllers\SESSEION;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoomRequest;
use App\Models\Student;
use App\Models\Team;
use App\Models\TeamRoom;
use Illuminate\Http\Request;

class TeamsController extends Controller
{


    public function getRooms(Request $request)
    {
        // TODO  still have to join the sender id with the supervisor id
        $student = $request->user("student");

        //* get partner information
        //  ! retrive members ids from Team

        $studentId = $student->id;

        $team_info = Team::whereExists(function ($query) use ($studentId) {
            $query->select('id')
                ->from('students')
                ->whereRaw("JSON_CONTAINS(team_member, '$studentId')");
        })->get()->first();



        $fetch_rooms = TeamRoom::
            select('team_rooms.id AS  id_room', 'name', 'team_rooms.created_at', 'room_name', 'team_rooms.discription')
            ->join('students', 'students.id', '=', 'team_rooms.creater_id')
            ->where('team_id', $team_info->id)
            ->get();
        return $fetch_rooms;
    }

    public function createRoom(CreateRoomRequest $request)
    {
        // !  verfy id team in supervisor pages

        $sender = $request->user();
        $senderId = $sender->id;

        $team_info = Team::whereExists(function ($query) use ($senderId) {
            $query->select('id')
                ->from('students')
                ->whereRaw("JSON_CONTAINS(team_member, '$senderId')");
        })->get()->first();

        TeamRoom::create([
            'team_id' => $team_info->id,
            'room_name' => $request['roomName'],
            'discription' => $request['roomDiscription'],
            'creater_id' => $senderId,
        ]);

        return response('room created ', 200);

    }

    public function getListOfTeams(Request $request)
    {
        // !! fetch list of the teams that the teacher lead !!

        $teacher_id = $request->user()->id;

        $Teams = Team::select('team_member', 'id')->where('id_supervisor', $teacher_id)->get();

        $teams_list = array();
        $team_info = array();

        foreach ($Teams as $team) {
            $member_ids = json_decode($team['team_member']);

            foreach ($member_ids as $member_id) {
                $student_info = Student::find($member_id);
                $student_resouce_info =  $student_info   ;
                $team_info[] = $student_resouce_info;
            }

            $teams_list[] = ['team_id' => $team->id ,   $team_info];

            $team_info = array();
        }

        return  count($teams_list) >  0 ?  response(compact('teams_list'), 200)  :  response('',403) ;

    }

// !! get all rooms of team for teacher

public function getRoomsByTeam(Request $request , $id )
{
    $fetch_rooms = TeamRoom::
    select('team_rooms.id AS  id_room', 'name', 'team_rooms.created_at', 'room_name', 'team_rooms.discription')
    ->join('students', 'students.id', '=', 'team_rooms.creater_id')
    ->where('team_id', $id)
    ->get();


return    $fetch_rooms ;

}


}
