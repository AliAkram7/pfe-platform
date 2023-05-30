<?php

namespace App\Http\Controllers\SESSEION;

use App\Http\Requests\EmailVerificationRequest;
use App\Http\Requests\invitRequest;
use App\Http\Requests\studentResponseToInvitationRequest;
use App\Http\Requests\StudentupdateInfoRequest;
use App\Models\Invitation;
use App\Models\Specialitie;
use App\Models\Student;
use App\Models\Students_Account_Seeder;
use App\Models\Student_speciality;
use App\Models\Teacher;
use App\Models\Team;
use App\Models\TeamRoom;
use App\Models\Theme;
use App\Models\Year_scholar;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use \ErrorException;

class sessionStudentController extends Controller
{

    public function getStudentInfo(Request $request)
    {
        $role = "student";

        $user = $request->user('student');

        return response(compact('user', 'role'));


    }
    public function updateStudentInfo(StudentupdateInfoRequest $request)
    {
        $credentials = $request->validated();

        $student = Auth::guard('student')->user();
        $logged = Students_Account_Seeder::select('logged')->where('code', $student->code)->get()->first()['logged'];

        if (!$logged) {

            if (empty($credentials['email']) || empty($credentials['tel']) || empty($credentials['newPassword'])) {
                return response('bad update', 403);
            } else {


                $student->update([
                    'password' => bcrypt($credentials['newPassword']),
                    'email' => $credentials['email'],
                    'tel' => $credentials['tel'],
                ]);
                // $student->notify(new EmailVerificationNotification);
                \DB::update('update students_account_seeders set logged = true where code = ?', [$student->code]);

                return response('updated successfully', 201);
            }
        }
        if ($logged) {

            if (!$student || !Hash::check($credentials['prPassword'], $student->password)) {
                return response('bad cred', 403);
            }

            if (empty($credentials['prPassword'])) {
                return response('error in update', 402);
            }
            if (!empty($credentials['newPassword'])) {
                $student->update(['password' => bcrypt($credentials['newPassword'])]);
            }
            if ($student->update($request->all())) {
                return response('updated', 201);
            } else {
                return response('error in update', 402);
            }
        }
    }


    public function getInscriptions(Request $request)
    {

        $student = $request->user('student');

        $getStudent_specialties = Student_speciality::select()
            ->where('student_id', $student->id)
            // ->groupBy('speciality_id', 'id')
            ->get()
        ;

        $response = [];

        foreach ($getStudent_specialties as $inscription) {

            $response[] = Specialitie::select(
                'fullname',
                'student_specialities.id  as value',
                \DB::raw("CONCAT(start_date, ' - ', end_date) AS label"),

            )
                ->leftJoin('student_specialities', 'specialities.id', '=', 'student_specialities.speciality_id')
                ->leftJoin('year_scholars', 'year_scholars.id', '=', 'year_scholar_id')
                ->where('specialities.id', $inscription->speciality_id)
                ->where('year_scholar_id', $inscription->year_scholar_id)
                ->where('student_id', $student->id)
                ->get();

        }


        $newArray = [];

        foreach ($response as $item) {
            $label = $item[0]['fullname'];
            $link = [];
            foreach ($item as $val) {
                unset($val['fullname']);
                $link[] = $val;
            }
            if (isset($newArray[$label])) {
                $newArray[$label]['links'][] = $link[0];
            } else {
                $newArray[$label] = [
                    'label' => $label,
                    'links' => $link
                ];
            }
        }

        $newArray = array_values($newArray);


        return $newArray;






    }





    public function getRanking(Request $request, $studentSpecialtyId)
    {
        $student = $request->user('student');

        $year_scholar_id = Student_speciality::get()->where('id', $studentSpecialtyId)->first()->year_scholar_id;


        $SpecialtyId = Student_speciality::get()->where('id', $studentSpecialtyId)->first()->speciality_id;


        $year_scholar_String = Year_scholar::select(
            \DB::raw("CONCAT(start_date, ' - ', end_date) AS yearScholar"),
        )->where('id', $year_scholar_id)->get()->first();



        $get_Speciality_name = Specialitie::get()->where('id', $SpecialtyId)->first()->fullname;

        $this_year = date('Y');

        $studentRanks = Students_Account_Seeder::
            select('students_account_seeders.code', 'students_account_seeders.name AS student_name', 'ranks.ms1', 'ranks.ms2', 'ranks.mgc', 'observation')
            ->join('student_specialities', 'students_account_seeders.id', '=', 'student_specialities.student_id')
            ->join('specialities', 'student_specialities.speciality_id', '=', 'specialities.id')
            ->join('ranks', 'student_specialities.id', '=', 'ranks.student_specialite_id')
            ->join('year_scholars', 'student_specialities.year_scholar_id', '=', 'year_scholars.id')
            ->where('year_scholars.id', $year_scholar_id)
            ->where('student_specialities.speciality_id', $SpecialtyId)
            ->groupBy('specialities.id', 'students_account_seeders.id', 'students_account_seeders.name', 'ranks.ms1', 'ranks.ms2', 'ranks.mgc', 'specialities.fullname', 'observation', 'students_account_seeders.code')
            ->orderBy('ranks.mgc', 'desc')
            ->get();

        $response = [
            'speciality_name' => $get_Speciality_name,
            'year_scholar' => $year_scholar_String->yearScholar,
            'student_rank' => $studentRanks,
        ];

        return response($response);
    }
    public function invitePartner(invitRequest $request)
    {

        $credentials = $request->validated();

        $sender = $request->user('student');

        if ($sender->code == $request['code']) {
            return response('', 403);
        }

        $receiver = Student::get()->where('code', $credentials['code'])->first();

        // check that the sender and receiver are with the same speciality

        $this_year = date('Y');
        // $last_year = "" . $this_year - 1 ."";
        // $year_scholar = "$last_year-$this_year";


        // return response(compact('receiverId', 'senderId'),200)  ;
        // // try {

        $sender_inscription = \DB::table('year_scholars AS ys')
            ->join('student_specialities AS ss', 'ys.id', '=', 'ss.year_scholar_id')
            ->select('ys.id AS year_id', 'ss.id', 'ss.speciality_id', 'ys.end_date')
            ->where('ss.student_id', $sender->id)
            ->orderBy('ys.end_date', 'DESC')
            ->limit(1)
            ->get()->first();


        $receiver_inscription = \DB::table('year_scholars AS ys')
            ->join('student_specialities AS ss', 'ys.id', '=', 'ss.year_scholar_id')
            ->select('ys.id AS year_id', 'ss.id', 'ss.speciality_id', 'ys.end_date')
            ->where('ss.student_id', $receiver->id)
            ->orderBy('ys.end_date', 'DESC')
            ->limit(1)
            ->get()->first();


        // $receiver_speciality = Student_speciality::get()->where('student_id', $receiver->id)->where('year_scholar', $this_year)->first()->speciality_id;
        // $sender_speciality = Student_speciality::get()->where('student_id', $sender->id)->where('year_scholar', $this_year)->first()->speciality_id;


        if ($sender_inscription->year_id != $receiver_inscription->year_id) {
            return response('two deferent years', 403);
        }
        if ($sender_inscription->speciality_id != $receiver_inscription->speciality_id) {
            return response('two deferent specialties', 403);
        }


        // ! check that the inviter are already in team or already send the invitation

        // return $sender_inscription->year_id ;

        $receiverId = $receiver->id ;
        $senderId = $sender->id ;

        $checkIfReceiverAreAlreadyInTeam = \DB::table('teams')
            ->where('year_scholar_id', $receiver_inscription->year_id)
            // ->where('member_1', $receiver->id)
            // ->orWhere('member_2', $receiver->id)
            ->where(function ($query) use ($receiverId) {
                $query->where('member_1', $receiverId)
                    ->orWhere('member_2', $receiverId);
            })
            ->exists()
        ;

        $checkIfSenderAreAlreadyInTeam = \DB::table('teams')
            ->where('year_scholar_id', $sender_inscription->year_id)
            ->where(function ($query) use ($senderId) {
                $query->where('member_1', $senderId)
                    ->orWhere('member_2', $senderId);
            })
            ->exists()
        ;

        // !! check if the students is already in team

        $checkIfInvitationIsAlreadyExist = \DB::table('invitations')
            ->where('sender_id', $sender->id)
            ->where('receiver_id', $receiver->id)
            ->where('isAccepted', 0)
            ->exists()
            ||
            \DB::table('invitations')
                ->where('receiver_id', $sender->id)
                ->where('sender_id', $receiver->id)
                ->where('isAccepted', 0)
                ->exists()
        ;

        if ($checkIfInvitationIsAlreadyExist || $checkIfSenderAreAlreadyInTeam || $checkIfReceiverAreAlreadyInTeam) {
            // !! developing response test
            return response([
                'InvitationIsAlreadyExist' => $checkIfInvitationIsAlreadyExist,
                'senderAreAlreadyInTeam' => $checkIfSenderAreAlreadyInTeam,
                'receiverAreAlreadyInTeam' => $checkIfReceiverAreAlreadyInTeam
            ], 403);

        }
        Invitation::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'isAccepted' => 0,
        ]);

        return response('success', 200);


        // }
    }
    public function getRecievedInvitation(Request $request)
    {
        $student = $request->user('student');
        $invitation = Invitation::
            select('tel', 'email', 'name', 'code', 'invitations.created_at', 'isAccepted')
            ->join('students', 'students.id', '=', 'invitations.sender_id')
            ->where('receiver_id', $student->id)
            ->where('isAccepted', 0)
            ->orWhere('isAccepted', 1)
            ->where('code', '!=', $student->code)
            ->groupBy('name', 'code', 'invitations.created_at', 'tel', 'email', 'isAccepted')
            ->get()
        ;
        return response($invitation, 200);
    }
    public function getSendedInvitation(Request $request)
    {
        $student = $request->user('student');

        $invitation = Invitation::
            select('tel', 'email', 'name', 'code', 'invitations.created_at', 'isAccepted')
            ->join('students', 'students.id', '=', 'invitations.receiver_id')
            ->where('sender_id', $student->id)
            ->where('isAccepted', 0)
            ->groupBy('name', 'code', 'invitations.created_at', 'tel', 'email', 'isAccepted')
            ->get()
        ;
        return response($invitation, 200);

    }
    public function studentResponseToInvitation(studentResponseToInvitationRequest $request)
    {


        //  ! reciever
        $receiver_id = $request->user('student')->id;

        // !  sender
        $sender_id = Student::get()
            ->where('code', $request['codeSender'])
            ->first()
            ->id;
        // !----------------------------------------------------------------------------------------
// * chack if invitatin exist
        $checkInvition = \DB::table('invitations')
            ->where('sender_id', $sender_id)
            ->where('receiver_id', $receiver_id)
            ->exists();

        $checkInvition_sm = \DB::table('invitations')
            ->where('sender_id', $receiver_id)
            ->where('receiver_id', $sender_id)
            ->exists();

        if (!$checkInvition && !$checkInvition_sm) {
            return response('invitation are not exist', 403);
        }
        // !-----------------------------------------------------------------------------------------------------------
// * check if reciver accpet the invitation  the create the team

        if ($request['actionValue'] == 1) {

            $invitation = Invitation::get()
                ->where('sender_id', $sender_id)
                ->where('receiver_id', $receiver_id)
                ->first();

            // ! refuse the rest of invitation in accepte one
            \DB::update('update invitations  set isAccepted = -1 where ( receiver_id  = ? or  receiver_id  = ?)   or  (sender_id  = ? or  sender_id  = ?)', [$receiver_id, $sender_id, $receiver_id, $sender_id]);


            // Invitation::create([
            //     'sender_id' => $receiver_id,
            //     'receiver_id' => $sender_id,
            //     'isAccepted' => 1,
            // ]);

            // * create team

            $receiver_inscription = \DB::table('year_scholars AS ys')
                ->join('student_specialities AS ss', 'ys.id', '=', 'ss.year_scholar_id')
                ->select('ys.id AS year_id', 'ss.id', 'ss.speciality_id', 'ys.end_date')
                ->where('ss.student_id', $receiver_id)
                ->orderBy('ys.end_date', 'DESC')
                ->limit(1)
                ->get()
                ->first();


            if (
                $team = Team::create([
                    'member_1' => $sender_id,
                    'member_2' => $receiver_id,
                    "choice_list"=> "[]",
                    'year_scholar_id' => $receiver_inscription->year_id
                ])
            ) {

                $invitation->update(["isAccepted" => 1]);
                Invitation::
                    where('sender_id', $receiver_id)->delete();
                Invitation::
                    where('sender_id', $sender_id)->delete();
            }



            //             Chat room name: Project Partners
            // Description: Working on a final project can be overwhelming, but you don't have to do it alone. Join Project Partners to find a study partner who can help you stay accountable and motivated as you work towards completing your project. Share your goals, get feedback, and celebrate your successes together.

            // * create first room
            TeamRoom::create([
                'team_id' => $team->id,
                'room_name' => 'Project Partners',
                'discription'
                => "Working on a final project can be overwhelming, but you don't have to do it alone. Join Project Partners to find a study partner who can help you stay accountable and motivated as you work towards completing your project. Share your goals, get feedback, and celebrate your successes together.:",
                'creater_id' => 4,
            ]);

            return response('accepted', 201);
            // !-------------------------------------------------------------------------------------------------------------------------------------------------------------
// * check if receiver refuse  the invitation

        } else if ($request['actionValue'] == -1) {



            $invitation_exist = \DB::table('invitations')
                ->where('sender_id', $sender_id)
                ->where('receiver_id', $receiver_id)
                ->where('isAccepted', 0)
                ->exists();

            $invitation = Invitation::get()
                ->where('sender_id', $sender_id)
                ->where('receiver_id', $receiver_id)
                ->where('isAccepted', 0)
                ->first();
            if ($invitation_exist) {
                $invitation->update(["isAccepted" => -1]);
                // return response('refused', 201);
            }



            // ! -----------------------------------------------------------------------------------------------
// * cancel the invitation


            $invitation_symmetric_exist = \DB::table('invitations')
                ->where('sender_id', $receiver_id)
                ->where('receiver_id', $sender_id)
                ->where('isAccepted', 0)
                ->exists();


            if ($invitation_symmetric_exist) {
                $invitation_symmetric = Invitation::get()
                    ->where('sender_id', $receiver_id)
                    ->where('receiver_id', $sender_id)
                    ->where('isAccepted', 0)
                    ->first();
                $invitation_symmetric->update(["isAccepted" => -1]);
                // return response('invitation cancled', 201);
            }


            return response('invitation canceled', 201);



        } else {
            return response('', 201);
        }





    }

    public function getStudentTeamInformation(Request $request)
    {
        $student = $request->user("student");

        //* get partner information
        //  ! retrive members ids from Team

        $studentId = $student->id;

        $student_inscription = \DB::table('year_scholars AS ys')
            ->join('student_specialities AS ss', 'ys.id', '=', 'ss.year_scholar_id')
            ->select('ys.id AS year_id', 'ss.id', 'ss.speciality_id', 'ys.end_date')
            ->where('ss.student_id', $studentId)
            ->orderBy('ys.end_date', 'DESC')
            ->limit(1)
            ->get()
            ->first();

        try {

            // $students = Team::whereExists(function ($query) use ($studentId) {
            //     $query->select('id')
            //         ->from('students')
            //         ->whereRaw("JSON_CONTAINS(team_member, '$studentId')");
            // })->get()->first();

            // $students = Team::select('member_1', 'member_2', 'supervisor_id', 'theme_id')
            //     ->where('year_scholar_id', $student_inscription->year_id)
            //     ->where('member_1', $studentId)
            //     ->orWhere('member_2', $studentId)
            //     ->get()
            //     ->first();

                $students = Team::select('member_1', 'member_2', 'supervisor_id', 'theme_id')
                ->where('year_scholar_id', $student_inscription->year_id)
                ->where(function ($query) use ($studentId) {
                    $query->where('member_1', $studentId)
                          ->orWhere('member_2', $studentId);
                })
                ->get()
                ->first();



            $students_ids = [$students->member_1, $students->member_2];


            $team_members = array();

            foreach ($students_ids as $student_id) {
                $team_members[] = Student::get()->where('id', $student_id)->first();
            }


            //**  fetch the supervisor information *


            $supervisor_info = Teacher::select('name', 'institutional_email', 'personal_email', 'tel')->where('id', $students->supervisor_id)->get()->first();



            // ** fetch theme information *

            $theme_info = Theme::select('title', 'description')->where('id', $students->theme_id)->get();


            $response = ['supervsorInfo' => $supervisor_info, 'team_members' => $team_members, 'theme_info' => $theme_info];

            return response($response, 200);
        } catch (ErrorException $th) {
            return response('no team', 403);
        }

    }

    public function refreshToken(Request $request)
    {
        $account_status = Students_Account_Seeder::select('account_status')->where('code', $request->user()->code)->get()->first();

        if ($account_status->account_status == 0) {
            return response(["message" => 'Unauthorized'], 401);
        }

        $logged = Students_Account_Seeder::select('logged')->where('code', $request->user()->code)->get()->first()['logged'];

        $student = $request->user();

        $token = auth()->claims($student->getJWTCustomClaims())->refresh(false, true);

        return response(compact('token'), 200);

    }

    public function checkEmailVerification(EmailVerificationRequest $request)
    {

        $user = $request->user();

        // return $user->email ;

        $otp = $this->otp->validate($user->email, $request->otp);
        if (!($otp->status)) {
            return response()->json([
                'error' => $otp
            ], 401);
        }
        $student = Students_Account_Seeder::where('code', $user->code)->first();
        $student->update(['logged' => true]);
        return response()->json([
            'access_token' => JWTAuth::fromUser($student),
        ], 200);


    }



}
