<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use JWTAuth;
use App\Invite;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class InviteController extends Controller
{
    use Helpers;

    public function invite(Request $request)
    {
        $current_user = JWTAuth::parseToken()->authenticate();

        //initial check
        if (($request->get('meeting_id')) == NULL ||
            ($request->get('invitee_email')) == NULL)
        {
            return response()->json([
                'error' => array('message' => 'Missing parameter', 'status_code' => 400
            )], 400);  
        }

        //check if meeting exists
        if ((DB::table('rooms')->where('id', '=', $request->get('meeting_id'))->get()) == "[]")
        {
            return response()->json([
                'error' => array('message' => 'Meeting does not exist', 'status_code' => 403
            )], 403);  
        }
        
        //check to see if user is owner of meeting
        $User = DB::table('rooms')->where('id', '=', $request->get('meeting_id'))->pluck('email')[0];

        if ($User != $current_user->email)
        {
            return response()->json([
                'error' => array('message' => 'You do not have permission over this meeting', 'status_code' => 403
            )], 403);  
        }

        //check if user exists
        $Invitee = DB::table('users')->where('email', '=', $request->get('invitee_email'))->get();

        if ((DB::table('users')->where('email', '=', $request->get('invitee_email'))->get()) == "[]")
        {
            return response()->json([
                'error' => array('message' => 'Invitee does not exist', 'status_code' => 403
            )], 403); 
        }

        //check to see if invite exists
        $Invite = DB::table('invites')->where('meeting_id', '=', $request->get('meeting_id'))
                                ->where('invitee_email', '=', $request->get('invitee_email'))->get();

        if ($Invite != "[]")
        {
            return response()->json([
                'error' => array('message' => 'Invite already sent', 'status_code' => 403
            )], 403); 
        }

        $invite = new Invite;

        $invite->meeting_id = $request->get('meeting_id');
        $invite->creator_email = $current_user->email;
        $invite->invitee_email = $request->get('invitee_email');
        $invite->status = "pending";

        if ($invite->save())
        {
            return response()->json([
                'status' => array('message' => 'okay', 'status_code' => 200, 'invite' => $invite,
            )], 200);
        } 
        else
        {
            return response()->json([
                'error' => array('message' => 'Could not invite user', 'status_code' => 500
            )], 500);   
        }
    }

    public function pending(Request $request)
    {
        $current_user = JWTAuth::parseToken()->authenticate();

        $DB_List = DB::table('invites')->where('invitee_email', '=', $current_user->email)
                                        ->where('status', '=', 'pending')->get();

        if ($DB_List != "[]")
        {
            return response()->json([
                array('pending' => $DB_List, 'status_code' => 200
            )], 200);
        }
        else
        {
           return response()->json([
                'status' => array('message' => 'No pending invites', 'status_code' => 200
            )], 200);
        }
    }

    public function accepted(Request $request)
    {
        $current_user = JWTAuth::parseToken()->authenticate();

        $DB_List = DB::table('invites')->where('invitee_email', '=', $current_user->email)
                                        ->where('status', '=', 'accepted')->get();

        if ($DB_List != "[]")
        {
            return response()->json([
                array('accepted' => $DB_List, 'status_code' => 200
            )], 200);
        }
        else
        {
           return response()->json([
                'status' => array('message' => 'No accepted invites', 'status_code' => 200
            )], 200);
        }
    }

    public function rejected(Request $request)
    {
        $current_user = JWTAuth::parseToken()->authenticate();

        $DB_List = DB::table('invites')->where('invitee_email', '=', $current_user->email)
                                        ->where('status', '=', 'rejected')->get();

        if ($DB_List != "[]")
        {
            return response()->json([
                array('rejected' => $DB_List, 'status_code' => 200
            )], 200);
        }
        else
        {
           return response()->json([
                'status' => array('message' => 'No rejected invites', 'status_code' => 200
            )], 200);
        }
    }

    public function response(Request $request)
    {

        $current_user = JWTAuth::parseToken()->authenticate();

        //initial check
        if (($request->get('meeting_id') == NULL) ||
            ($request->get('response') == NULL) ||
            ($request->get('id') == NULL))
        {
            return response()->json([
                'error' => array('message' => 'Missing parameter', 'status_code' => 400
            )], 400);  
        }

        if((strcmp($request->get('response'), 'accept') != 0) &&
            (strcmp($request->get('response'), 'reject') != 0))
        {
            return response()->json([
                'error' => array('message' => 'Incorrect response', 'status_code' => 400
            )], 400);  
        }

        //check if meeting exists
        if ((DB::table('invites')->where('meeting_id', '=', $request->get('meeting_id'))
                                 ->where('id', '=', $request->get('id'))->get()) == "[]")
        {
            return response()->json([
                'error' => array('message' => 'Meeting does not exist', 'status_code' => 403
            )], 403);  
        }

        //check if current user is invitee of meeting
        $Invitee = DB::table('invites')->where('meeting_id', '=', $request->get('meeting_id'))
                                        ->where('id', '=', $request->get('id'))
                                        ->pluck('invitee_email')[0];
        if ($Invitee != $current_user->email)
        {
            return response()->json([
                $Invitee,
                'error' => array('message' => 'You are not invited to this meeting', 'status_code' => 403
            )], 403);  
        }  

        //send response
        $Invite = DB::table('invites')->where('meeting_id', '=', $request->get('meeting_id'))
                                ->where('invitee_email', '=', $current_user->email)
                                ->update(['status' => $request->get('response')]);

        return response()->json([
            'status' => array('message' => $request->get('response'), 'status_code' => 200
        )], 200);  
        
    }
}
