<?php

namespace App\Http\Controllers;

use App\Http\Requests\FetchThemeRequest;
use App\Http\Requests\PresidentValidateRequest;
use App\Http\Requests\SpecialtyManagerValidateRequest;
use App\Http\Requests\ThemeSuggestionRequest;
use App\Models\Specialitie;
use App\Models\Student_speciality;
use App\Models\Teacher_specialty_manager;
use App\Models\Team;
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

            $specialty_id = Specialitie::get()->where('fullname', $credentials['specialty_name'])->first()->id;

        } catch (\Throwable $th) {
            return response('specialty not found', 404);
        }


        // try {
        Theme::create(
            [
                'title' => $credentials['themeTitle'],
                'description' => !empty($credentials['themeDesc']) ? $credentials['themeDesc'] : null,
                'teacher_id' => $sender->id,
                'specialty_id' => $specialty_id,
            ]
        );
        // } catch (\Throwable $th) {
        //     return response('send Error', 403);
        // }

        return response('sended successfully', 201);
    }

    public function PresidentValidity(PresidentValidateRequest $request)
    {
        $credentials = $request->validated();
        $sender = $request->user('teacher');
        try {
            if ($credentials['response'] == 1) {
                \DB::update(
                    'update themes set president_validation  = 1  where id = ?',
                    [$credentials['suggestion_id']]
                );
            }

        } catch (\Throwable $th) {
            return response('error response', 403);
        }

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

$teams = Team::all();


foreach ($teams as $team) {

    $teamMemberIds = json_decode($team->team_member);


    $memberSpecialtyIds = Student_speciality::whereIn('student_id', $teamMemberIds)
        ->pluck('speciality_id')
        ->toArray();

    $matchingThemes = Theme::whereIn('specialty_id', $memberSpecialtyIds)
        ->where('specialty_manager_validation', true)
        ->pluck('id')
        ->toArray();

    $team->themes_ids = json_encode($matchingThemes);
    $team->save(); }

        return response('publish successfully', 201);

    }

    public function fetchSuggestedTheme(Request $request)
    {
        $teacher = $request->user('teacher');


        $specialty_id = Teacher_specialty_manager::get()->where('teacher_id', $teacher->id)->first()->specialty_id;


        return $theme_info = Theme::select('themes.id', 'title', 'description', 'specialty_manager_validation', 'teachers.name', 'themes.created_at')->where('specialty_id', $specialty_id)
            ->join('teachers', 'teachers.id', '=', 'themes.teacher_id')
            ->get()
        ;

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

        $team = Team::whereJsonContains('team_member', $student->id)->get('themes_ids')->first();

         $array_of_themes_ids = json_decode($team->themes_ids) ;


         $list_theme = [] ;

         foreach ($array_of_themes_ids as $theme_id ) {


            $list_theme [] = Theme::select('id', 'title')->where('id', $theme_id)->first() ;


         }

         return response(compact('list_theme',200)) ;


    }







}
