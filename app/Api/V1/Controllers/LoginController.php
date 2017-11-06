<?php

namespace App\Api\V1\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\LoginRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use App\Validation;
use App\User;

class LoginController extends Controller 
{
    public function login(LoginRequest $request) 
    {
        $pincode = $request->get('pincode');
        $phonenumber = $request->get('phonenumber');

        // Check for valid OTP token that isn't too old.
        $validRecentOTPToken = DB::table('validations')
            ->where('phone_number', '=', $phonenumber)
            ->where('pin_code', '=', $pincode)
            ->where('created_at', '>', DB::raw('DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 24 HOUR)'))
            ->count();

        if ($validRecentOTPToken) {
            //remove old sms OTP token
            Validation::where('phone_number', '=', $phonenumber)
                        ->delete();
            $users = User::where('phonenumber', '=', $phonenumber)->get();
            $token = $users[0]->token;
            return response()
                    ->json([
                        'status' => 'ok',
                        'token' => $token,
                        'code' =>  200
                    ]);
        } else{//pin code mismatching
            return response()
                    ->json([
                            'status' => 'fail',
                            'message' => 'Unable to find matching OTP and phone number.',
                            'code' =>  422
                    ]);
        }
    }
}
