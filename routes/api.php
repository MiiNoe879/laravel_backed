<?php

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
    $api->group(['prefix' => 'user'], function(Router $api) {
        $api->post('signup', 'App\\Api\\V1\\Controllers\\OTPController@signUp');
        $api->post('login', 'App\\Api\\V1\\Controllers\\LoginController@login');
    });

    $api->group(['middleware' => 'checkHeader'], function (Router $api) {
        $api->get('user/get', 'App\\Api\\V1\\Controllers\\UserController@getUserInfo');
        $api->put('user/update', 'App\\Api\\V1\\Controllers\\UserController@updateUserInfo');
        $api->get('user/leaderboard', 'App\\Api\\V1\\Controllers\\UserController@getLeaderBoard');

        $api->post('image', 'App\Api\V1\Controllers\ImageController@store');
        $api->put('image/{id}', 'App\Api\V1\Controllers\ImageController@update');
        $api->delete('image/{id}', 'App\Api\V1\Controllers\ImageController@remove');
    
        $api->post('image/uploadrequest', 'App\Api\V1\Controllers\ImageController@upload_request');
        $api->post('image/store', 'App\Api\V1\Controllers\ImageController@store');
    
        $api->get('image/latest', 'App\Api\V1\Controllers\ImageController@get_latest_images');
        $api->get('image/get_user_images', 'App\Api\V1\Controllers\ImageController@get_user_images');
        $api->get('image/near', 'App\Api\V1\Controllers\ImageController@get_images_near');
        $api->get('image/all', 'App\Api\V1\Controllers\ImageController@getAllImages');
        $api->get('image/{id}', 'App\Api\V1\Controllers\ImageController@getImageInfo');

        $api->post('report', 'App\Api\V1\Controllers\ReportController@store');
        $api->get('report', 'App\Api\V1\Controllers\ReportController@index');
    });

    $api->get('hello', function() {
        return response()->json([
            'message' => 'This is a simple example of item returned by your APIs. Everyone can see it.'
        ]);
    });
});
