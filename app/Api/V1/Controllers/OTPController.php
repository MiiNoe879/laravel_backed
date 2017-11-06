<?php

namespace App\Api\V1\Controllers;

use Config;
use Request;
use DB;
use App\User;
use App\Validation;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\SignUpRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class OTPController extends Controller
{
    public function signUp(SignUpRequest $request)
    {   
        $ip_address = $this->getIpaddressofClient();//get client ip address
        $phone_number = $request->get('phonenumber');//get request phonenumber
        
        if($this->rateLimitExceeded($phone_number, $ip_address)){
            return response()->json([
                'message' => 'Rate limit exceeded.',
                'status' => 'fail',
                'code'  =>  403
            ], 200);
        } else {
            $count = User::where('phonenumber', $phone_number)->count();
            if($count==0){//if phonenumber no exist
                $user = new User($request->all());
                $user->nickname = 'User' . $this->generatePIN(6);
                $user->token = md5(mt_rand());
                if(!$user->save()) {
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'Unable to save new user.',
                        'code'  =>  500
                    ], 200);
                }
            }
            $pincode = $this->generatePIN();//random pincode generate
        }

        //send pincode sms
        $message = "Your BurkaWatch validation code: " . $pincode;
        if (env('TWILIO_SMS_ENABLED')) {
            $this->_sendSms($phone_number, $message);
        }

        //Save Validations
        $validation = new Validation();
        $validation->pin_code = $pincode;
        $validation->ip_address = $this->getIpaddressofClient(); 
        $validation->phone_number = $phone_number;

        if(!$validation->save())
            throw new HttpException(500);

        return response()->json([
            'status' => 'ok',
            'message' => 'OTP Token sent successfully.',
            'code'  =>  200
        ], 200);
    }

    //generate pin code
    private function generatePIN($digits = 4){
        $i = 0; //counter
        $pin = ""; //our default pin is blank.
        while($i < $digits){
            //generate a random number between 0 and 9.
            $pin .= mt_rand(0, 9);
            $i++;
        }
        return $pin;
    }

    //send sms
    private function _sendSms($to, $message){
        $accountSid = env('TWILIO_ACCOUNT_SID');
        $authToken = env('TWILIO_AUTH_TOKEN');
        $twilioNumber = env('TWILIO_NUMBER');

        $client = new Client($accountSid, $authToken);

        try {
            $client->messages->create(
                $to,
                [
                    "body" => $message,
                    "from" => $twilioNumber
                ]
            );
        } catch (TwilioException $e) {
            throw new HttpException(500);
        }
    }

    //check rate limit
    private function rateLimitExceeded($phonenumber,$ipaddress){
        //$output = new ConsoleOutput();
        $otpAttemptsPhoneNumber = DB::table('validations')
            ->where('phone_number', '=', $phonenumber)
            ->where('created_at', '>', \DB::raw('DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 24 HOUR)'))
            ->count();

        $otpAttemptsIP = DB::table('validations')
            ->where('ip_address', '=', $ipaddress)
            ->where('created_at', '>=', \DB::raw('DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 24 HOUR)'))
            ->count();

        if($otpAttemptsIP > 5 || $otpAttemptsPhoneNumber > 5) {
            return true;
        } else {
            return false;
        }
    }

    //get client ip address
    private function getIpaddressofClient(){
        return Request::ip();
    }
}
