<?php

namespace App\Http\Controllers;
use App\Models\MobileUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;

use Plivo\RestClient;
// use Plivo\Resources\PHLO\PhloRestClient;
// use Plivo\Exceptions\PlivoRestException;

class ApiController extends Controller
{
    
   //function to send sms testing purpose...
   public function send_testsms(){

    $otp = mt_rand(1000,9999);
    $client = new RestClient("MAZGMXNDEYOWJMZDG3ND","ODA2ZGM1MWFiYTk4Yjk5ZTM1YTM5OWQ2ZWQ0ZjIw");
    // $client = new PhloRestClient("MAZGMXNDEYOWJMZDG3ND","ODA2ZGM1MWFiYTk4Yjk5ZTM1YTM5OWQ2ZWQ0ZjIw");
    $response = $client->messages->create(
        [  
            "src" => "+919738432807",
            "dst" => "+918861122509",
            "text"  =>"Hi, Welcome to Review App, Your login one-time password is: $otp",
         ]
  );

    header('Content-Type: application/json');
    return json_encode($response);

   }

   // function to create users though mobile api
   public function createUser(Request $request) {
    // logic to create a mobileUser record goes here 
 
    $user_mail_count = MobileUsers::where('email',$request->email)->count();
    $user_phone_count = MobileUsers::where('phone_no',$request->phone_no)->count();
        if(empty($request->first_name)){
            return response()->json([
                "status" => false,
                "message" => "first name is required"
            ], 201);
        }elseif(empty($request->password)){
            return response()->json([
                "status" => false,
                "message" => "Password is required"
            ], 201);
        }elseif(empty($request->email)){
            return response()->json([
                "status" => false,
                "message" => "email is required"
            ], 201);
        }elseif(empty($request->phone_no)){
            return response()->json([
                "status" => false,
                "message" => "phone_no is required"
            ], 201);
        }elseif($user_mail_count > 0){
            return response()->json([
                "status" => false,
                "message" => "Email is already present"
            ], 201);
        }elseif($user_phone_count > 0){
            return response()->json([
                "status" => false,
                "message" => "Phone number is already present"
            ], 201);
        }


          $mobileUser = new MobileUsers;
          $mobileUser->first_name = $request->first_name;
          $mobileUser->last_name = $request->last_name;
          $mobileUser->email = $request->email;
          $mobileUser->phone_no = $request->phone_no;
          $mobileUser->otp =  mt_rand(1000,9999);
          $mobileUser->password = Hash::make($request->password);
          $mobileUser->active = 0;
          $mobileUser->picture = "";

          $mobileUser->save();
         
          if($mobileUser){
            $generatedotp = rand(100000,999999);

            $to = "+919738432807";
            $from = "+918861122509";
            $message = "Dear Customer,".$generatedotp." is your one-time password (OTP).
            Thank you,
            Team Review.";
            $sms = plivo_send_text($to, $message, $from);
        
            if(isset($sms['response']['error']) && !empty($sms['response']['error']) ){
                return response()->json(['status'=>false ,'message' => $sms['response']['error']], 200);
            } else {
                return response()->json(['status'=>true ,'message' => 'Successfully send otp!'], 200);
            }
                return response()->json([
                    "status" => true,
                    "message" => "mobileUser record created"
                ], 201);
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "mobileUser record not created"
                ], 201);
            }
  }

  public function verifield_otp(Request $request){

    $user_otp_verification_count = MobileUsers::where('phone_no',$request->phone_no)->where('otp',$request->otp)->count();

    if($user_otp_verification_count == 1){
        // used to update the mobile user of active is 1
        MobileUsers::where('phone_no',$request->phone_no)->update(["active" => 1]);
        return response()->json([
            "status" => true,
            "message" => "OTP given is correct"
        ], 201);
    }else{
        return response()->json([
            "status" => false,
            "message" => "OTP given is not-correct"
        ], 201);
    }
  }

}
