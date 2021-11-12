<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use JWTAuth;
use App\Rooms;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoomsController extends Controller
{
    use Helpers;

    public function index()
    {
        $current_user = JWTAuth::parseToken()->authenticate();
        return $current_user->rooms();
    }

    public function store(Request $request)
    {
        $current_user = JWTAuth::parseToken()->authenticate();

        //initial check
        if (($request->get('room')) == NULL ||
            ($request->get('month')) == NULL ||
            ($request->get('day')) == NULL ||
            ($request->get('hour')) == NULL ||
            ($request->get('minute')) == NULL ||
            ($request->get('hour_finish')) == NULL ||
            ($request->get('minute_finish') == NULL))
        {
            return response()->json([
                'error' => 'Missing parameter'
            ], 400);
        }

        $room = new Rooms;

        //checks
        if (RoomsController::check($request) != NULL)
        {
            return RoomsController::check($request);
        }

        //assign values
        $room->room = $request->get('room');
        $room->month = $request->get('month');
        $room->day = $request->get('day');
        $room->hour = $request->get('hour');
        $room->minute = $request->get('minute');
        $room->hour_finish = $request->get('hour_finish');
        $room->minute_finish = $request->get('minute_finish');

        //assign email to meeting
        $room->email = $current_user['email'];

        //overlap check
        $check_return = RoomsController::check_availability($room);
        if ($check_return != NULL)
        {
            return $check_return;
        }

        if ($room->save())
        {
            return response()->json([
                'status' => array('message' => 'meeting booked', 'status_code' => 200, 'room' => $room,
            )], 200);
        } 
        else
        {
            return response()->json([
                'error' => array('message' => 'Could not create meeting', 'status_code' => 500
            )], 500);   
        }
    }

    public function check(Request $request)
    {

        $room = new Rooms;

        $room_start = new Rooms;
        $room_finish = new Rooms;

        //room
        if ($request->get('room') != "")
        {
            if (($request->get('room') == 1) ||
                ($request->get('room') == 2) ||
                ($request->get('room') == 3))
            {
                //$room->room = $request->get('room');
            }
            else
            {
                return response()->json([
                    'error' => 'Room not found'
                ], 400);
            }
        }

        //month
        if (($request->get('month') >= 1) &&
            ($request->get('month') <= 12))
        {
            if (strlen($request->get('month')) != 2)
            {
                return response()->json([
                    'error' => array('message' => 'Invalid month format', 'status_code' => 400
                )], 400); 
            }
            $room_start->month = $request->get('month');
        }
        else
        {
            return response()->json([
                'error' => 'Invalid month'
            ], 400);
        }

        //finish_month
        if ($request->get('month_finish') != "")
        {
            if (($request->get('month_finish') >= 1) &&
                ($request->get('month_finish') <= 12))
            {
                if (strlen($request->get('month_finish')) != 2)
                {
                    return response()->json([
                        'error' => array('message' => 'Invalid finish month format', 'status_code' => 400
                    )], 400); 
                }
                $room_finish->month = $request->get('month_finish');
            }
            else
            {
                return response()->json([
                    'error' => 'Invalid finish month'
                ], 400);
            } 
        }       

        //day
        if (($request->get('day') >= 1) &&
            ($request->get('day') <= 31))
        {
            if (strlen($request->get('day')) != 2)
            {
                return response()->json([
                    'error' => array('message' => 'Invalid day format', 'status_code' => 400
                )], 400); 
            }
            $room_start->day = $request->get('day');
        }
        else
        {
            return response()->json([
                'error' => 'Invalid day'
            ], 400);
        }

        //day_finish
        if (($request->get('day_finish') != ""))
        {
            if (($request->get('day_finish') >= 1) &&
                ($request->get('day_finish') <= 31))
            {
                if (strlen($request->get('day_finish')) != 2)
                {
                    return response()->json([
                        'error' => array('message' => 'Invalid finish day format', 'status_code' => 400
                    )], 400); 
                }
                $room_finish->day = $request->get('day_finish');
            }
            else
            {
                return response()->json([
                    'error' => 'Invalid day'
                ], 400);
            }
        }

        //hour
        if (($request->get('hour') >= 0) &&
            ($request->get('hour') <= 23))
        {
            if (strlen($request->get('hour')) != 2)
            {
                return response()->json([
                    'error' => array('message' => 'Invalid hour format', 'status_code' => 400
                )], 400); 
            }
            $room->hour = $request->get('hour');
            $room_start->hour = $request->get('hour');
        }
        else
        {
            return response()->json([
                'error' => 'Invalid hour'
            ], 400);
        }

        //minute
        if (($request->get('minute') >= 0) &&
            ($request->get('minute') <= 59))
        {
            if (strlen($request->get('minute')) != 2)
            {
                return response()->json([
                    'error' => array('message' => 'Invalid minute format', 'status_code' => 400
                )], 400); 
            }
            $room->minute = $request->get('minute');
            $room_start->minute = $request->get('minute');
        }
        else
        {
            return response()->json([
                'error' => 'Invalid minute'
            ], 400);
        }

        //hour_finish
        if (($request->get('hour_finish') >= 0) &&
            ($request->get('hour_finish') <= 23))
        {
            if (strlen($request->get('hour_finish')) != 2)
            {
                return response()->json([
                    'error' => array('message' => 'Invalid finish hour format', 'status_code' => 400
                )], 400); 
            }
            $room->hour_finish = $request->get('hour_finish');
            $room_finish->hour = $request->get('hour_finish');
        }
        else
        {
            return response()->json([
                'error' => 'Invalid hour_finish'
            ], 400);
        }

        //minute_finish
        if (($request->get('minute_finish') >= 0) &&
            ($request->get('minute_finish') <= 59))
        {
            if (strlen($request->get('minute_finish')) != 2)
            {
                return response()->json([
                    'error' => array('message' => 'Invalid finish minute format', 'status_code' => 400
                )], 400); 
            }
            $room->minute_finish = $request->get('minute_finish');
            $room_finish->minute = $request->get('minute_finish');
        }
        else
        {
            return response()->json([
                'error' => 'Invalid minute_finish'
            ], 400);
        }

        if (($request->get('month_finish') != NULL) &&
            ($request->get('day_finish') != NULL)) 
        {
            $Start = $room_start->month.$room_start->day.$room_start->hour.$room_start->minute;
            $Finish = $room_finish->month.$room_finish->day.$room_finish->hour.$room_finish->minute;
            if (($Finish) < ($Start))
            {
                return response()->json([
                    'error' => array('message' => 'Finish date and/or time is before Start date and/or time', 'status_code' => 400
                )], 400); 
            }

        }

        //hour and minute check
        {
            if ($room->hour == $room->hour_finish)
            {
                if ($room->minute > $room->minute_finish)
                {
                    return response()->json([
                        'error' => 'End time is before start time (minutes)'
                    ], 400);
                }
            }
            else if ($room->hour > $room->hour_finish)
            {
                return response()->json([
                    'error' => 'End time is before start time (hour)'
                ], 400);
            }
        }
    }


    public function check_availability($room)
    {

        $id_array = $room->all('id');
        $length = count($id_array);

        //for moving through ID
        $id_num = 1;

        //i is to maintain the list
        for ($i = 1; $i <= $length; $i++)
        {
            $list = DB::table('rooms')->where('id', '=', $id_num)->get();

            //if ID is empty, go to the next one
            while ($list->isEmpty()) 
            {
                $id_num++;
                $list = DB::table('rooms')->where('id', '=', $id_num)->get();
            }

            if ((intval($room->room) == DB::table('rooms')->where('id', '=', $id_num)->pluck('room')[0]) &&
                (intval($room->month) == DB::table('rooms')->where('id', '=', $id_num)->pluck('month')[0]) &&
                (intval($room->day) == DB::table('rooms')->where('id', '=', $id_num)->pluck('day')[0])
               )
            {

                //Variable initialization
                {
                    $h = intval($room->hour);
                    $H = DB::table('rooms')->where('id', '=', $id_num)->pluck('hour')[0];
                    $m = intval($room->minute);
                    $M = DB::table('rooms')->where('id', '=', $id_num)->pluck('minute')[0];
                    $h_f = intval($room->hour_finish);
                    $H_F = DB::table('rooms')->where('id', '=', $id_num)->pluck('hour_finish')[0];               
                    $m_f = intval($room->minute_finish);
                    $M_F = DB::table('rooms')->where('id', '=', $id_num)->pluck('minute_finish')[0];
                }

                //Start and end time check
                {  
                    
                    if ((($h > $H) && ($h < $H_F)) ||
                        (($h == $H) && ($m <= $M)) ||
                        (($h == $H_F) && ($m < $M_F)))
                    {
                        return response()->json([
                            'error' => 'Start time ' . $h . ':' . $m . ' is set during another meeting'
                        ], 400);                         
                    }

                    if ((($h_f > $H) && ($h_f < $H_F)) ||
                        (($h_f == $H) && ($m_f < $M)) ||
                        (($h_f == $H_F) && ($m <= $M_F)))
                    {
                        return response()->json([
                            'error' => 'End time ' . $h_f . ':' . $m_f . ' is set during another meeting'
                        ], 400);                         
                    }

                }

                //Check to see if previous meeting is during meeting being registered
                {
                    if ((($h < $H) && ($h_f == $H) && ($m_f > $M_F)) ||
                        (($h < $H) && ($h_f > $H)) ||
                        (($h == $H) && ($m < $M) && ($h_f == $H) && ($m_f > $M_F)) ||
                        (($h == $H) && ($m < $M) && ($h_f > $H)))
                    {
                        return response()->json([
                            'error' => 'Meetings overlap'
                        ], 400);                     
                    }
                }
            }
            $id_num++;
        }
    }

    public function check_availability_exclude($room, $id)
    {
        $id_array = $room->all('id');
        $length = count($id_array);

        //for moving through ID
        $id_num = 1;

        //i is to maintain the list
        for ($i = 1; $i <= $length; $i++)
        {
            $list = DB::table('rooms')->where('id', '=', $id_num)->get();

            //if ID is empty, go to the next one
            while ($list->isEmpty()) 
            {
                $id_num++;
                $list = DB::table('rooms')->where('id', '=', $id_num)->get();
            }    

            //check to skip the room that is to be changes
            if ($id_num == $id)
            {
                //check to see if id is final element
                if ($length == $id)
                {
                    return;
                }
                $id_num++;
                $i++;

                //check to see if value is out of bounds
                if ($i > $length)
                {
                    break;
                }
            }

            if ((intval($room->room) == DB::table('rooms')->where('id', '=', $id_num)->pluck('room')[0]) &&
                (intval($room->month) == DB::table('rooms')->where('id', '=', $id_num)->pluck('month')[0]) &&
                (intval($room->day) == DB::table('rooms')->where('id', '=', $id_num)->pluck('day')[0])
               )
            {
                //Variable initialization
                {
                    $h = intval($room->hour);
                    $H = DB::table('rooms')->where('id', '=', $id_num)->pluck('hour')[0];
                    $m = intval($room->minute);
                    $M = DB::table('rooms')->where('id', '=', $id_num)->pluck('minute')[0];
                    $h_f = intval($room->hour_finish);
                    $H_F = DB::table('rooms')->where('id', '=', $id_num)->pluck('hour_finish')[0];               
                    $m_f = intval($room->minute_finish);
                    $M_F = DB::table('rooms')->where('id', '=', $id_num)->pluck('minute_finish')[0];
                }

                //Start and end time check
                {  
                    
                    if ((($h > $H) && ($h < $H_F)) ||
                        (($h == $H) && ($m >= $M)) ||
                        (($h == $H_F) && ($m < $M_F)))
                    {
                        return response()->json([
                            'error' => 'Start time ' . $h . ':' . $m . ' is set during another meeting',
                        ], 400);                         
                    }

                    if ((($h_f > $H) && ($h_f < $H_F)) ||
                        (($h_f == $H) && ($m_f > $M)) ||
                        (($h_f == $H_F) && ($m <= $M_F)))
                    {
                        return response()->json([
                            'error' => 'End time ' . $h_f . ':' . $m_f . ' is set during another meeting'
                        ], 400);                         
                    }

                }
                
                //Check to see if previous meeting is during meeting being registered
                {
                    if ((($h < $H) && ($h_f == $H) && ($m_f > $M_F)) ||
                        (($h < $H) && ($h_f > $H)) ||
                        (($h == $H) && ($m < $M) && ($h_f == $H) && ($m_f > $M_F)) ||
                        (($h == $H) && ($m < $M) && ($h_f > $H)))
                    {
                        return response()->json([
                            'error' => 'Meetings overlap'
                        ], 400);                     
                    }
                }
            }
            $id_num++;
        }
    }

    //retrieve a list of rooms
    public function get_rooms()
    {
        $current_user = JWTAuth::parseToken()->authenticate();

        return response()->json([
            'status' => array('message' => 'Rooms available are rooms: 1, 2, and 3')
        ]);  
    }

    //edit meeting
    public function edit_meeting(Request $request, $id)
    {
        $current_user = JWTAuth::parseToken()->authenticate();

        //check if meeting exists
        if ((DB::table('rooms')->where('id', '=', $id)->get()) == "[]")
        {
            return response()->json([
                'error' => 'Meeting does not exist'
            ], 403);
        }

        //initial check
        if (($request->get('room') == NULL) ||
            ($request->get('month') == NULL) ||
            ($request->get('day') == NULL) ||
            ($request->get('hour') == NULL) ||
            ($request->get('minute') == NULL) ||
            ($request->get('hour_finish') == NULL) ||
            ($request->get('minute_finish') == NULL))
        {
            return response()->json([
                'error' => 'Missing parameter'
            ], 400);
        }

        //parameter check
        $check_return = RoomsController::check($request);
        if ($check_return != NULL)
        {
            return $check_return;
        }
        
        $room = new Rooms;
        
        $room->room = $request->get('room');
        $room->month = $request->get('month');
        $room->day = $request->get('day');
        $room->hour = $request->get('hour');
        $room->minute = $request->get('minute');
        $room->hour_finish = $request->get('hour_finish');
        $room->minute_finish = $request->get('minute_finish');

        //overlap check
        $check_return = RoomsController::check_availability_exclude($room, $id);
        if ($check_return != NULL)
        {
            return $check_return;
        }

        //verify if current user is the owner of this meeting
        if (($current_user->email) == (DB::table('rooms')->where('id', '=', $id)->pluck('email')[0]))
        {
            DB::table('rooms')->where('id', '=', $id)->update(['room' => $request->get('room')]);
            DB::table('rooms')->where('id', '=', $id)->update(['month' => $request->get('month')]);
            DB::table('rooms')->where('id', '=', $id)->update(['day' => $request->get('day')]);
            DB::table('rooms')->where('id', '=', $id)->update(['hour' => $request->get('hour')]);
            DB::table('rooms')->where('id', '=', $id)->update(['minute' => $request->get('minute')]);
            DB::table('rooms')->where('id', '=', $id)->update(['hour_finish' => $request->get('hour_finish')]);
            DB::table('rooms')->where('id', '=', $id)->update(['minute_finish' => $request->get('minute_finish')]);
        }
        else
       {
            return response()->json([
                'error' => array('message' => 'You do not have permission to change this meeting', 'status_code' => 403
            )], 403); 
        }      

        return response()->json([
            'status' => array('message' => 'meeting edited', 'status_code' => 200
        )], 200);
    }

    //delete meeting
    public function delete_meeting(Request $request, $id)
    {
        $current_user = JWTAuth::parseToken()->authenticate();

        if ((DB::table('rooms')->where('id', '=', $id)->get()) == "[]")
        {
            return response()->json([
                'error' => 'Meeting does not exist'
            ], 403);
        }

        if (($current_user->email) == (DB::table('rooms')->where('id', '=', $id)->pluck('email')[0]))
        {
            DB::table('rooms')->where('id', '=', $id)->delete();
        }
        else
        {
            return response()->json([
                'error' => 'You do not have permission to change this meeting'
            ], 403);
        }

        return response()->json([
            'status' => array('message' => 'meeting deleted', 'status_code' => 200
        )], 200);

    }


    public function get_meetings()
    {
        $current_user = JWTAuth::parseToken()->authenticate();

        $DB_List = DB::table('rooms')->get();

        return response()->json([
            'Meetings' => $DB_List
        ]);
    }

    //retrieve a list of meetings in a given room, filtered by a date range
    public function get_meetings_in_room(Request $request)
    {
        $current_user = JWTAuth::parseToken()->authenticate();

        if (($request->get('room') == NULL) ||
            ($request->get('month') == NULL) ||
            ($request->get('day') == NULL) ||
            ($request->get('hour') == NULL) ||
            ($request->get('minute') == NULL) ||
            ($request->get('month_finish') == NULL) ||
            ($request->get('day_finish') == NULL) ||
            ($request->get('hour_finish') == NULL) ||
            ($request->get('minute_finish') == NULL))
        {
            return response()->json([
                    'error' => array('message' => 'Missing parameter', 'status_code' => 400
                )], 400);             
        }

        //parameter check
        $check_return = RoomsController::check($request);
        if ($check_return != NULL)
        {
            return $check_return;
        }

        $DB_List = DB::table('rooms')->where('room', '=', $request->get('room'))
                                        ->where('month', '>=', $request->get('month'))
                                        ->where('month', '<=', $request->get('month_finish'))
                                        ->where('day', '>=', $request->get('day'))
                                        ->where('day', '<=', $request->get('day_finish'))
                                        ->where('hour', '>=', $request->get('hour'))
                                        ->where('hour_finish', '<=', $request->get('hour_finish'))
                                        ->where('minute', '>=', $request->get('minute'))
                                        ->where('minute_finish', '<=', $request->get('minute_finish'))->get();

        return response()->json([
            'meetings' => $DB_List
        ]);
    }

    //retrieve a list of all meetings for a given user, filtered by a date range
    public function get_meetings_of_user(Request $request)
    {
        $current_user = JWTAuth::parseToken()->authenticate();

        if (($request->get('email') == NULL) ||
            ($request->get('month') == NULL) ||
            ($request->get('day') == NULL) ||
            ($request->get('hour') == NULL) ||
            ($request->get('minute') == NULL) ||
            ($request->get('month_finish') == NULL) ||
            ($request->get('day_finish') == NULL) ||
            ($request->get('hour_finish') == NULL) ||
            ($request->get('minute_finish') == NULL))
        {
            return response()->json([
                    'error' => array('message' => 'Missing parameter', 'status_code' => 400
                )], 400);             
        }

        //parameter check
        $check_return = RoomsController::check($request);
        if ($check_return != NULL)
        {
            return $check_return;
        }

        $DB_List = DB::table('rooms')->where('email', '=', $request->get('email'))
                                        ->where('month', '>=', $request->get('month'))
                                        ->where('month', '<=', $request->get('month_finish'))
                                        ->where('day', '>=', $request->get('day'))
                                        ->where('day', '<=', $request->get('day_finish'))
                                        ->where('hour', '>=', $request->get('hour'))
                                        ->where('hour_finish', '<=', $request->get('hour_finish'))
                                        ->where('minute', '>=', $request->get('minute'))
                                        ->where('minute_finish', '<=', $request->get('minute_finish'))->get();

        $Response = 'meetings for user: '.$request->email; 

        return response()->json([
            $Response => $DB_List
        ]);
    }
}