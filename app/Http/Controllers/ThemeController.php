<?php

namespace App\Http\Controllers;

use App\Http\Requests\FetchThemeRequest;
use App\Http\Requests\PresidentValidateRequest;
use App\Http\Requests\SpecialtyManagerValidateRequest;
use App\Http\Requests\ThemeSuggestionRequest;
use App\Models\Affectation_method;
use App\Models\Framer;
use App\Models\Specialitie;
use App\Models\Students_Account_Seeder;
use App\Models\Student_speciality;
use App\Models\Teacher;
use App\Models\Teacher_specialty_manager;
use App\Models\Team;
use App\Models\TeamRoom;
use App\Models\Theme;
use Dotenv\Dotenv;
use Illuminate\Http\Request;
use Dotenv\Loader;


/**
 * Summary of ThemeContoller
 */
class ThemeController extends Controller
{

    public function sendSuggestionTheme(ThemeSuggestionRequest $request)
    {
        $credentials = $request->validated();
        $sender = $request->user('teacher');

        // ! get specialty information
        try {

            $specialty_id = Specialitie::get()->where('abbreviated_name', $credentials['specialty'])->first()->id;

        } catch (\Throwable $th) {
            return response('specialty not found', 404);
        }


        // try {
        Theme::create(
            [
                'title' => $credentials['title'],
                'description' => $credentials['description'],
                'research_domain' => $credentials['searchDomain'],
                'objectives_of_the_project' => $credentials['objectives'],
                'key_word' => json_encode($credentials['keyWords']),
                'work_plan' => json_encode($credentials['workPlan']),
                'teacher_id' => $sender->id,
                'specialty_id' => $specialty_id,
            ]
        );
        // } catch (\Throwable $th) {
        //     return response('send Error', 403);
        // }

        return response('sended successfully', 201);
    }


    public function SpecialtyManagerValidity(SpecialtyManagerValidateRequest $request)
    {

        $credentials = $request->validated();
        $sender = $request->user('teacher');
        try {
            if ($credentials['response'] == 1) {
                \DB::update(
                    'update themes set  specialty_manager_validation = 1  where id = ?',
                    [$credentials['suggestion_id']]
                );
            } else {
                \DB::update(
                    'update themes set  specialty_manager_validation = 0  where id = ?',
                    [$credentials['suggestion_id']]
                );
            }
        } catch (\Throwable $th) {
            return response('error response', 403);
        }
    }


    public function publishTheListOfThemes(Request $request)
    {

        $teacher = $request->user('teacher');
        $specialty_id = Teacher_specialty_manager::get()->where('teacher_id', $teacher->id)->first()->specialty_id;

        // !! don't delete me
        // $path = base_path('.env');
        // if (file_exists($path)) {
        //     $fp = fopen($path, 'a');
        //     fwrite($fp, "SPECIALTY_" . $specialty_id . "_PUBLISH_THEME=true");
        //     fclose($fp);
        // }

        $teams = Team::select('teams.id')
            ->join('student_specialities', 'student_specialities.student_id', '=', 'teams.member_1')
            ->where('speciality_id', $specialty_id)
            ->orderBy('teams.id')
            ->get();

        foreach ($teams as $team) {


            $matchingThemes = Theme::where('specialty_id', $specialty_id)
                ->where('specialty_manager_validation', true)
                ->pluck('id')
                ->toArray();

            $team->choice_list = json_encode($matchingThemes);
            $team->save();
        }

        return response('publish successfully', 201);

    }

    public function fetchSuggestedTheme(Request $request)
    {
        $teacher = $request->user('teacher');


        $specialty_id = Teacher_specialty_manager::get()->where('teacher_id', $teacher->id)->first()->specialty_id;

        $themes_info = Theme::select(
            'themes.id',
            'title',
            'description',
            'objectives_of_the_project',
            'key_word',
            'work_plan',
            'research_domain',
            'specialty_manager_validation',
            'teachers.name',
            'themes.created_at'
        )->where('specialty_id', $specialty_id)
            ->join('teachers', 'teachers.id', '=', 'themes.teacher_id')
            ->get();


        $response = [];

        foreach ($themes_info as $theme_info) {

            $theme_info->id;
            $theme_response = [
                'id' => $theme_info->id,
                'title' => $theme_info->title,
                'description' => $theme_info->description,
                'objectives_of_the_project' => $theme_info->objectives_of_the_project,
                'key_word' => json_decode($theme_info->key_word),
                'work_plan' => json_decode($theme_info->work_plan),
                'research_domain' => $theme_info->research_domain,
                'specialty_manager_validation' => $theme_info->specialty_manager_validation,
                'name' => $theme_info->name,
                'created_at' => $theme_info->created_at,
            ];

            $response[] = $theme_response;

        }


        return $response;

        // $theme_response = [
        //     'themes.id' => $theme_info['id'],
        //     'title' => $theme_info['title'],
        //     'description' => $theme_info['description'],
        //     'objectives_of_the_project' => $theme_info['objectives_of_the_project'],
        //     'key_word' => json_decode($theme_info['key_word']),
        //     'work_plan' => json_decode($theme_info['work_plan']),
        //     'research_domain' => $theme_info['research_domain'],
        //     'specialty_manager_validation' => $theme_info['specialty_manager_validation'],
        //     'name' => $theme_info['name'],
        //     'created_at' => $theme_info['created_at']
        // ];

        // return response($theme_response, 200);


    }




    // public function affectation(affectationRequest $request)
// {
// }
// public function displayResultOfAffectation(Request $request)
// {
// }

    public function fetchThemePublished(FetchThemeRequest $request)
    {

        $student = $request->user('student');

        $specialty_id = Student_speciality::select('*')->where('student_id', $student->id)->get()->first()->speciality_id;

        $team = Team::where('member_1', $student->id)->orWhere('member_2', $student->id)->get('choice_list')->first();

        $array_of_themes_ids = json_decode($team->choice_list);

        $list_theme = [];

        $method_of_aff = Affectation_method::select('method')->where('specialty_id', $specialty_id)->get()->first()->method;
        if ($method_of_aff == 2) {
            foreach ($array_of_themes_ids as $theme_id) {
                $list_theme[] = Teacher::select('id', 'name as title')->where('id', $theme_id)->first();
            }
        } else {
            foreach ($array_of_themes_ids as $theme_id) {
                $list_theme[] = Theme::select('id', 'title')->where('id', $theme_id)->first();
            }
        }

        return response(compact('list_theme', 200));


    }

    //!! affectation of themes




    public function affectThemeToStudents(Request $request)
    {

        // !! dependencies
        // !! specialty_id

        $teacher = $request->user('teacher');
        $specialty_id = Teacher_specialty_manager::select()->where('teacher_id', $teacher->id)->get()->first()->specialty_id;

        // !! students array of ids sorted using rank

        $sorted_list_of_students = Students_Account_Seeder::select('students_account_seeders.id', 'mgc')
            ->join('student_specialities', 'student_specialities.student_id', '=', 'students_account_seeders.id')
            ->where('student_specialities.speciality_id', $specialty_id)
            ->join('ranks', 'ranks.student_specialite_id', 'student_specialities.id')
            ->orderBy('mgc', 'desc')
            ->get()->toArray();

        // return $sorted_list_of_students ;

        $sorted_list_of_students_array = array_map(function ($obj) {
            return $obj["id"];
        }, $sorted_list_of_students);


        // !! themes array of available theme
        $list_theme = Theme::select('id')->where('specialty_id', $specialty_id)->where('specialty_manager_validation', 1)->get()->toArray();

        $list_theme_array = array_map(function ($obj) {
            return $obj["id"];
        }, $list_theme);


        $student_black_list = [];
        $theme_black_list = [];

        foreach ($sorted_list_of_students_array as $student) {

            // !! get List choice of student

            // return $student /;

            $team_info = Team::select('id', 'member_1', 'member_2', 'choice_list')
                ->where('member_1', $student)
                ->orWhere('member_2', $student)->get()->first();

            if (in_array($student, $student_black_list)) {
                echo "student $student  in team  $team_info->id  blacklisted\n";
                continue;
            }





            $choice_list = [];
            if ($team_info != null) {
                $choice_list = json_decode($team_info->choice_list);
            }


            if (count($choice_list) > 0) {


                foreach ($choice_list as $theme) {





                    if (in_array($theme, $theme_black_list)) {
                        echo "theme $theme blacklisted \n ";
                        continue;
                    }

                    if (in_array($theme, $list_theme_array)) {

                        if ($theme != null) {

                            if (\DB::update('update teams set  theme_id = ?   where id = ?', [$theme, $team_info->id])) {

                                // !! black listed the theme and member_2 and member_1
                                $framer_id = Theme::select('teacher_id')->where('id', $theme)->get()->first()->teacher_id;
                                \DB::update('update teams set  supervisor_id  = ?   where id = ?', [$framer_id, $team_info->id]);

                                // * get theme information and supervisor

                                $theme_info = Theme::find($theme);
                                $framer_info = Teacher::find($framer_id);



                                TeamRoom::create([
                                    'team_id' => $team_info->id,
                                    'creater_id' => 4,
                                    'room_name' => "affectation result",
                                    'discription' => $theme_info['title'] . " by " . $framer_info['name'] .
                                    ", This theme delves into : " .
                                    $theme_info['description'] . "."
                                ]);





                                echo "$team_info->id inserted $theme\n  ";

                                if ($team_info->member_1 != null) {
                                    $student_black_list[] = $team_info->member_1;
                                }
                                if ($team_info->member_2 != null) {
                                    $student_black_list[] = $team_info->member_2;
                                }
                                $theme_black_list[] = $theme;
                                break;
                            }
                        }

                    }


                }
            }



        }


    }




}
