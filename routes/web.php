<?php
use App\Http\Controllers\Admin\TestCenterController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\FileImportController;
use App\Http\Controllers\Admin\ArrangeSeatController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StudentRecheckController;
use App\Http\Controllers\Admin\StudentUpdateController;
use App\Http\Controllers\Admin\ReportHeaderController;
use App\User;
use Laravel\Socialite\Facades\Socialite;

Route::redirect('/', '/login');

// Authentication Routes...
// Route::get('login', 'Auth\LoginController@showLoginForm')->name('auth.login');
// Route::post('login', 'Auth\LoginController@login')->name('auth.login');
// Route::post('logout', 'Auth\LoginController@logout')->name('auth.logout');

Route::redirect('/home', '/admin');

Auth::routes(['register' => false]);

Route::get('/auth/redirect', function () {
    return Socialite::driver('azure')->redirect();
});

Route::get('/auth/callback', function () {
    // azure sociallite driver
    // $azureUser = Socialite::driver('azure')->user();
    $azureUser = Socialite::driver('azure')
    ->stateless()
    ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
    ->user();

    // dd($azureUser);
    // $businessPhones = $azureUser->user['businessPhones'];
    $displayName = $azureUser->user['displayName'];
    $givenName = $azureUser->user['givenName'];
    // $jobTitle = $azureUser->user['jobTitle'];
    $mail = $azureUser->user['mail'];
    // $mobilePhone = $azureUser->user['mobilePhone'];
    $officeLocation = $azureUser->user['officeLocation'];
    // $preferredLanguage = $azureUser->user['preferredLanguage'];
    // $surname = $azureUser->user['surname'];
    // $userPrincipalName = $azureUser->user['userPrincipalName'];
    // $id = $azureUser->user['id'];
    // $email = $azureUser->attributes['email'];
    $explodeMail = explode('@', $mail);
    // dd($explodeMail[0]);
    $user = User::updateOrCreate([
        'email' => $mail,
    ], [
        'name' => $displayName,
        'email' => $mail,
        'username' => $explodeMail[0],
        'department_name' => $officeLocation
    ]);

    Auth::login($user);
    return redirect('/admin');
});

Route::get('/logout-azure', function () {

    // Logout Laravel session
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    // Logout Microsoft
    return redirect("https://login.microsoftonline.com/common/oauth2/v2.0/logout?post_logout_redirect_uri=" . urlencode(config('app.url')));

});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
    Route::get('/', 'HomeController@index')->name('home');

    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

    Route::resource('test_center', TestCenterController::class);
    Route::post('save/', 'TestCenterController@save')->name('save');

    Route::group(['prefix' => 'test_center', 'as' => 'test_center.'], function(){
        Route::controller(TestCenterController::class)->group(function () {
            Route::post('save/', 'save')->name('save');
            Route::post('resetTestCenter', 'resetTestCenter')->name('resetTestCenter');
            Route::post('bulk-delete','bulkDelete')->name('bulkDelete');
            Route::post('exportFile', action: 'exportFile')->name('exportFile');
        });
    });

    Route::group(['prefix' => 'report_header', 'as' => 'report_header.'], function(){
        Route::controller(ReportHeaderController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::post('save/', 'save')->name('save');
        });
    });

    // Student Recheck Routes
    Route::group(['prefix' => 'student_update', 'as' => 'student_update.'], function () {
        Route::controller(StudentUpdateController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::delete('/{id}', 'destroy')->name('destroy');

            // Routes สำหรับการค้นหา
            Route::post('/search-student', 'searchStudent')->name('searchStudent');
            Route::post('/get-student', 'getStudent')->name('getStudent');

            // Routes สำหรับการเช็คชื่อ
            Route::post('/update-attendance', 'updateAttendance')->name('updateAttendance');
            Route::post('/bulk-update-attendance', 'bulkUpdateAttendance')->name('bulkUpdateAttendance');

            // Routes สำหรับการดาวน์โหลด
            Route::post('exportFile', action: 'exportFile')->name('exportFile');
        });
    });

    // Student Recheck Routes
    Route::group(['prefix' => 'student_recheck', 'as' => 'student_recheck.'], function () {
        Route::controller(StudentRecheckController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::delete('/{id}', 'destroy')->name('destroy');

            // Routes สำหรับการค้นหา
            Route::post('/search-student', 'searchStudent')->name('searchStudent');
            Route::post('/get-student', 'getStudent')->name('getStudent');

            // Routes สำหรับการเช็คชื่อ
            Route::post('/update-attendance', 'updateAttendance')->name('updateAttendance');
            Route::post('/bulk-update-attendance', 'bulkUpdateAttendance')->name('bulkUpdateAttendance');

            // Routes สำหรับการดาวน์โหลด
            Route::post('exportFile', action: 'exportFile')->name('exportFile');
        });
    });

    Route::group(['prefix' => 'file_import', 'as' => 'file_import.'], function(){
        Route::controller(FileImportController::class)->group(function () {
            Route::get('', 'index')->name('index');
            Route::get('list', 'list')->name('list');
            Route::post('save/{id?}', 'save')->name('save');
            Route::get('create', 'create')->name('create');
            Route::get('edit/{id?}', 'edit')->name('edit');
            Route::get('view/{id?}', 'view')->name('view');
            Route::post('update/{id?}', 'update')->name('update');
            Route::post('resetStudentImport/', 'resetStudentImport')->name('resetStudentImport');
        });
    });

    Route::group(['prefix' => 'arrange_seat', 'as' => 'arrange_seat.'], function(){
        Route::controller(ArrangeSeatController::class)->group(function () {
            Route::get('', 'index')->name('index');
            Route::post('save/{id?}', 'save')->name('save');
            Route::get('create', 'create')->name('create');
            Route::get('edit/{id?}', 'edit')->name('edit');
            Route::get('view', 'view')->name('view');
            Route::post('update/{id?}', 'update')->name('update');
            Route::post('assignSeats/', 'assignSeats')->name('assignSeats');
            Route::post('search/', 'search')->name('search');
            Route::post('exportFile/', action: 'exportFile')->name('exportFile');
            Route::post('resetAssignSeats', 'resetAssignSeats')->name('resetAssignSeats');
            Route::post('searchStudent', 'searchStudent')->name('searchStudent');
            Route::post('getStudent', 'getStudent')->name('getStudent');
        });
    });

    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function(){
        Route::controller(ReportController::class)->group(function () {
            Route::get('', 'index')->name('index');
            Route::get('view', 'view')->name('view');
            Route::post('pdfPrint', 'pdfPrint')->name('pdfPrint');
            Route::get('pdfFile', 'pdfFile')->name('pdfFile');
        });
    });
});
