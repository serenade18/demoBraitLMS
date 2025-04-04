<?php

// routes/api.php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'development'], function () {
    // Your API-specific routes here
    Route::get('/test', function () {
        return 'api test';
    });

    // Include other API routes here
    Route::middleware('api')->group(base_path('routes/api/auth.php'));
    Route::namespace('Web')->group(base_path('routes/api/guest.php'));
    Route::prefix('panel')->middleware('api.auth')->namespace('Panel')->group(base_path('routes/api/user.php'));
    Route::group(['namespace' => 'Config'], function () {
        Route::get('/config', ['uses' => 'ConfigController@list']);
        Route::get('/config/register/{type}', ['uses' => 'ConfigController@getRegisterConfig']);
    });
    Route::prefix('instructor')->middleware(['api.auth', 'api.level-access:teacher'])->namespace('Instructor')->group(base_path('routes/api/instructor.php'));
});