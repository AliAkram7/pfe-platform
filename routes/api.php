<?php

use App\Http\Controllers\AuthStudentsController;
use App\Http\Controllers\AuthTeacherController;
use App\Http\Controllers\authUser;
use App\Http\Controllers\DepartmentManagerController;
use App\Http\Controllers\RankConroller;
use App\Http\Controllers\SESSEION\sessionStudentController;
use App\Http\Controllers\SESSEION\sessionTeacherController;
use App\Http\Controllers\SESSEION\TeamMessages;
use App\Http\Controllers\SESSEION\TeamsController;


use App\Http\Controllers\SpecialtyManagerContoller;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
############################# public route USED // !! BY ERVRY ONE ##############################

Route::post('/registre', [AuthStudentsController::class, 'registre']);
Route::post('/student/login', [AuthStudentsController::class, 'login']);

####################################################################################

########################### student route // !! ONLY AUTHORIZED STUDENT ##############################
Route::group(['middleware' => 'student.guard:student'], function () {
    Route::get('/student/info', [sessionStudentController::class, 'getStudentInfo']);
    Route::post('/studentLogout', [AuthStudentsController::class, 'logout']);
    Route::post('/student/update/info', [sessionStudentController::class, 'updateStudentInfo']);
    Route::get('/getRanking', [sessionStudentController::class, 'getRanking']);
    Route::post('/invitePartner', [sessionStudentController::class, 'invitePartner']);
    Route::get('/getRecievedInvitation', [sessionStudentController::class, 'getRecievedInvitation']);
    Route::get('/getSendedInvitation', [sessionStudentController::class, 'getSendedInvitation']);
    Route::get('/getStudentTeamInformation', [sessionStudentController::class, 'getStudentTeamInformation']);
    Route::post('/studentResponseToInvitation', [sessionStudentController::class, 'studentResponseToInvitation']);
    Route::post('/getRooms', [TeamsController::class, 'getRooms']);
    Route::post('/createRoom', [TeamsController::class, 'createRoom']);
    Route::post('/studentsendMessage', [TeamMessages::class, 'studentsendMessage']);
    Route::get('/student/getMessages/{id_room}', [TeamMessages::class, 'getMessages']);
    Route::post('/student/refreshToken', [sessionStudentController::class, 'refreshToken']);
    Route::post('/student/checkEmailVerification', [sessionStudentController::class, 'checkEmailVerification']);

    Route::get('/student/fetchThemePublished', [ThemeController::class, 'fetchThemePublished']);

    // updateListOfThemeChooses
    Route::post('/student/updateListOfThemeChooses', [TeamsController::class, 'updateListOfThemeChooses']);



});





######################### teacher public route #####################################

Route::post('/teacher/login', [AuthTeacherController::class, 'login']);

######################### teacher public route #####################################


Route::group(['middleware' => ['teacher.guard:teacher']], function () {

    Route::get('/teacher/info', [sessionTeacherController::class, 'getTeacherInfo']);
    Route::post('/teacher/update/info', [sessionTeacherController::class, 'teacherUpdateInfo']);

    Route::get('/teacher/getTeams', [TeamsController::class, 'getListOfTeams']);

    Route::get('/teacher/getRoomsByTeam/{id}', [TeamsController::class, 'getRoomsByTeam']);

    Route::get('/teacher/getMessages/{id_room}', [TeamMessages::class, 'getMessages']);

    Route::post('/teacher/sendMessage', [TeamMessages::class, 'teacherSendMessage']);

    Route::post('/teacher/refreshToken', [sessionTeacherController::class, 'refreshToken']);

    Route::get('/teacher/department_manager/get_department_info', [DepartmentManagerController::class, 'getDepartmentInfo']);

    Route::post('/teacher/department_manager/upload', [DepartmentManagerController::class, 'upload']);

    Route::get('/teacher/department_manager/fetchStudentsData/{id}', [DepartmentManagerController::class, 'fetchStudentsData']);

    Route::post('/teacher/department_manager/addStudent', [DepartmentManagerController::class, 'addStudent']);

    Route::post('/teacher/department_manager/lockAccount', [DepartmentManagerController::class, 'lockAccount']);

    Route::post('/teacher/department_manager/unLockAccount', [DepartmentManagerController::class, 'unLockAccount']);

    Route::post('/teacher/department_manager/deleteAccount', [DepartmentManagerController::class, 'deleteAccount']);

    Route::post('/teacher/department_manager/updateAccount', [DepartmentManagerController::class, 'updateAccount']);

    Route::post('/teacher/sendSuggestionTheme', [ThemeController::class, 'sendSuggestionTheme']);

    Route::post('/teacher/PresidentValidity', [ThemeControllerr::class, 'PresidentValidity']);

    Route::post('/teacher/specialty_manager/SpecialtyManagerValidity', [ThemeController::class, 'SpecialtyManagerValidity']);

    Route::get('/teacher/specialty_manager/fetchSpecialtyInfo', [SpecialtyManagerContoller::class, 'fetchSpecialtyInfo']);

    Route::get('/teacher/specialty_manager/fetchSuggestedTheme', [ThemeController::class, 'fetchSuggestedTheme']);

    Route::post('/teacher/specialty_manager/publishTheListOfThemes', [ThemeController::class, 'publishTheListOfThemes']);

    Route::get('/teacher/specialty_manager/getRanking', [SpecialtyManagerContoller::class, 'getRanking']);

    Route::get('/teacher/specialty_manager/getStudentWithoutRank', [RankConroller::class, 'getStudentWithoutRank']);

    Route::post('/teacher/specialty_manager/addRankByStudent', [RankConroller::class, 'addRankByStudent']);

    Route::post('/teacher/specialty_manager/uploadRanks', [RankConroller::class, 'uploadRanks']);

    Route::post('/teacher/specialty_manager/deleteRank', [RankConroller::class, 'deleteRank']);

    Route::post('/teacher/specialty_manager/updateRank', [RankConroller::class, 'updateRank']);

});

// Route::middleware('auth.gaurd:student')->group(function () {
//     Route::post('/Userlogout', [authUser::class, 'logout']);
//         Route::post('/student/logout', [AuthStudentsController::class, 'logout']);
// });

####################################################################

Route::middleware('auth:user')->group(function () {
    // Route::post('/Userlogout', [authUser::class, 'logout']);
});




Route::post('/userRegistre', [authUser::class, 'registre']);
