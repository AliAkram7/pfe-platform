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

    public function getRanking(Request $request)
    {
        $student = $request->user('student');

        $getStudent_speciality_id = Student_speciality::get()->where('student_id', $student->id)->first()->speciality_id;

        $get_Speciality_name = Specialitie::get()->where('id', $getStudent_speciality_id)->first()->fullname;

        $this_year = date('Y');
        $last_year = " " . $this_year - 1 . "";

        $year_scholar = "$last_year-$this_year";

        $studentRanks = Student::
            select('students.code', 'students.name AS student_name', 'ranks.ms1', 'ranks.ms2', 'ranks.mgc', 'observation')
            ->join('student_specialities', 'students.id', '=', 'student_specialities.student_id')
            ->join('specialities', 'student_specialities.speciality_id', '=', 'specialities.id')
            ->join('ranks', 'student_specialities.id', '=', 'ranks.student_specialite_id')
            ->where('year_scholar', $year_scholar)
            ->where('student_specialities.speciality_id', $getStudent_speciality_id)
            ->groupBy('specialities.id', 'students.id', 'students.name', 'ranks.ms1', 'ranks.ms2', 'ranks.mgc', 'specialities.fullname', 'year_scholar', 'observation', 'students.code')
            ->orderBy('ranks.mgc', 'desc')
            ->get();

        $response = [
            'speciality_name' => $get_Speciality_name,
            'year_scholar' => $year_scholar,
            'student_rank' => $studentRanks,
        ];

        return response($response);
    }
    public function invitePartner(invitRequest $request)
    {

        $credentials = $request->validated() ;

        $sender = $request->user('student');

        if ($sender->code == $request['code']) {
            return response('', 403);
        }

        $receiver = Student::get()->where('code', $credentials['code'])->first();

        // check that the sender and receiver are with the same speciality

        $this_year = date('Y');
        $last_year = "" . $this_year - 1 ."";
        $year_scholar = "$last_year-$this_year";


        // return response(compact('receiverId', 'senderId'),200)  ;
        // // try {
            $reciever_speciality = Student_speciality::get()->where('student_id', $receiver->id)->where('year_scholar', $year_scholar)->first()->speciality_id;
            $sender_speciality = Student_speciality::get()->where('student_id', $sender->id)->where('year_scholar', $year_scholar)->first()->speciality_id;


        if ($reciever_speciality != $sender_speciality) {
            return response('hello 1', 403);
        }

        // ! check that the inviter are alredy in team or alredy send the invitation

        $checkIfRecieverAreAlredyInTeam = \DB::table('invitations')
            ->where('receiver_id', $receiver->id)
            ->where('isAccepted', 1)
            ->exists();

        $checkIfSenderAreAlredyInTeam = \DB::table('invitations')
            ->where('sender_id', $sender->id)
            ->where('isAccepted', 1)
            ->exists();

        try {
            $recieverId = $receiver->id;

            $students = Team::whereExists(function ($query) use ($recieverId) {
                $query->select('id')
                    ->from('students')
                    ->whereRaw("JSON_CONTAINS(team_member, '$recieverId')");
            })->get()->first();

            $students_ids = json_decode($students->team_member);

            if (count($students_ids) > 0) {
                $checkIfRecieverAreAlredyInTeam = true;
            }
        } catch (ErrorException $th) {
            $checkIfRecieverAreAlredyInTeam = false;
        }


        $checkIfInvitationIsAlredyExist = \DB::table('invitations')
            ->where('sender_id', $sender->id)
            ->where('receiver_id', $receiver->id)
            ->where('isAccepted', 0)
            ->exists();

        if ($checkIfInvitationIsAlredyExist || $checkIfSenderAreAlredyInTeam || $checkIfRecieverAreAlredyInTeam) {
            // !! devloping response test
            return response([$checkIfInvitationIsAlredyExist, $checkIfSenderAreAlredyInTeam, $checkIfRecieverAreAlredyInTeam], 403);
        }

        Invitation::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'isAccepted' => 0,
        ]);

        return response('seccess', 200);


    }
    public function getRecievedInvitation(Request $request)
    {
        $student = $request->user('student');
        $invitation = Invitation::
            select('tel', 'email', 'name', 'code', 'invitations.created_at', 'isAccepted')
            ->join('students', 'students.id', '=', 'invitations.sender_id')
            ->where('receiver_id', $student->id)
            ->where('isAccepted', 0 )
            ->orWhere('isAccepted', 1)
            ->where('code','!=', $student->code )
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

            $invitation->update(["isAccepted" => 1]);

            Invitation::create([
                'sender_id' => $receiver_id,
                'receiver_id' => $sender_id,
                'isAccepted' => 1,
            ]);

            // * create team
            $team_members = array($receiver_id, $sender_id);
            $team_members_json = json_encode($team_members);

            Team::create([
                'team_member' => $team_members_json,
                'id_supervisor' => null,
            ]);

            return response('accepted', 201);
            // !----------------------------------------------------------------------------------------------------------------------------
// * check if reciver refuse  the invitation

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


            $invitation_symitrique_exist = \DB::table('invitations')
                ->where('sender_id', $receiver_id)
                ->where('receiver_id', $sender_id)
                ->where('isAccepted', 0)
                ->exists();


            if ($invitation_symitrique_exist) {
                $invitation_symitrique = Invitation::get()
                    ->where('sender_id', $receiver_id)
                    ->where('receiver_id', $sender_id)
                    ->where('isAccepted', 0)
                    ->first();
                $invitation_symitrique->update(["isAccepted" => -1]);
                // return response('invitation cancled', 201);
            }


            return response('invitation cancled', 201);



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
        try {

            $students = Team::whereExists(function ($query) use ($studentId) {
                $query->select('id')
                    ->from('students')
                    ->whereRaw("JSON_CONTAINS(team_member, '$studentId')");
            })->get()->first();

            $students_ids = json_decode($students->team_member);

            $team_members = array();

            foreach ($students_ids as $student_id) {
                $team_members[] = Student::get()->where('id', $student_id)->first();
            }


            //**  fetch the supervisor information *

            $supervisor_info = Teacher::select('name', 'institutional_email', 'personal_email', 'tel')->where('id', $students->id_supervisor)->get()->first();

            $response = ['supervsorInfo' => $supervisor_info, 'team_members' => $team_members];



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
