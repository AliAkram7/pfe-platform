<?php

namespace App\Http\Controllers;

use App\Http\Requests\PresidentValidateRequest;
use App\Http\Requests\SpecialtyManagerValidateRequest;
use App\Http\Requests\ThemeSuggestionRequest;
use App\Models\Specialitie;
use App\Models\Theme;
use Illuminate\Http\Request;

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
            }
        } catch (\Throwable $th) {
            return response('error response', 403);
        }
    }


    // public function affectation(affectationRequest $request)
    // {
    // }
    // public function displayResultOfAffectation(Request $request)
    // {
    // }






}
