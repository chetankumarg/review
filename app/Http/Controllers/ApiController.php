<?php

namespace App\Http\Controllers;
use App\Models\MobileUsers;
use App\Models\MobileAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Plivo\RestClient;
use DB;

class ApiController extends Controller
{
    
    //function to send sms testing purpose...
   public function send_testsms(){

    $otp = mt_rand(1000,9999);
    $client = new RestClient("MAZGMXNDEYOWJMZDG3ND","ODA2ZGM1MWFiYTk4Yjk5ZTM1YTM5OWQ2ZWQ0ZjIw");
    // $client = new PhloRestClient("MAZGMXNDEYOWJMZDG3ND","ODA2ZGM1MWFiYTk4Yjk5ZTM1YTM5OWQ2ZWQ0ZjIw");
    $response = $client->messages->create(
        [  
            "src" => "+918861122509",
            "dst" => "+919738432807",
            "text"  =>"Hi, Welcome to Review App, Your login one-time password is: $otp"
         ]
  );

    header('Content-Type: application/json');
    return json_encode($response->statusCode);

   }

   public function send_sms($otp,$dest_no,$mess_text){

    $client = new RestClient("MAZGMXNDEYOWJMZDG3ND","ODA2ZGM1MWFiYTk4Yjk5ZTM1YTM5OWQ2ZWQ0ZjIw");
    $response = $client->messages->create(
        [  
            "src" => "+918861122509",
            "dst" => $dest_no,
            "text"  =>"$mess_text : $otp"
         ]
  );

    header('Content-Type: application/json');
    return json_encode($response->statusCode);

   }

   // function to create users though mobile api
   public function createUser(Request $request) {
    // logic to create a mobileUser record goes here 
    $user_mail_count = MobileUsers::where('email',$request->email)->count();
    $user_phone_count = MobileUsers::where('phone_no',$request->phone_no)->count();
    $user_name_count = MobileUsers::where('user_name',$request->user_name)->count();

        if(empty($request->full_name)){
            return response()->json([
                "status" => false,
                "message" => "full name is required"
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
        }elseif($user_name_count > 0){
            return response()->json([
                "status" => false,
                "message" => "User-name already present, please try other one"
            ], 201);
        }
          $otp = mt_rand(1000,9999);

         $mess_text = "Hi, Welcome to Review App, Your Mobile Verification one-time password is: ";

         $mess_status = send_sms($otp,$request->phone_no,$mess_text);

        if($mess_status == "202"){

          $mobileUser = new MobileUsers;
          $mobileUser->full_name = $request->full_name;
          $mobileUser->user_name = $request->user_name;
          $mobileUser->email = $request->email;
          $mobileUser->phone_no = $request->phone_no;
          $mobileUser->otp =  $otp;
          $mobileUser->password = Hash::make("1234512*");
          $mobileUser->active = 0;
          $mobileUser->profile_picture = "";

          $mobileUser->save();
         
          if($mobileUser){
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
        }else{
            return response()->json([
                "status" => false,
                "message" => "SMS not send to destination phone no"
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

       // function to create users though mobile api
       public function loginMobile(Request $request) {
        $user_phone_count = MobileUsers::where('phone_no',$request->phone_no)->count();
        $otp = mt_rand(1000,9999);
        $mess_text = "Hi, Welcome to Review App, Your Login one-time password is: ";

        if($user_phone_count == 0){
            return response()->json([
                "status" => false,
                "message" => "Mobile number is not registered,please registered with the mobile number"
            ], 201);
        }else{
            $mobile_otp_count = MobileAuthentication::where('phone_no',$request->phone_no)->count();
            
            if($mobile_otp_count == 0){
                // First time login case.
                $mess_status = send_sms($otp,$request->phone_no,$mess_text);
                if($mess_status == "202"){

                    $mobile_otp_User = new MobileAuthentication;
                    $mobile_otp_User->phone_no = $request->phone_no;
                    $mobile_otp_User->otp = $otp;
                    $mobile_otp_User->expired = "0";
                    $mobile_otp_User->save();

                    return response()->json([
                        "status" => true,
                        "message" => "otp is send to the register mobile number"
                    ], 201);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "otp is not send to the register mobile number"
                    ], 201);
                }    
            }else{   
                // Login senorio for the second time
                $mess_status = send_sms($otp,$request->phone_no,$mess_text);
                if($mess_status == "202"){
                    MobileAuthentication::where('phone_no',$request->phone_no)
                        ->update(array(
                                'otp'=> "1235",
                                'expired'=> "0"
                        ));  
                        return response()->json([
                            "status" => true,
                            "message" => "otp is send to the register mobile number updated"
                        ], 201);   
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "otp is not send to the register mobile number updated"
                    ], 201); 
                }                     
            }

        }
     }   

     // function for the login verified_otp for the logins
     public function loginverifield_otp(Request $request){

        $user_otp_verification_count = MobileUsers::where('phone_no',$request->phone_no)->count();
        $mobile_otp_count = MobileAuthentication::where('phone_no',$request->phone_no)->count();
    
        if($user_otp_verification_count == 1 && $mobile_otp_count == 1){

            $mobile_otp_verification_count = MobileAuthentication::where('phone_no',$request->phone_no)->where('otp',$request->otp)->count();

            if( $mobile_otp_verification_count == 1){
                MobileAuthentication::where('phone_no',$request->phone_no)
                ->update(array(
                        'expired'=> "1"
                ));  
                return response()->json([
                    "status" => true,
                    "message" => "otp is correct"
                ], 201);  
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "otp is wrong"
                ], 201); 
            }
        }else{
            return response()->json([
                "status" => false,
                "message" => "Please Register and login then otp will be sent"
            ], 201);
        }
      }

}
