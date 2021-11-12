<?php

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
    $api->group(['prefix' => 'auth'], function(Router $api) {
        $api->post('signup', 'App\\Api\\V1\\Controllers\\SignUpController@signUp');
        $api->post('login', 'App\\Api\\V1\\Controllers\\LoginController@login');

            $api->get('hello', function() {
        return response()->json([
            'BOOOP'
        ]);
    });

        //$api->post('recovery', 'App\\Api\\V1\\Controllers\\ForgotPasswordController@sendResetEmail');
        //$api->post('reset', 'App\\Api\\V1\\Controllers\\ResetPasswordController@resetPassword');
    });

    $api->group(['middleware' => 'jwt.auth'], function(Router $api) {
        $api->get('protected', function() {
            return response()->json([
                'message' => 'Access to protected resources granted!'
            ]);
        });
        /*
        $api->get('refresh', [
            'middleware' => 'jwt.refresh',
            function() {
                return response()->json([
                    'message' => 'You can refresh your access token at each request. Check out this response headers!'
                ]);
            }
        ]);
        */
    });

    $api->group(['middleware' => 'api.auth'], function($api) {
        //save meeting
        $api->post('meeting/store', 'App\\Api\\V1\\Controllers\\RoomsController@store');
        //edit meeting
        $api->put('meeting/edit/{id}', 'App\\Api\\V1\\Controllers\\RoomsController@edit_meeting');
        //delete room
        $api->delete('meeting/delete/{id}', 'App\\Api\\V1\\Controllers\\RoomsController@delete_meeting');
        //get rooms
        $api->get('meeting/retrieve/rooms', 'App\\Api\\V1\\Controllers\\RoomsController@get_rooms');
        //get meetings
        $api->get('meeting/retrieve/meetings', 'App\\Api\\V1\\Controllers\\RoomsController@get_meetings');
        //get meeting based on room with date range
        $api->post('meeting/retrieve/meeting/room', 'App\\Api\\V1\\Controllers\\RoomsController@get_meetings_in_room');
        //get meetings based on user with date range
        $api->post('meeting/retrieve/meeting/user', 'App\\Api\\V1\\Controllers\\RoomsController@get_meetings_of_user');
    
        //invite
        $api->post('invite', 'App\\Api\\V1\\Controllers\\InviteController@invite');
        //pending invites
        $api->get('invite/pending', 'App\\Api\\V1\\Controllers\\InviteController@pending');
        //accepted
        $api->get('invite/accepted', 'App\\Api\\V1\\Controllers\\InviteController@accepted');
        //rejected
        $api->get('invite/rejected', 'App\\Api\\V1\\Controllers\\InviteController@rejected');
        //respond
        $api->post('invite/response', 'App\\Api\\V1\\Controllers\\InviteController@response');
    
    });

    $api->get('hello', function() {
        return response()->json([
            'BOOOP'
        ]);
    });
});
