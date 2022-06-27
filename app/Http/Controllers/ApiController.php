<?php

namespace App\Http\Controllers;
use App\Models\MobileUsers;
use App\Models\followers;
use App\Models\Review;
use App\Models\categories;
use App\Models\Comment;
use App\Models\comments_likes;
use App\Models\subcomments_likes;
use App\Models\agree_comments;
use App\Models\agree_subcomments;
use App\Models\SubComment;
use App\Models\Likes;
use App\Models\Views;
use App\Models\MobileAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Plivo\RestClient;
use DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Helpers\AppHelper;
use Image;
use Carbon\Carbon;

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
    return json_encode($response);
    return json_encode($response->statusCode);
   }
   // function used to get the user details by phone-no:
   public function getusr_detail_by_phone($phoneno){
        $mobileUsers = MobileUsers::where('phone_no', $phoneno)->get();
        $userdata = array();
        foreach($mobileUsers as $data)
                                    {                                      
                                    $userdata["id"] = $data->id;
                                    $userdata["full_name"] = $data->full_name;  // $petani is a Std Class Object here
                                    $userdata["email"] = $data->email;
                                    $userdata["username"] = $data->user_name;
                                    $userdata["phoneno"] = $data->phone_no;
                                    $userdata["profile_picture"] = $data->profile_picture;
                                    $userdata["active"] = $data->active;
                                    $userdata["createdat"] = $data->created_at;
                                    }
        return $userdata;                           
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

   public function check_userdetials(Request $request){
       $user_name = $request->user_name;
       $user_email = $request->user_mail;
       $user_mobileno = $request->user_mobileno;

       if(empty($request->user_name)){
        return response()->json([
            "status" => false,
            "message" => "User Name should not be empty."
        ], 200);
       }

       if(empty($request->user_mail)){
        return response()->json([
            "status" => false,
            "message" => "User Mail should not be empty."
        ], 200);
       }

       if(empty($request->user_mobileno)){
        return response()->json([
            "status" => false,
            "message" => "User Mobile-No should not be empty."
        ], 200);
       }

    $user_name_count = MobileUsers::where('user_name',$request->user_name)->count();
    $suggestion_name = array();
    if($user_name_count == 0){
        for($i = 0; $i < 3; $i++){
            $full_name = $request->user_name;
           // $f_name = substr($full_name, 0 ,4) ."@". mt_rand(1000,9999);
            $f_name=explode(' ', $full_name);
            $f_name=$f_name[0]."_".mt_rand(1000,9999);
            $user_name_f_count = MobileUsers::where('user_name',$f_name)->count();
            if(  $user_name_f_count == 0){
                $suggestion_name[] = $f_name;
            }else{
                $i--;
            }
        }
        
            $user_status = true;          
            $user_message = "User-name is available you use along with it you can use other suggestion name";
            $user_isAvailable = true;
            $user_avaliable_username = $suggestion_name;
       
    }else{

        for($i = 0; $i < 3; $i++){
            $full_name = $request->user_name;
            $f_name = substr($full_name, 0 ,4) ."@". mt_rand(1000,9999);
            $user_name_f_count = MobileUsers::where('user_name',$f_name)->count();
            if(  $user_name_f_count == 0){
                $suggestion_name[] = $f_name;
            }else{
                $i--;
            }
        }
            $user_status = true;
            $user_message = "User-name suggestion";
            $user_isAvailable  = false;
            $user_avaliable_username  = $suggestion_name;
        
    }
    $user_phone_count = MobileUsers::where('phone_no',$request->user_mobileno)->count();     
    if( $user_phone_count == 0){
       
            $user_mobile_status = true;
            $user_mobile_message = "Mobile number is availabe";
      
    }else{
            $user_mobile_status = false;
            $user_mobile_message = "Mobile number is not availabe";
      
    }

    $user_mail_count = MobileUsers::where('email',$request->user_mail)->count();
    if( $user_mail_count == 0){
        
            $user_mail_status = true;
            $user_mail_message = "User Email is availabe";
      
    }else{
      
            $user_mail_status = false;
            $user_mail_message = "User Email is not availabe";
       
    }

    return response()->json([
        "status" => true,            
        "user_name_status" =>  $user_status,
        "user_name_message" => $user_message,
        "user_name_isAvailable" => $user_isAvailable,
        "user_name_avaliable" => $user_avaliable_username,
        "user_mobile_status" => $user_mobile_status,
        "user_mobile_message" =>$user_mobile_message,
        "user_mail_status" => $user_mail_status,
        "user_mail_message" =>  $user_mail_message
    ], 200);

   }
   
   // function for the existing of user_name and suggestion of user-names
   public function check_username(Request $request){

    $user_name_count = MobileUsers::where('user_name',$request->user_name)->count();
    $suggestion_name = array();
    if($user_name_count == 0){
        for($i = 0; $i < 3; $i++){
            $full_name = $request->full_name;
           // $f_name = substr($full_name, 0 ,4) ."@". mt_rand(1000,9999);
            $f_name=explode(' ', $full_name);
            $f_name=$f_name[0]."_".mt_rand(1000,9999);
            $user_name_f_count = MobileUsers::where('user_name',$f_name)->count();
            if(  $user_name_f_count == 0){
                $suggestion_name[] = $f_name;
            }else{
                $i--;
            }
        }
        return response()->json([
            "status" => true,            
            "message" => "User-name is available you use along with it you can use other suggestion name",
            "isAvailable" => true,
            "avaliable_username" => $suggestion_name
        ], 201);
    }else{

        for($i = 0; $i < 3; $i++){
            $full_name = $request->full_name;
            $f_name = substr($full_name, 0 ,4) ."@". mt_rand(1000,9999);
            $user_name_f_count = MobileUsers::where('user_name',$f_name)->count();
            if(  $user_name_f_count == 0){
                $suggestion_name[] = $f_name;
            }else{
                $i--;
            }
        }
        return response()->json([
            "status" => true,
            "message" => "User-name suggestion",
            "isAvailable" => false,
            "avaliable_username" => $suggestion_name
        ], 201);
    }
   }

   // function for checking the check_mobileno
   public function check_mobileno(Request $request){
    
    $user_phone_count = MobileUsers::where('phone_no',$request->phone_no)->count();     
        if( $user_phone_count == 0){
            return response()->json([
                "status" => true,
                "message" => "Mobile number is availabe"
            ], 201);
        }else{
            return response()->json([
                "status" => false,
                "message" => "Mobile number is not availabe"
            ], 201);
        }
   }

    // function for checking the check_email
    public function check_email(Request $request){
    
        $user_mail_count = MobileUsers::where('email',$request->email)->count();
        if( $user_mail_count == 0){
            return response()->json([
                "status" => true,
                "message" => "User Email is availabe"
            ], 201);
        }else{
            return response()->json([
                "status" => false,
                "message" => "User Email is not availabe"
            ], 201);
        }
    
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
        }
        // elseif($user_mail_count > 0){
        //     return response()->json([
        //         "status" => false,
        //         "message" => "Email is already present"
        //     ], 201);
        // }elseif($user_phone_count > 0){
        //     return response()->json([
        //         "status" => false,
        //         "message" => "Phone number is already present"
        //     ], 201);
        // }elseif($user_name_count > 0){
        //     return response()->json([
        //         "status" => false,
        //         "message" => "User-name already present, please try other one"
        //     ], 201);
        // }

        /* conditions for the checking username, email and phone-no: */
        $user_name_count = MobileUsers::where('user_name',$request->user_name)->count();
        $suggestion_name = array();
        if($user_name_count == 0){
            for($i = 0; $i < 3; $i++){
                $full_name = $request->full_name;
               // $f_name = substr($full_name, 0 ,4) ."@". mt_rand(1000,9999);
                $f_name=explode(' ', $full_name);
                $f_name=$f_name[0]."_".mt_rand(1000,9999);
                $user_name_f_count = MobileUsers::where('user_name',$f_name)->count();
                if(  $user_name_f_count == 0){
                    $suggestion_name[] = $f_name;
                }else{
                    $i--;
                }
            }
            
                $user_status = true;          
                $user_message = "User-name is available you use along with it you can use other suggestion name";
                $user_isAvailable = true;
                $user_avaliable_username = $suggestion_name;
           
        }else{
    
            for($i = 0; $i < 3; $i++){
                $f_name=explode(' ', $request->full_name);
                $f_name=$f_name[0]."_".mt_rand(1000,9999);
                $user_name_f_count = MobileUsers::where('user_name',$f_name)->count();
                if(  $user_name_f_count == 0){
                    $suggestion_name[] = $f_name;
                }else{
                    $i--;
                }
            }
                $user_status = true;
                $user_message = "User-name suggestion";
                $user_isAvailable  = false;
                $user_avaliable_username  = $suggestion_name;
            
        }
        $user_phone_count = MobileUsers::where('phone_no',$request->phone_no)->count();     
        if( $user_phone_count == 0){
           
                $user_mobile_status = true;
                $user_mobile_message = "Mobile number is availabe";
          
        }else{
                $user_mobile_status = false;
                $user_mobile_message = "Mobile number is not availabe";
          
        }
    
        $user_mail_count = MobileUsers::where('email',$request->email)->count();
        if( $user_mail_count == 0){
            
                $user_mail_status = true;
                $user_mail_message = "User Email is availabe";
          
        }else{
          
                $user_mail_status = false;
                $user_mail_message = "User Email is not availabe";
           
        }
        /* */
        if($user_isAvailable == true &&  $user_mobile_status == true && $user_mail_status == true ){
          $otp = mt_rand(1000,9999);

         $mess_text = "Hi, Welcome to Review App, Your Mobile Verification one-time password is: ";

         $mess_status = self::send_sms($otp,$request->phone_no,$mess_text);

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
                    "message" => "mobileUser record created",
                    "user_name_status" =>  $user_status,
                    "user_name_message" => $user_message,
                    "user_name_isAvailable" => $user_isAvailable,
                    "user_name_avaliable" => $user_avaliable_username,
                    "user_mobile_status" => $user_mobile_status,
                    "user_mobile_message" =>$user_mobile_message,
                    "user_mail_status" => $user_mail_status,
                    "user_mail_message" =>  $user_mail_message
                ], 200);
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "mobileUser record not created",
                    "user_name_status" =>  $user_status,
                    "user_name_message" => $user_message,
                    "user_name_isAvailable" => $user_isAvailable,
                    "user_name_avaliable" => $user_avaliable_username,
                    "user_mobile_status" => $user_mobile_status,
                    "user_mobile_message" =>$user_mobile_message,
                    "user_mail_status" => $user_mail_status,
                    "user_mail_message" =>  $user_mail_message
                ], 200);
            }
        }else{
            return response()->json([
                "status" => false,
                "message" => "SMS not send to destination phone no"
            ], 200);
        } 
        }else{
            return response()->json([
                "status" => false,
                "message" => "user_name or mobile no or email is already present",
                "user_name_status" =>  $user_status,
                "user_name_message" => $user_message,
                "user_name_isAvailable" => $user_isAvailable,
                "user_name_avaliable" => $user_avaliable_username,
                "user_mobile_status" => $user_mobile_status,
                "user_mobile_message" =>$user_mobile_message,
                "user_mail_status" => $user_mail_status,
                "user_mail_message" =>  $user_mail_message
            ], 200);
        }   
  }

  // function to create the review (post) by the mobile user api...
    public function create_review(Request $request){

        $short_url = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 6);

        $mobileReview = new Review;
        $mobileReview->name = $request->name;
        $mobileReview->hashtags = $request->hashtags;
        $mobileReview->mobile_user_id = $request->mobile_user_id;
        $mobileReview->description = $request->description;
        $mobileReview->image =  $request->image;
        $mobileReview->lat = $request->lat;
        $mobileReview->long = $request->long;
        $mobileReview->usr_lat = $request->usr_lat;
        $mobileReview->usr_long = $request->usr_long;
        $mobileReview->other_hashtags = $request->other_hashtags;
        $mobileReview->rating = $request->rating;
        $mobileReview->categorie_id = $request->categorie_id;
        $mobileReview->shorturl = $short_url;
        $mobileReview->publish = "1";

        $mobileReview->save();
       
        if($mobileReview){
              return response()->json([
                  "status" => true,
                  "message" => "mobileReview record created",
                  "post_id" => $mobileReview->id,
                  "short_url" =>env('APP_URL'). "review_detail/". $short_url 
              ], 200);
          }else{
              return response()->json([
                  "status" => false,
                  "message" => "mobileReview record not created"
              ], 200);
          }

    }

  public function verifield_otp(Request $request){

    $user_otp_verification_count = MobileUsers::where('phone_no',$request->phone_no)->where('otp',$request->otp)->count();

    if($user_otp_verification_count == 1){
        // used to update the mobile user of active is 1
        MobileUsers::where('phone_no',$request->phone_no)->update(["active" => 1]);
        $userdetails = self::getusr_detail_by_phone($request->phone_no);
        return response()->json([
            "status" => true,
            "message" => "OTP given is correct",
            "userdetails" => $userdetails
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
                $mess_status = self::send_sms($otp,$request->phone_no,$mess_text);
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
                $mess_status = self::send_sms($otp,$request->phone_no,$mess_text);
                if($mess_status == "202"){
                    MobileAuthentication::where('phone_no',$request->phone_no)
                        ->update(array(
                                'otp'=> $otp,
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
                $userdetails = self::getusr_detail_by_phone($request->phone_no);
                MobileAuthentication::where('phone_no',$request->phone_no)
                ->update(array(
                        'expired'=> "1"
                ));  
                return response()->json([
                    "status" => true,
                    "message" => "otp is correct",
                    "userdetails" => $userdetails
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

    // function for the resend otp for the registeration / login    
    public function resend_otp(Request $request){

        $mobile_number = $request->phone_no;
        $otp_from = $request->otpfrom;

        $user_phone_count = MobileUsers::where('phone_no',$request->phone_no)->count();

        if($user_phone_count == 0){
            return response()->json([
                "status" => false,
                "message" => "Mobile number is not register"
            ], 201);
        }

        $mobile_otp_count = MobileAuthentication::where('phone_no',$request->phone_no)->count();

        // otpfrom will be 1 or 2 , 1 means register ,2 means login
        if($otp_from == 2){
            $userdetails = self::getusr_detail_by_phone($mobile_number);
            $otp = mt_rand(1000,9999);
            $mess_text = "Hi, Welcome to Review App, (Resend OTP) Your Login one-time password is: ";
            
            $mess_status = self::send_sms($otp,$request->phone_no,$mess_text);
                if($mess_status == "202"){
                    MobileAuthentication::where('phone_no',$request->phone_no)
                        ->update(array(
                                'otp'=> $otp,
                                'expired'=> "0"
                        ));  
                        return response()->json([
                            "status" => true,
                            "otp_from"=> "login",
                            "resend_otp_register" => false,
                            "userdetails" => $userdetails,
                            "message" => "Resend otp is send to the register mobile number updated"
                        ], 201);   
                }    
        }elseif($otp_from == 1){

            $otp = mt_rand(1000,9999);

            $mess_text = "Hi, Welcome to Review App, Your Mobile Verification one-time password is: ";

            $mess_status = self::send_sms($otp,$request->phone_no,$mess_text);

            if($mess_status == "202"){
                MobileUsers::where('phone_no',$request->phone_no)
                    ->update(array(
                            'otp'=> $otp
                    ));  
                    return response()->json([
                        "status" => true,
                        "otp_from"=> "register",
                        "resend_otp_register" => true,
                        "message" => "otp is re-send to the register mobile number updated"
                    ], 201);   
            }

        }

    }  

    public function getuser_details(Request $request){

        $userid = $request->uid;
        $userdata = array();
        $user_id_count = MobileUsers::where('id',$userid)->count();
        if($user_id_count == 1){
        $mobileUsers = MobileUsers::where('id', $userid)->get();

        foreach($mobileUsers as $data)
                                    {                                      
                                      $userdata["id"] = $data->id;
                                      $userdata["full_name"] = $data->full_name;  // $petani is a Std Class Object here
                                      $userdata["email"] = $data->email;
                                      $userdata["username"] = $data->user_name;
                                      $userdata["phoneno"] = $data->phone_no;
                                      $userdata["active"] = $data->active;
                                      $userdata["createdat"] = $data->created_at;
                                    }
                                    
        return response()->json([
                "status" => true,
                "userdetails" => $userdata
        ], 201); 
    }else{
        return response()->json([
            "status" => false,
            "message" => "Userid not present in database"
    ], 201); 
    }                           
        
    }

    public function getupload_pic(Request $request){
        $mobile_number = $request->phone_no;

       $profile_pic =MobileUsers::where('phone_no', $mobile_number)->value('profile_picture');
    
       if($profile_pic){
        return response()->json([
            "status" => true,
            "image_path" => $profile_pic
        ], 201);
       }else{
        return response()->json([
            "status" => false,
            "image_path" => ''
        ], 201);
       }

    }

    public function upload_media_img(Request $request){

        if (request()->hasFile('upload_img')){
            $image = $request->file('upload_img');
            $imageName = random_int(10000, 99999)."-".time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/post_media/');

            $destinationPath_small = 'uploads/post_media/thumbnail';

            // $img = Image::make($image->path());    
            // $img->resize(300, 200, function ($constraint) {    
            //     $constraint->aspectRatio();    
            // })->save($destinationPath_small.'/'.$imageName);


           // $image->move($destinationPath, $imageName);
            if( $image->move($destinationPath, $imageName) ){
                $file_path =$destinationPath."".$imageName;
                $file_thumbnail = $destinationPath_small.'/'.$imageName;

                return response()->json([
                    "status" => 200,                         
                    "message" => "Image has been uploaded successfully",
                    "img_url" => $file_path,
                    "image_url" => env('APP_URL'). "/uploads/post_media/".$imageName
                    // "image_thumbnail" => env('APP_URL')."/".$file_thumbnail
                ], 200);
            }else{

                return response()->json([
                    "status" => "fail",                           
                    "message" => "Image has been not uploaded successfully"
                ], 201);
            }
            $image->imagePath = $destinationPath . $imageName;
            
        }

    }

    public function upload_pic(Request $request){

         $mobile_number = $request->phone_no;
         $mobile_number = $request->uid;
         $otp_from = $request->image;

        // Working file upload wokring 
        $file_photo =null;
        $file_sign =null;

                // Handle File upload
                if (request()->hasFile('profile_pic')){
                    $image = $request->file('profile_pic');
                    $imageName = $request->uid ."-".time() . '.' . $image->getClientOriginalExtension();
                    $destinationPath = public_path('/uploads/profile_picture/');
                   // $image->move($destinationPath, $imageName);
                    if( $image->move($destinationPath, $imageName) ){
                        $file_path =$destinationPath."".$imageName;
                        MobileUsers::where('phone_no',$request->phone_no)->update(["profile_picture" => $file_path]);
                        return response()->json([
                            "status" => "success",                           
                            "message" => "Image has been uploaded successfully"
                        ], 201);
                    }else{

                        return response()->json([
                            "status" => "fail",                           
                            "message" => "Image has been not uploaded successfully"
                        ], 201);
                    }
                    $image->imagePath = $destinationPath . $imageName;
                    
                }
        // if($request->file('receiver_photo')){
        //     $name = $request->uid.'_'.$request->file('receiver_photo')->getClientOriginalExtension();
        //     $ext  = $request->file('receiver_photo')->extension();
        //     $url  = 'delivery/photo/'.$name;
        //     $request->receiver_photo->move(base_path('public/uploads/profile_picture/'), $name);
           
        //     $file_photo = $request->files()->create( [
        //         'name'    => $name,
        //         'url'     => $url,
        //         'ext'     => $ext,
        //         'type'     => 'receiver_photo',
        //         'alt' => $request->input( 'alt_text' )
                
        //     ]);//->fillable(['type' => 'receiver_photo']);
        // }
        // File uploading file structure...
        
    }

    public function getuser_followers(Request $request){

        $username = $request->name;
        $current_userid = $request->uid;
        $usercontianer = array();

        if(!empty($username)){
            $mobileUsers = MobileUsers::where('user_name', 'like', '%'.$username.'%')->get();

            if(count($mobileUsers) > 0){
            foreach($mobileUsers as $data)
                            {                                      
                              $userdata["id"] = $data->id;
                              $userdata["full_name"] = $data->full_name;  // $petani is a Std Class Object here
                              $userdata["email"] = $data->email;
                              $userdata["profile_picture"] = $data->profile_picture;
                              $userdata["username"] = $data->user_name;
                              $userdata["phoneno"] = $data->phone_no;
                              $userdata["active"] = $data->active;
                              $userdata["createdat"] = $data->created_at;
                              $usercontianer[] = $userdata;
                            }
                return response()->json([
                                "status" => true,
                                "message" => "User-name with".$username,
                                "userdetails" => $usercontianer
                ], 201);             
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "No User name is found",
                    "userdetails" => []
                 ], 201);
            }                
        }

        if(!empty($current_userid)){

            $result = DB::table("mobile_users as mu")
            ->join("followers as fol","fol.user_id","=","mu.id")
          // as per the discussion will list all users   ->where('fol.user_id',$current_userid)
            ->select("mu.id", 
                    "fol.user_id","follower_id")
            ->get();

            if(count($result) > 0){
            foreach($result as $data)
            {                                      
              $userdata['id'] = $data->id;
              $userdata['followerid'] = $data->follower_id;

              $mobileUsers = MobileUsers::where('id', $data->follower_id)->get();

              foreach($mobileUsers as $data)
                            {                                      
                              $userdata["id"] = $data->id;
                              $userdata["full_name"] = $data->full_name;  // $petani is a Std Class Object here
                              $userdata["email"] = $data->email;
                              $userdata["profile_picture"] = $data->profile_picture;
                              $userdata["username"] = $data->user_name;
                              $userdata["phoneno"] = $data->phone_no;
                              $userdata["active"] = $data->active;
                              $userdata["createdat"] = $data->created_at;
                              $usercontianer[] = $userdata;
                            }

             // $usercontianer[] = $userdata;
            }
                return response()->json([
                    "status" => true,
                    "message" => "My Followers",
                    "userdetails" => $usercontianer
                 ], 201); 
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "My Followers",
                    "userdetails" => []
                 ], 201); 
            }     
          //  var_dump($usercontianer);
        }

    }

    public function get_trending_list(Request $request){
        $hashtags = $request->hashtag;
        $usercontianer = array();

        if(empty($hashtags)){
            $result = DB::table("reviews as r")
            ->join("trendings as t","t.review_id","=","r.id")
            ->where('t.active',1)
            ->select("r.id", 
                    "r.hashtags","r.name")
            ->get();        
        }else{

            $result = DB::table("reviews as r")
            ->join("trendings as t","t.review_id","=","r.id")
            ->where('t.active',1)
            ->where('r.hashtags', 'like', '%'.$hashtags.'%')
            ->select("r.id", 
                    "r.hashtags","r.name")
            ->get();     
            
            if(count($result) == 0){
                return response()->json([
                    "status" => false,
                    "message" => "Trending list with this hashtag is 0",
                    "eventdetails" => []
                ], 201);  
            }
        }            

            foreach($result as $data)
                    {                                      
                      $eventdata["id"] = $data->id;
                      $eventdata["name"] = $data->name;  // $petani is a Std Class Object here
                      $eventdata["hashtags"] = $data->hashtags;
                      $eventcontianer[] = $eventdata;
                    }
            
            return response()->json([
                "status" => true,
                "message" => "Trending list",
                "eventdetails" => $eventcontianer
            ], 201);           

    } 

    function outputMetaTags($url){
        // $url = 'https://www.myntra.com/casual-shoes/kook-n-keech/kook-n-keech-men-white-sneakers/2154180/buy';
         $streamContext = stream_context_create(array(
         "http" => array(
             "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36",
             'follow_location' => false
          )
        )
        ); //we try to act as browser, just in case server forbids us to access to page 
 
         $htmlData = file_get_contents($url, false, $streamContext); //fetch the html data from given url
         //libxml_use_internal_errors(true); //optionally disable libxml url errors and warnings
         $doc = new  DOMDocument(); //parse with DOMDocument
         $doc->loadHTML($htmlData);
         $xpath = new  DOMXPath($doc); //create DOMXPath object and parse loaded DOM from HTML
         $query = '//*/meta';
 
         $metaData = $xpath->query($query);
         foreach ($metaData as $singleMeta) {
             //for og:image, check if $singleMeta->getAttribute('property') === 'og:image', same goes with og:url
             //not every meta has property or name attribute
             if(!empty($singleMeta->getAttribute('property'))){
                 echo $singleMeta->getAttribute('property') . "\n";
             }elseif(!empty($singleMeta->getAttribute('name'))){
                 echo $singleMeta->getAttribute('name')  . "\n";
             }
             //get content from meta tag
             echo $singleMeta->getAttribute('content')  . "\n";
 
         }
 }

    public function getpost_review_by_shortcode(Request $request){
        $user_id_mobile = $request->login_user_id;
   // $login_user_id = $request->login_user_id;
        if(!empty($request->shorturl)){
            $post_id = $request->shorturl;
        }else{
            $post_id = $request->id;   
        }
        $postdata = array();
        if(!empty($request->shorturl)){
            $get_reviewcount = Review::where('shorturl',$post_id)->count();
        }else{
            $get_reviewcount = Review::where('id',$post_id)->count(); 
        }
        if($get_reviewcount > 0){
            if(!empty($request->shorturl)){
                $post_review = Review::where('shorturl', $post_id)->get();
            }else{
                $post_review = Review::where('id', $post_id)->get();  
            }

            foreach($post_review as $data)
                                        { 
                                                
                                            $mobile_user = DB::table('mobile_users')
                                            ->select('id', 'full_name', 'user_name', 'email','profile_picture')
                                            ->where('id','=', $data->mobile_user_id)
                                            ->first();
                                            $postdata["id"] = $data->id;
                                            $postdata["name"] = $data->name;  // $petani is a Std Class Object here
                                            $postdata["hashtags"] = $data->hashtags;
                                            $postdata["other_hashtags"] = $data->other_hashtags;
                                            $postdata["mobile_user_id"] = $data->mobile_user_id;
                                            $postdata["mobile_full_name"] = $mobile_user->full_name;
                                            $postdata["mobile_user_name"] = $mobile_user->user_name;
                                            $postdata["mobile_email"] = $mobile_user->email;
                                            if(empty($mobile_user->profile_picture) || $mobile_user->profile_picture == " " || $mobile_user->profile_picture =="" ){
                                                $postdata["mobile_profile_picture"] = "";  
                                            } else
                                                $postdata["mobile_profile_picture"] =  env('APP_URL')."/". str_replace("/var/www/html/review/public/","",$mobile_user->profile_picture) ;
                                            $postdata["description"] = $data->description;
                                            $postdata["image"] = $data->image;
                                            $postdata["rating"] = $data->rating;
                                            $postdata["shorturl"] = $data->shorturl;
                                            $postdata["lat"] = $data->lat;
                                            $postdata["long"] = $data->long;
                                            $postdata["usr_lat"] = $data->usr_lat;
                                            $postdata["usr_long"] = $data->usr_long;
                                            $postdata["created_at"] = $data->created_at;
                                            $postdata["likes_count"] = Likes::where('post_id', $data->id)->count();
                                            $postdata["views_count"] = Views::where('post_id', $data->id)->count();
                                                if(!empty($user_id_mobile)){
                                                    $postdata["User_like_post"] = Likes::where('post_id', $data->id)->where('user_id',$user_id_mobile)->count();
                                                    $postdata["User_follow_post"] =  followers::where('user_id',$data->mobile_user_id)->where('follower_id',$user_id_mobile)->count();
                                                }
                                            $postcontianer[] = $postdata;
                                        }
                                    
            return response()->json([
                    "status" => true,
                    "postdetails" => $postcontianer
            ], 200); 
        }else{
            return response()->json([
                "status" => false,
                "message" => "No post is present for this post-di or shortcode"
            ], 200); 
        } 
    }
    
    public function delete_all_post_review(){
        Review::truncate();
    }

    public function get_all_post_review(){
        $postdata = array();
        $get_reviewcount = Review::count();
        if($get_reviewcount > 0){
        $post_review = Review::orderBy('created_at', 'DESC')->get();
            foreach($post_review as $data)
                                        {                                      
                                        $postdata["id"] = $data->id;
                                        $postdata["name"] = $data->name;  // $petani is a Std Class Object here
                                        $postdata["hashtags"] = $data->hashtags;
                                        $postdata["other_hashtags"] = $data->other_hashtags;
                                        $postdata["mobile_user_id"] = $data->mobile_user_id;
                                        $postdata["description"] = $data->description;
                                        $postdata["image"] = str_replace("/var/www/html/review/public/","http://139.59.76.151/",$data->image);
                                        $postdata["rating"] = $data->rating;
                                        $postdata["shorturl"] = $data->shorturl;
                                        $postdata["lat"] = $data->lat;
                                        $postdata["long"] = $data->long;
                                        $postdata["usr_lat"] = $data->usr_lat;
                                        $postdata["usr_long"] = $data->usr_long;
                                        $postdata["created_at"] = $data->created_at;
                                        $postcontianer[] = $postdata;
                                        }
                                    
            return response()->json([
                    "status" => true,
                    "userdetails" => $postcontianer
            ], 200); 
        }else{
            return response()->json([
                "status" => false,
                "message" => "No post-review created by this user"
            ], 200); 
        }        
    }

    public function getpost_review(Request $request){
        $userid = $request->uid;
        $start = (!empty($request->start)) ? $request->start - 1 : 0;
        $end = (!empty($request->end)) ? $request->end - 1 : 10;
        $postdata = array();
        $get_reviewcount = Review::where('mobile_user_id',$userid)->count();
        if($get_reviewcount > 0){
        $post_review = Review::where('mobile_user_id', $userid)
        ->skip($start)
        ->take($end)
        ->orderBy('id', 'desc')
        ->get();

            foreach($post_review as $data)
                                        {                                      
                                        $postdata["id"] = $data->id;
                                        $postdata["name"] = $data->name;  // $petani is a Std Class Object here
                                        $postdata["hashtags"] = $data->hashtags;
                                        $postdata["other_hashtags"] = $data->other_hashtags;
                                        $postdata["mobile_user_id"] = $data->mobile_user_id;
                                        $postdata["description"] = $data->description;
                                        $postdata["image"] = str_replace("/var/www/html/review/public/","http://139.59.76.151/",$data->image);
                                        $postdata["rating"] = $data->rating;
                                        $postdata["shorturl"] = $data->shorturl;
                                        $postdata["lat"] = $data->lat;
                                        $postdata["long"] = $data->long;
                                        $postdata["usr_lat"] = $data->usr_lat;
                                        $postdata["usr_long"] = $data->usr_long;
                                        $postdata["created_at"] = $data->created_at;
                                        $postcontianer[] = $postdata;
                                        }
                                    
            return response()->json([
                    "status" => true,
                    "userdetails" => $postcontianer
            ], 200); 
        }else{
            return response()->json([
                "status" => false,
                "message" => "No post-review created by this user"
            ], 200); 
        }  
    }

    public function getcategorie_list(Request $request){

        
         $get_categories = categories::all();
         $categorie_data = array();

         foreach($get_categories as $data)
         {                                      
         $categorie_data["id"] = $data->id;
         $categorie_data["name"] = $data->name;  // $petani is a Std Class Object here
         $categorie_contianer[] = $categorie_data;
         }
     
         return response()->json([
            "status" => true,
            "categories" => $categorie_contianer
            ], 200);
    }

    public function get_jsonurl(Request $request){

        $url = $request->url;
        
        // echo "Hi dsafsdaf";

        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_URL, 'https://www.amazon.in/Michael-Kors-Analog-Mother-Watch-MK3831/dp/B0795632LQ?ref_=Oct_DLandingS_D_7d256c7f_77&smid=A1JFLV2BUSJ5AK');
        // $result = curl_exec($ch);
        // curl_close($ch);

        // $obj = json_decode($result);
        // var_dump($obj);
        // return $obj;
     //   $url='https://www.amazon.in/dp/B082DJ9S58/ref=s9_acsd_al_bw_c2_x_0_t?pf_rd_m=A1K21FY43GMZF8&pf_rd_s=merchandised-search-6&pf_rd_r=SE4BJG9N3J0KSQGYD2BN&pf_rd_t=101&pf_rd_p=67d3b229-e14c-4336-b71f-9cdc781470de&pf_rd_i=16676064031';
        $meta=get_meta_tags($url);
        if(!empty($meta['title'])){
           $title=$meta['title'];
        }elseif(!empty($meta['og_title'])){
           $title=$meta['og_title'];
        }elseif(!empty($meta['og:title'])){
            $title=$meta['og:title'];
         }else{
            $title = '';
        }

        if(!empty($meta['Description'])){
            $description = $meta['Description']; 
        }elseif(!empty($meta['description'])){
            $description = $meta['description']; 
        }
        elseif(!empty($meta['og:description'])){
           $description = $meta['og:description'];
        }else{
            $description = '';
        }

        if(!empty($meta['og_image'])){
            $image = $meta['og_image'];
        }else{
            $image = "";
        }

      //  echo "title - " . $title . " <br/> Description: - " .$description  . "<br/> Image -: " . $image;        // var_dump($meta);


        return response()->json([
            "status" => true,
            "title" => $title,
            "description" => $description,
            "image" => $image
        ], 201); 

    }

    public function view_the_post_comment_mod(Request $request){
        $post_id = $request->post_id;
        $user_id = $request->mobile_user_id;
        $start = (!empty($request->start)) ? $request->start - 1 : 0;
        $end = (!empty($request->end)) ? $request->end - 1 : 10;
        // $comdata = array();

        $recom_like_ids = array();
        $most_like_id = array();
        $comments_count = Comment::where('review_id','=',$post_id)->count();
        if($comments_count > 5){
        
        $like_result_id = DB::select( DB::raw("SELECT c.id, count(p.id) as likes_count from `comments` c LEFT JOIN `comments_likes` p ON c.id = p.comment_id WHERE c.review_id = :review_id 
        GROUP BY c.id order by c.created_at desc limit 1"), array(
            'review_id' =>  $post_id,
          ));

        if(!empty($like_result_id)){
            foreach($like_result_id as $res){
            if($res->likes_count > 5){
                $recom_like_ids[] = $res->id;
                $most_like_id[] = $res->id;
                $liked_id = $res->id;
                // $most_comment_id["id"] = $res->id;
                $comments_likes = Comment::where('id','=',$res->id)->where('review_id','=', $post_id)->orderBy('created_at', 'desc')->get();
                foreach($comments_likes as $data)
                { 
                    $comdata["id"] = $data->id;
                    $comdata["review_id"] = $data->review_id;
                    $comdata["content"] = $data->content; 
                    $comdata["created_at"] = $data->created_at->diffForHumans(); 
                    $comdata["mobile_user_id"] = $data->mobile_user_id;
                    
                    $mobile_user = DB::table('mobile_users')
                    ->select('id', 'full_name', 'user_name', 'email','profile_picture')
                    ->where('id','=', $data->mobile_user_id)
                    ->first();                    
                    
                    $comdata["mobile_full_name"] = (!empty($mobile_user->full_name)) ? $mobile_user->full_name : " ";
                    $comdata["mobile_user_name"] = (!empty($mobile_user->user_name)) ? $mobile_user->user_name : " ";
                    $comdata["mobile_email"] = (!empty($mobile_user->email)) ? $mobile_user->email : " ";
                    if(empty($mobile_user->profile_picture) || $mobile_user->profile_picture == " " || $mobile_user->profile_picture =="" ){
                        $comdata["mobile_profile_picture"] = "";  
                    } else
                        $comdata["mobile_profile_picture"] =  env('APP_URL')."/". str_replace("/var/www/html/review/public/","",$mobile_user->profile_picture) ;

                    $comdata["com_likes_count"] = comments_likes::where('comment_id',$data->id)->count();
                    $comdata["com_likes_status"] = comments_likes::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                    $comdata["com_agree_count"] = agree_comments::where('comment_id',$data->id)->count();
                    $comdata["com_agree_status"] = agree_comments::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                    $comdata["com_reply_count"] = SubComment::where('comment_id',$data->id)->count();
                    $com_like_contianer[] = $comdata;
                } 
            }else{
                $liked_id = 0;
                $com_like_contianer = [];
            }
        
            }
        }else{
            $com_like_contianer = [];
        }

        $agree_result_id = DB::select( DB::raw("SELECT c.id, count(p.id) as reply_count from `comments` c LEFT JOIN `sub_comments` p ON c.id = p.comment_id WHERE c.review_id = :review_id and not c.id = :liked_id 
        GROUP BY c.id order by c.created_at desc limit 1,1"), array(
              'review_id' =>  $post_id,
              'liked_id' => $liked_id
            
        ));  
        //echo implode("",$most_like_id);

        if(!empty($agree_result_id)){
            foreach($agree_result_id as $res){
                if($res->reply_count > 5){
                // $most_comment_id["id"] = $res->id;
                $recom_like_ids[] = $res->id;
                $comments_likes = Comment::where('id','=',$res->id)->where('review_id','=', $post_id)->whereNotIn('id',$most_like_id)->orderBy('created_at', 'desc')->get();
                foreach($comments_likes as $data)
                { 
                    $comdata["id"] = $data->id;
                    $comdata["review_id"] = $data->review_id;
                    $comdata["content"] = $data->content; 
                    $comdata["created_at"] = $data->created_at->diffForHumans(); 
                    $comdata["mobile_user_id"] = $data->mobile_user_id;

                    $mobile_user = DB::table('mobile_users')
                                            ->select('id', 'full_name', 'user_name', 'email','profile_picture')
                                            ->where('id','=', $data->mobile_user_id)
                                            ->first();                                            
                                            
                        $comdata["mobile_full_name"] = (!empty($mobile_user->full_name)) ? $mobile_user->full_name : " ";
                        $comdata["mobile_user_name"] = (!empty($mobile_user->user_name)) ? $mobile_user->user_name : " ";
                        $comdata["mobile_email"] = (!empty($mobile_user->email)) ? $mobile_user->email : " ";
                            if(empty($mobile_user->profile_picture) || $mobile_user->profile_picture == " " || $mobile_user->profile_picture =="" ){
                                $comdata["mobile_profile_picture"] = "";  
                            } else
                                $comdata["mobile_profile_picture"] =  env('APP_URL')."/". str_replace("/var/www/html/review/public/","",$mobile_user->profile_picture) ;
                        
                    $comdata["com_likes_count"] = comments_likes::where('comment_id',$data->id)->count();
                    $comdata["com_likes_status"] = comments_likes::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                    $comdata["com_agree_count"] = agree_comments::where('comment_id',$data->id)->count();
                    $comdata["com_agree_status"] = agree_comments::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                    $comdata["com_reply_count"] = SubComment::where('comment_id',$data->id)->count();
                    $com_agree_contianer[] = $comdata;
                }  
            }else{
                $com_agree_contianer = [];
            }  
        
            }
        }else{
            $com_agree_contianer = [];
        }
        }else{
            $com_agree_contianer = [];
            $com_like_contianer = [];
            $recom_like_ids[]= '';
        } 
        $comments = Comment::where('review_id','=', $post_id)->whereNotIn('id',$recom_like_ids)->orderBy('created_at', 'desc')
        ->skip($start)
        ->take($end)->get();

        $Comments_Count = count($comments);
        if($Comments_Count > 0){
        foreach($comments as $data)
                                        { 
                                            $comdata["id"] = $data->id;
                                            $comdata["review_id"] = $data->review_id;
                                            //if($comdata["created_at"] != null){
                                              $comdata["created_at"] = $data->created_at->diffForHumans();  //->diffForHumans();
                                            //}else
                                            ///$comdata["created_at"] = '';                                             
                                            $comdata["content"] = $data->content; 
                                            $comdata["mobile_user_id"] = $data->mobile_user_id;

                                            $mobile_user = DB::table('mobile_users')
                                            ->select('id', 'full_name', 'user_name', 'email','profile_picture')
                                            ->where('id','=', $data->mobile_user_id)
                                            ->first();                                            
                                            
                                            $comdata["mobile_full_name"] = (!empty($mobile_user->full_name)) ? $mobile_user->full_name : " ";
                                            $comdata["mobile_user_name"] = (!empty($mobile_user->user_name)) ? $mobile_user->user_name : " ";
                                            $comdata["mobile_email"] = (!empty($mobile_user->email)) ? $mobile_user->email : " ";
                                                if(empty($mobile_user->profile_picture) || $mobile_user->profile_picture == " " || $mobile_user->profile_picture =="" ){
                                                    $comdata["mobile_profile_picture"] = "";  
                                                } else
                                                    $comdata["mobile_profile_picture"] =  env('APP_URL')."/". str_replace("/var/www/html/review/public/","",$mobile_user->profile_picture) ;
                                          
                                            $comdata["com_likes_count"] = comments_likes::where('comment_id',$data->id)->count();
                                            $comdata["com_likes_status"] = comments_likes::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                                            $comdata["com_agree_count"] = agree_comments::where('comment_id',$data->id)->count();
                                            $comdata["com_agree_status"] = agree_comments::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                                            $comdata["com_reply_count"] = SubComment::where('comment_id',$data->id)->count();
                                            $comcontianer[] = $comdata;
                                        }    

                                }else{
                                    $comcontianer = [];
                                }  
                if(($com_agree_contianer == []) && ($com_like_contianer ==[]) && ($comcontianer ==[])){
                    $status = "false";
                }else{
                    $status = "true";
                }                                        
        return response()->json([
            "status" => $status,
            "post_id" => $post_id,
            "user_id" => $user_id,
            "top_recommended_comment" => $com_agree_contianer,
            "top_liked_comment" => $com_like_contianer,
            "comment_data" => $comcontianer
        ], 200); 
    }

    public function view_the_post_sub_comment(Request $request){

        $post_id = $request->post_id;
        $user_id = $request->mobile_user_id;

        $start = (!empty($request->start)) ? $request->start - 1 : 0;
        $end = (!empty($request->end)) ? $request->end - 1 : 10;

        $Subcomments = SubComment::where('comment_id','=', $post_id)
        ->skip($start)
        ->take($end)->get();
                                            $SubComments_Count = count($Subcomments);
                                            if($SubComments_Count > 0){
                                                foreach($Subcomments as $subdata){
                                                    $subcomdata["comment_id"] = $post_id;
                                                    $subcomdata["subcom_id"] = $subdata->id;
                                                    $subcomdata["review_id"] = $subdata->review_id;
                                                    $subcomdata["content"] = $subdata->content; 
                                                    $subcomdata["created_at"] = $subdata->created_at->diffForHumans();  
                                                    $subcomdata["mobile_user_id"] = $subdata->mobile_user_id;
                                                    
                                                        $mobile_user = DB::table('mobile_users')
                                                        ->select('id', 'full_name', 'user_name', 'email','profile_picture')
                                                        ->where('id','=', $subdata->mobile_user_id)
                                                        ->first();
                                                        
                                                        
                                                        $subcomdata["mobile_full_name"] =(!empty($mobile_user->full_name)) ? $mobile_user->full_name : " ";
                                                        $subcomdata["mobile_user_name"] = (!empty($mobile_user->user_name)) ? $mobile_user->user_name : " ";
                                                        $subcomdata["mobile_email"] = (!empty($mobile_user->email)) ? $mobile_user->email : " ";
                                                        if(empty($mobile_user->profile_picture) || $mobile_user->profile_picture == " " || $mobile_user->profile_picture =="" ){
                                                            $subcomdata["mobile_profile_picture"] = "";  
                                                        } else
                                                            $subcomdata["mobile_profile_picture"] =  env('APP_URL')."/". str_replace("/var/www/html/review/public/","",$mobile_user->profile_picture) ;

                                                    $subcomdata["subcom_likes_count"] = subcomments_likes::where('subcomment_id',$subdata->id)->count();
                                                    $subcomdata["subcom_likes_status"] = subcomments_likes::where('subcomment_id',$subdata->id)->where('mobile_user_id',$user_id)->count();
                                                    $subcomdata["subcom_agree_count"] = agree_subcomments::where('subcomment_id',$subdata->id)->count();
                                                    $subcomdata["subcom_agree_status"] = agree_subcomments::where('subcomment_id',$subdata->id)->where('mobile_user_id',$user_id)->count();
                                                    $subcomcontianer[] = $subcomdata;
                                                }
                                            }else{
                                                $subcomcontianer = [];
                                            }
                        return response()->json([
                                                "status" => true,
                                                "post_id" => $post_id,
                                                "user_id" => $user_id,
                                                "sub_comments" => $subcomcontianer
                                            ], 200);                         
    }
    public function view_the_post_comment(Request $request){
        $post_id = $request->post_id;
        $user_id = $request->mobile_user_id;
       // $comdata = array();

        $comments = Comment::where('review_id','=', $post_id)->orderBy('created_at', 'desc')->get();

        $like_result_id = DB::select( DB::raw("SELECT c.id from `comments` c LEFT JOIN `comments_likes` p ON c.id = p.comment_id WHERE c.review_id = :review_id 
        GROUP BY c.id order by c.created_at desc limit 1"), array(
            'review_id' =>  $post_id,
          ));

        $agree_result_id = DB::select( DB::raw("SELECT c.id from `comments` c LEFT JOIN `comments_likes` p ON c.id = p.comment_id WHERE c.review_id = :review_id 
        GROUP BY c.id order by c.created_at desc limit 1"), array(
              'review_id' =>  $post_id,
        ));  

        if(!empty($like_result_id)){
            foreach($like_result_id as $res){
                // $most_comment_id["id"] = $res->id;
                $comments_likes = Comment::where('id','=',$res->id)->where('review_id','=', $post_id)->orderBy('created_at', 'desc')->get();
                foreach($comments_likes as $data)
                { 
                    $comdata["id"] = $data->id;
                    $comdata["review_id"] = $data->review_id;
                    $comdata["content"] = $data->content; 
                    $comdata["created_at"] = $data->created_at; 
                    $comdata["mobile_user_id"] = $data->mobile_user_id;
                    
                    $mobile_user = DB::table('mobile_users')
                    ->select('id', 'full_name', 'user_name', 'email','profile_picture')
                    ->where('id','=', $data->mobile_user_id)
                    ->first();                    
                    
                    $comdata["mobile_full_name"] = (!empty($mobile_user->full_name)) ? $mobile_user->full_name : " ";
                    $comdata["mobile_user_name"] = (!empty($mobile_user->user_name)) ? $mobile_user->user_name : " ";
                    $comdata["mobile_email"] = (!empty($mobile_user->email)) ? $mobile_user->email : " ";
                    if(empty($mobile_user->profile_picture) || $mobile_user->profile_picture == " " || $mobile_user->profile_picture =="" ){
                        $comdata["mobile_profile_picture"] = "";  
                    } else
                        $comdata["mobile_profile_picture"] =  env('APP_URL')."/". str_replace("/var/www/html/review/public/","",$mobile_user->profile_picture) ;

                    $comdata["com_likes_count"] = comments_likes::where('comment_id',$data->id)->count();
                    $comdata["com_likes_status"] = comments_likes::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                    $comdata["com_agree_count"] = agree_comments::where('comment_id',$data->id)->count();
                    $comdata["com_agree_status"] = agree_comments::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                    $com_like_contianer[] = $comdata;
                }  
        
            }
        }else{
            $com_like_contianer = "No Top Recommended Comments";
        }

        if(!empty($agree_result_id)){
            foreach($agree_result_id as $res){
                // $most_comment_id["id"] = $res->id;
                $comments_likes = Comment::where('id','=',$res->id)->where('review_id','=', $post_id)->orderBy('created_at', 'desc')->get();
                foreach($comments_likes as $data)
                { 
                    $comdata["id"] = $data->id;
                    $comdata["review_id"] = $data->review_id;
                    $comdata["content"] = $data->content; 
                    $comdata["created_at"] = $data->created_at; 
                    $comdata["mobile_user_id"] = $data->mobile_user_id;

                    $mobile_user = DB::table('mobile_users')
                                            ->select('id', 'full_name', 'user_name', 'email','profile_picture')
                                            ->where('id','=', $data->mobile_user_id)
                                            ->first();                                            
                                            
                        $comdata["mobile_full_name"] = (!empty($mobile_user->full_name)) ? $mobile_user->full_name : " ";
                        $comdata["mobile_user_name"] = (!empty($mobile_user->user_name)) ? $mobile_user->user_name : " ";
                        $comdata["mobile_email"] = (!empty($mobile_user->email)) ? $mobile_user->email : " ";
                            if(empty($mobile_user->profile_picture) || $mobile_user->profile_picture == " " || $mobile_user->profile_picture =="" ){
                                $comdata["mobile_profile_picture"] = "";  
                            } else
                                $comdata["mobile_profile_picture"] =  env('APP_URL')."/". str_replace("/var/www/html/review/public/","",$mobile_user->profile_picture) ;
                        
                    $comdata["com_likes_count"] = comments_likes::where('comment_id',$data->id)->count();
                    $comdata["com_likes_status"] = comments_likes::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                    $comdata["com_agree_count"] = agree_comments::where('comment_id',$data->id)->count();
                    $comdata["com_agree_status"] = agree_comments::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                    $com_agree_contianer[] = $comdata;
                }  
        
            }
        }else{
            $com_agree_contianer = "No Agree Comment";
        }
          
        $Comments_Count = count($comments);
        if($Comments_Count > 0){
        foreach($comments as $data)
                                        { 
                                            $comdata["id"] = $data->id;
                                            $comdata["review_id"] = $data->review_id;
                                            $comdata["created_at"] = $data->created_at;                                             
                                            $comdata["content"] = $data->content; 
                                            $comdata["mobile_user_id"] = $data->mobile_user_id;

                                            $mobile_user = DB::table('mobile_users')
                                            ->select('id', 'full_name', 'user_name', 'email','profile_picture')
                                            ->where('id','=', $data->mobile_user_id)
                                            ->first();                                            
                                            
                                            $comdata["mobile_full_name"] = (!empty($mobile_user->full_name)) ? $mobile_user->full_name : " ";
                                            $comdata["mobile_user_name"] = (!empty($mobile_user->user_name)) ? $mobile_user->user_name : " ";
                                            $comdata["mobile_email"] = (!empty($mobile_user->email)) ? $mobile_user->email : " ";
                                                if(empty($mobile_user->profile_picture) || $mobile_user->profile_picture == " " || $mobile_user->profile_picture =="" ){
                                                    $comdata["mobile_profile_picture"] = "";  
                                                } else
                                                    $comdata["mobile_profile_picture"] =  env('APP_URL')."/". str_replace("/var/www/html/review/public/","",$mobile_user->profile_picture) ;
                                          
                                            $comdata["com_likes_count"] = comments_likes::where('comment_id',$data->id)->count();
                                            $comdata["com_likes_status"] = comments_likes::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                                            $comdata["com_agree_count"] = agree_comments::where('comment_id',$data->id)->count();
                                            $comdata["com_agree_status"] = agree_comments::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                                            $Subcomments = SubComment::where('comment_id','=', $data->id)->get();
                                            $SubComments_Count = count($Subcomments);
                                            if($SubComments_Count > 0){
                                                foreach($Subcomments as $subdata){
                                                    $subcomdata["comment_id"] = $data->id;
                                                    $subcomdata["subcom_id"] = $subdata->id;
                                                    $subcomdata["review_id"] = $subdata->review_id;
                                                    $subcomdata["content"] = $subdata->content; 
                                                    $subcomdata["created_at"] = $data->created_at;  
                                                    $subcomdata["mobile_user_id"] = $subdata->mobile_user_id;
                                                    
                                                        $mobile_user = DB::table('mobile_users')
                                                        ->select('id', 'full_name', 'user_name', 'email','profile_picture')
                                                        ->where('id','=', $subdata->mobile_user_id)
                                                        ->first();
                                                        
                                                        
                                                        $subcomdata["mobile_full_name"] =(!empty($mobile_user->full_name)) ? $mobile_user->full_name : " ";
                                                        $subcomdata["mobile_user_name"] = (!empty($mobile_user->user_name)) ? $mobile_user->user_name : " ";
                                                        $subcomdata["mobile_email"] = (!empty($mobile_user->email)) ? $mobile_user->email : " ";
                                                        if(empty($mobile_user->profile_picture) || $mobile_user->profile_picture == " " || $mobile_user->profile_picture =="" ){
                                                            $subcomdata["mobile_profile_picture"] = "";  
                                                        } else
                                                            $subcomdata["mobile_profile_picture"] =  env('APP_URL')."/". str_replace("/var/www/html/review/public/","",$mobile_user->profile_picture) ;

                                                    $subcomdata["subcom_likes_count"] = subcomments_likes::where('subcomment_id',$subdata->id)->count();
                                                    $subcomdata["subcom_likes_status"] = subcomments_likes::where('subcomment_id',$subdata->id)->where('mobile_user_id',$user_id)->count();
                                                    $subcomdata["subcom_agree_count"] = agree_subcomments::where('subcomment_id',$subdata->id)->count();
                                                    $subcomdata["subcom_agree_status"] = agree_subcomments::where('subcomment_id',$subdata->id)->where('mobile_user_id',$user_id)->count();
                                                    $subcomcontianer[] = $subcomdata;
                                                }
                                            }else{
                                                $subcomcontianer = "No Sub Comments is there";
                                            }
                                            $comdata["subComments"] = $subcomcontianer;                                            
                                            unset($subcomcontianer);
                                            $comcontianer[] = $comdata;
                                        }    

                                }else{
                                    $comcontianer = "No Comments is there";
                                }                          
        return response()->json([
            "status" => true,
            "post_id" => $post_id,
            "user_id" => $user_id,
            "most_comment_agree" => $com_agree_contianer,
            "most_comments_likes" => $com_like_contianer,
            "comment_data" => $comcontianer
        ], 200); 

    }

    public function create_del_the_comment(Request $request){
        $review_id = $request->review_id;
        $user_id = $request->mobile_user_id;
        $content = $request->content;
        $comment_status = $request->comment_status;
        $comment_id = $request->id;


        if($comment_status == 1){

                $Comment = new Comment;
                $Comment->review_id = $request->review_id;
                $Comment->mobile_user_id = $request->mobile_user_id;
                $Comment->content = $request->content;
                $Comment->save();
                $commnet_id = $Comment->id;

                $comments = Comment::where('id','=',$commnet_id)->get();
                foreach($comments as $data)
                { 
                    $comdata["id"] = $data->id;
                    $comdata["review_id"] = $data->review_id;
                    $comdata["content"] = $data->content; 
                    $comdata["created_at"] = $data->created_at->diffForHumans(); 
                    $comdata["mobile_user_id"] = $data->mobile_user_id;

                    $mobile_user = DB::table('mobile_users')
                                            ->select('id', 'full_name', 'user_name', 'email','profile_picture')
                                            ->where('id','=', $data->mobile_user_id)
                                            ->first();                                            
                                            
                        $comdata["mobile_full_name"] = (!empty($mobile_user->full_name)) ? $mobile_user->full_name : " ";
                        $comdata["mobile_user_name"] = (!empty($mobile_user->user_name)) ? $mobile_user->user_name : " ";
                        $comdata["mobile_email"] = (!empty($mobile_user->email)) ? $mobile_user->email : " ";
                            if(empty($mobile_user->profile_picture) || $mobile_user->profile_picture == " " || $mobile_user->profile_picture =="" ){
                                $comdata["mobile_profile_picture"] = "";  
                            } else
                                $comdata["mobile_profile_picture"] =  env('APP_URL')."/". str_replace("/var/www/html/review/public/","",$mobile_user->profile_picture) ;
                        
                    $comdata["com_likes_count"] = comments_likes::where('comment_id',$data->id)->count();
                    $comdata["com_likes_status"] = comments_likes::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                    $comdata["com_agree_count"] = agree_comments::where('comment_id',$data->id)->count();
                    $comdata["com_agree_status"] = agree_comments::where('comment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                    $comdata["com_reply_count"] = SubComment::where('comment_id',$data->id)->count();
                    $com_data[] = $comdata;
                } 

                if($Comment){
                    return response()->json([
                        "status" => true,
                        "message" => "You have Created the Comment",
                        "data" =>  $com_data
                    ], 200);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "You have not Created the Comment"
                    ], 200);
                }
        }elseif($comment_status == 0){

            $Comment_del = Comment::where('id',$comment_id)->delete();
                if($Comment_del){
                    return response()->json([
                        "status" => true,
                        "message" => "Comment Like Deleted Successfully"
                    ], 200);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "Comment like Not Deleted Successfully."
                    ], 200);
                }
        }      
    }
 
    public function create_del_the_subcomment(Request $request){
        $review_id = $request->review_id;
        $user_id = $request->mobile_user_id;
        $content = $request->content;
        $comment_id = $request->comment_id;
        $comment_status = $request->comment_status;
        $subcomment_id = $request->id;


        if($comment_status == 1){

                $Comment = new SubComment;
                $Comment->comment_id = $request->comment_id;
                $Comment->review_id = $request->review_id;
                $Comment->mobile_user_id = $request->mobile_user_id;
                $Comment->content = $request->content;
                $Comment->save();

                $subcommnet_id = $Comment->id;

                $comments = SubComment::where('id','=',$subcommnet_id)->get();
                foreach($comments as $data)
                { 
                    $comdata["id"] = $data->id;
                    $comdata["comment_id"] = $data->comment_id;
                    $comdata["review_id"] = $data->review_id;
                    $comdata["content"] = $data->content; 
                    $comdata["created_at"] = $data->created_at->diffForHumans(); 
                    $comdata["mobile_user_id"] = $data->mobile_user_id;

                    $mobile_user = DB::table('mobile_users')
                                            ->select('id', 'full_name', 'user_name', 'email','profile_picture')
                                            ->where('id','=', $data->mobile_user_id)
                                            ->first();                                            
                                            
                        $comdata["mobile_full_name"] = (!empty($mobile_user->full_name)) ? $mobile_user->full_name : " ";
                        $comdata["mobile_user_name"] = (!empty($mobile_user->user_name)) ? $mobile_user->user_name : " ";
                        $comdata["mobile_email"] = (!empty($mobile_user->email)) ? $mobile_user->email : " ";
                            if(empty($mobile_user->profile_picture) || $mobile_user->profile_picture == " " || $mobile_user->profile_picture =="" ){
                                $comdata["mobile_profile_picture"] = "";  
                            } else
                                $comdata["mobile_profile_picture"] =  env('APP_URL')."/". str_replace("/var/www/html/review/public/","",$mobile_user->profile_picture) ;
                        
                    $comdata["com_likes_count"] = subcomments_likes::where('subcomment_id',$data->id)->count();
                    $comdata["com_likes_status"] = subcomments_likes::where('subcomment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                    $comdata["com_agree_count"] = agree_subcomments::where('subcomment_id',$data->id)->count();
                    $comdata["com_agree_status"] = agree_subcomments::where('subcomment_id',$data->id)->where('mobile_user_id',$user_id)->count();
                  //  $comdata["com_reply_count"] = SubComment::where('comment_id',$data->id)->count();
                    $com_data[] = $comdata;
                } 


                if($Comment){
                    return response()->json([
                        "status" => true,
                        "message" => "You have Created the Sub-Comment"
                    ], 200);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "You have not Created the Sub-Comment"
                    ], 200);
                }
        }elseif($comment_status == 0){

            $Comment_del = SubComment::where('id',$subcomment_id)->delete();
                if($Comment_del){
                    return response()->json([
                        "status" => true,
                        "message" => "Sub-Comment Like Deleted Successfully"
                    ], 200);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "Sub-Comment like Not Deleted Successfully."
                    ], 200);
                }
        }      
    }
    
    //function to like / unlike the comments by the mobile user api..
    public function like_del_the_comment(Request $request){

        $comment_id = $request->comment_id;
        $user_id = $request->mobile_user_id;
        $like_status = $request->like_status;

        $Comment_Like = comments_likes::where('comment_id',$comment_id)->where('mobile_user_id',$user_id)->get();
        $Com_like_Count = $Comment_Like->count();
    
        if($like_status == 1){
            
            if($Com_like_Count == 0 ){
                $likeComment = new comments_likes;
                $likeComment->comment_id = $request->comment_id;
                $likeComment->mobile_user_id = $request->mobile_user_id;
                $likeComment->save();

                if($likeComment){
                    return response()->json([
                        "status" => true,
                        "message" => "You have liked this Comment"
                    ], 200);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "No like this Comment is created yet."
                    ], 200);
                }
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "You Already Liked the Comments"
                ], 200);
            }

        }elseif($like_status == 0){

            if($Com_like_Count == 1 ){
                $Comments_like = comments_likes::where('comment_id',$comment_id)->where('mobile_user_id',$user_id)->delete();
                if($Comments_like){
                    return response()->json([
                        "status" => true,
                        "message" => "Comment Like Deleted Successfully"
                    ], 200);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "Comment like Not Deleted Successfully."
                    ], 200);
                }
            }elseif($Com_like_Count == 0 ){
                return response()->json([
                    "status" => false,
                    "message" => "Comment Not Liked atAll."
                ], 200);
            }    

        }
    }

     //function to like / unlike the Sub Comments by the mobile user api..
     public function like_del_the_subcomment(Request $request){

        $comment_id = $request->sub_comment_id;
        $user_id = $request->mobile_user_id;
        $like_status = $request->like_status;

        $Comment_Like = subcomments_likes::where('subcomment_id',$comment_id)->where('mobile_user_id',$user_id)->get();
        $Com_like_Count = $Comment_Like->count();
    
        if($like_status == 1){
            
            if($Com_like_Count == 0 ){
                $likeComment = new subcomments_likes;
                $likeComment->subcomment_id = $request->sub_comment_id;
                $likeComment->mobile_user_id = $request->mobile_user_id;
                $likeComment->save();

                if($likeComment){
                    return response()->json([
                        "status" => true,
                        "message" => "You have liked this Comment"
                    ], 200);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "No like this Comment is created yet."
                    ], 200);
                }
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "You Already Liked the Comments"
                ], 200);
            }

        }elseif($like_status == 0){

            if($Com_like_Count == 1 ){
                $Comments_like = subcomments_likes::where('subcomment_id',$comment_id)->where('mobile_user_id',$user_id)->delete();
                if($Comments_like){
                    return response()->json([
                        "status" => true,
                        "message" => "Comment Like Deleted Successfully"
                    ], 200);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "Comment like Not Deleted Successfully."
                    ], 200);
                }
            }elseif($Com_like_Count == 0 ){
                return response()->json([
                    "status" => false,
                    "message" => "Comment Not Liked atAll."
                ], 200);
            }    

        }
    }
 
    //function to Agree / Disagree the  Comments by the mobile user api..
    public function agree_disagree_the_comment(Request $request){

            $comment_id = $request->comment_id;
            $user_id = $request->mobile_user_id;
            $like_status = $request->like_status;
    
            $Comment_Like = agree_comments::where('comment_id',$comment_id)->where('mobile_user_id',$user_id)->get();
            $Com_like_Count = $Comment_Like->count();
        
            if($like_status == 1){
                
                if($Com_like_Count == 0 ){
                    $likeComment = new agree_comments;
                    $likeComment->comment_id = $request->comment_id;
                    $likeComment->mobile_user_id = $request->mobile_user_id;
                    $likeComment->save();
    
                    if($likeComment){
                        return response()->json([
                            "status" => true,
                            "message" => "You have Agreed this Comment"
                        ], 200);
                    }else{
                        return response()->json([
                            "status" => false,
                            "message" => "No Agreed this Comment is created yet."
                        ], 200);
                    }
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "You Already Agreed the Comments"
                    ], 200);
                }
    
            }elseif($like_status == 0){
    
                if($Com_like_Count == 1 ){
                    $Comments_like = agree_comments::where('comment_id',$comment_id)->where('mobile_user_id',$user_id)->delete();
                    if($Comments_like){
                        return response()->json([
                            "status" => true,
                            "message" => "Comment Agreed Deleted Successfully"
                        ], 200);
                    }else{
                        return response()->json([
                            "status" => false,
                            "message" => "Agreed like Not Deleted Successfully."
                        ], 200);
                    }
                }elseif($Com_like_Count == 0 ){
                    return response()->json([
                        "status" => false,
                        "message" => "Agreed Not Liked atAll."
                    ], 200);
                }    
    
            }
    }

     //function to agree_disagree_the_subcomment by the mobile user api..
     public function agree_disagree_the_subcomment(Request $request){

        $comment_id = $request->sub_comment_id;
        $user_id = $request->mobile_user_id;
        $like_status = $request->like_status;

        $Comment_Like = agree_subcomments::where('subcomment_id',$comment_id)->where('mobile_user_id',$user_id)->get();
        $Com_like_Count = $Comment_Like->count();
    
        if($like_status == 1){
            
            if($Com_like_Count == 0 ){
                $likeComment = new agree_subcomments;
                $likeComment->subcomment_id = $request->sub_comment_id;
                $likeComment->mobile_user_id = $request->mobile_user_id;
                $likeComment->save();

                if($likeComment){
                    return response()->json([
                        "status" => true,
                        "message" => "You have Agreed this SubComment"
                    ], 200);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "No Agreed this SubComment is created yet."
                    ], 200);
                }
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "You Already Agreed the SubComment"
                ], 200);
            }

        }elseif($like_status == 0){

            if($Com_like_Count == 1 ){
                $Comments_like = agree_subcomments::where('subcomment_id',$comment_id)->where('mobile_user_id',$user_id)->delete();
                if($Comments_like){
                    return response()->json([
                        "status" => true,
                        "message" => "SubComment Agreed Deleted Successfully"
                    ], 200);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "SubComment Agreed Not Deleted Successfully."
                    ], 200);
                }
            }elseif($Com_like_Count == 0 ){
                return response()->json([
                    "status" => false,
                    "message" => "SubComment Not Agreed atAll."
                ], 200);
            }    

        }
    }    
    
     // function to create the review (post) by the mobile user api...
     public function like_the_post(Request $request){

        $likeReviews = new Likes;
        $likeReviews->post_id = $request->post_id;
        $likeReviews->user_id = $request->mobile_user_id;
        $likeReviews->save();
       
        if($likeReviews){
              return response()->json([
                  "status" => true,
                  "message" => "You have liked this post"
              ], 200);
          }else{
              return response()->json([
                  "status" => false,
                  "message" => "No like this post is created yet."
              ], 200);
          }

    }
    // function to un-do the like post
    public function undo_like_the_post(Request $request){

        $post_id = $request->post_id;
        $user_id = $request->mobile_user_id;

        $Like = Likes::where('post_id',$post_id)->where('user_id',$user_id)->delete();
        if($Like){
            return response()->json([
                "status" => true,
                "message" => "Deleted Successfully"
            ], 200);
        }else{
            return response()->json([
                "status" => false,
                "message" => "Not Deleted Successfully."
            ], 200);
        }
    }
 
    // function to get the count of like posts.
    public function count_like_post(Request $request){
        $post_id = $request->post_id;
        $confirmed_likes = Likes::where('post_id', $post_id)->count();

        if($confirmed_likes > 0 ){
            return response()->json([
                "status" => true,
                "message" => "No of likes",
                "likes_count" => $confirmed_likes
            ], 200);
        }else{
            return response()->json([
                "status" => false,
                "message" => "No of likes",
                "likes_count" => 0
            ], 200);
        }
    }
    
    // function list of users liked the posts.
    public function users_liked_post(Request $request){
        $post_id = $request->post_id;
           
        $result = DB::table("likes as l")
        ->join("mobile_users as mu","l.user_id","=","mu.id")
        ->where('l.post_id',$post_id)
        ->select("mu.id", 
                "mu.user_name","mu.full_name","mu.email")
        ->get();     
        
        if(count($result) == 0){
            return response()->json([
                "status" => false,
                "message" => "No one liked this post"
            ], 200);  
        }else{

            foreach($result as $data)
                    {                                      
                      $eventdata["id"] = $data->id;
                      $eventdata["user_name"] = $data->user_name;  // $petani is a Std Class Object here
                      $eventdata["full_name"] = $data->full_name;
                      $eventdata["email"] = $data->email;
                      $eventcontianer[] = $eventdata;
                    }
            
            return response()->json([
                "status" => true,
                "message" => "User list liked this post",
                "userdetails" => $eventcontianer
            ], 201); 

        }
    }

    public function check_user_likepost(Request $request){
        $post_id = $request->post_id;
        $user_id = $request->mobile_user_id;

        $Like = Likes::where('post_id',$post_id)->where('user_id',$user_id)->count();

        if($Like == 0){
            return response()->json([
                "status" => true,
                "message" => "This post is not yet liked"
            ], 200); 
        }else{
            return response()->json([
                "status" => false,
                "message" => "This post is already liked by this user"
            ], 200); 
        }
    }


     // function to create the review (post) by the mobile user api...
     public function follow_user(Request $request){

        $follower_id  = $request->user_id;
        $user_id = $request->current_user_id; // current user id ..

        $followers_count = followers::where('user_id',$follower_id)->where('follower_id',$user_id)->count();
        
        if($followers_count == 0){
            $followers = new followers;
            $followers->user_id = $request->user_id;
            $followers->follower_id = $request->current_user_id; // current user id ..
            $followers->save();
        
            if($followers){
                return response()->json([
                    "status" => true,
                    "message" => "You are successfully following"
                ], 200);
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "You are not successfully following."
                ], 200);
            }
        }elseif($followers_count == 1 || $followers_count > 0 ){
            return response()->json([
                "status" => false,
                "message" => "You are already following."
            ], 200);
        }    
    }

    // function to get the list of followers by the user-id
    public function myfollower_list_user(Request $request){
        $user_id = $request->login_user_id;

        $followers_id = followers::join('mobile_users', 'mobile_users.id', '=', 'followers.follower_id')->where('user_id',$user_id)->get();

        $followers_count = $followers_id->count();

        if($followers_count > 0 ){
        $followers_ids = array();
            
        foreach($followers_id as $data){
            $followers_ids[] = $data->follower_id;

            $followers = DB::table('mobile_users')
            ->select ('mobile_users.id', 'mobile_users.full_name', 'mobile_users.user_name', 
            'mobile_users.email', 'mobile_users.phone_no', 'mobile_users.profile_picture') 
            ->where('mobile_users.id' , $data->follower_id)->get();

            foreach($followers as $data)
            {                                      
                    $postdata["mobile_user_id"] = $data->id;
                    // $petani is a Std Class Object here
                    $postdata["full_name"] = $data->full_name;
                    $postdata["user_name"] = $data->user_name;
                   // $postdata["mobile_user_id"] = $data->mobile_user_id;
                    $postdata["email"] = $data->email;
                    $postdata["profile_picture"] = str_replace("/var/www/html/review/public/","http://139.59.76.151/",$data->profile_picture);
                    $postdata["phone_no"] = $data->phone_no;
                    $postdata["following_status"] = followers::where('user_id',$data->id)->where('follower_id',$user_id)->count();
                   
                    $followers_list[] = $postdata;
            }
    
        }
        return response()->json([
            "status" => true,
            "post_details" => $followers_list
            ], 200); 
        }else{
            return response()->json([
                "status" => false,
                "message" => "No Followers is found"
                ], 200);
        }                 
    }

     // function to get the list of followers by the user-id
     public function myfollowing_list_user(Request $request){
        $user_id = $request->login_user_id;

        $followers_id = followers::join('mobile_users', 'mobile_users.id', '=', 'followers.user_id')->where('follower_id',$user_id)->get();

        $followers_count = $followers_id->count();

        if($followers_count > 0 ){
        $foll_ids = array();
        $followers_ids = array_unique($foll_ids);
            
        foreach($followers_id as $data){
            $followers_ids[] = $data->user_id;

            $followers = DB::table('mobile_users')
            ->select ('mobile_users.id', 'mobile_users.full_name', 'mobile_users.user_name', 
            'mobile_users.email', 'mobile_users.phone_no', 'mobile_users.profile_picture') 
            ->where('mobile_users.id' , $data->user_id)->get();

            foreach($followers as $data)
            {                                      
                    $postdata["mobile_user_id"] = $data->id;
                    // $petani is a Std Class Object here
                    $postdata["full_name"] = $data->full_name;
                    $postdata["user_name"] = $data->user_name;
                   // $postdata["mobile_user_id"] = $data->mobile_user_id;
                    $postdata["email"] = $data->email;
                    $postdata["profile_picture"] = str_replace("/var/www/html/review/public/","http://139.59.76.151/",$data->profile_picture);
                    $postdata["phone_no"] = $data->phone_no;
                    $postdata["following_status"] = followers::where('user_id',$data->id)->where('user_id',$user_id)->count();
                   
                    $followers_list[] = $postdata;
            }
    
        }
        return response()->json([
            "status" => true,
            "post_details" => $followers_list
            ], 200); 
        }else{
            return response()->json([
                "status" => false,
                "message" => "No Followers is found"
                ], 200);
        }                 
    }   

        // function to get the list of followers by the user-id
        public function otherfollower_list_user(Request $request){
            $user_id = $request->login_user_id;
            $follower_id = $request->followers_id;
    
            $followers_id = followers::where('user_id',$follower_id)->get();
            
            $followers_count = $followers_id->count();

            if($followers_count > 0 ){
            $followers_ids = array();
                
            foreach($followers_id as $data){
                $followers_ids[] = $data->follower_id;
    
                $followers = DB::table('mobile_users')
                ->select ('mobile_users.id', 'mobile_users.full_name', 'mobile_users.user_name', 
                'mobile_users.email', 'mobile_users.phone_no', 'mobile_users.profile_picture') 
                ->where('mobile_users.id' , $data->follower_id)
                ->where('mobile_users.id', '!=' , $follower_id)
                ->get();
    
                foreach($followers as $data)
                {                                      
                        $postdata["mobile_user_id"] = $data->id;
                        // $petani is a Std Class Object here
                        $postdata["full_name"] = $data->full_name;
                        $postdata["user_name"] = $data->user_name;
                       // $postdata["mobile_user_id"] = $data->mobile_user_id;
                        $postdata["email"] = $data->email;
                        $postdata["profile_picture"] = str_replace("/var/www/html/review/public/","http://139.59.76.151/",$data->profile_picture);
                        $postdata["phone_no"] = $data->phone_no;
                        $postdata["other_following_status_current"] = followers::where('user_id', $data->id)->where('follower_id',$follower_id)->count();
                        $postdata["following_status_current"] = followers::where('user_id',$data->id  )->where('follower_id',$user_id)->count();
                       // $postdata["following_status_current"] = followers::where('user_id',$data->id)->where('follower_id',$user_id)->count();
                       // $postdata["other_following_status_current"] = followers::where('user_id', $data->id)->where('follower_id',$follower_id)->count();
                        $followers_list[] = $postdata;
                }
        
            }
            return response()->json([
                "status" => true,
             //   "follower_id" => $followers_ids,
                "post_details" => $followers_list
                ], 200);     
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "No Followers is found"
                    ], 200);
            }             
        }


         // function to get the list of followers by the user-id
         public function otherfollowing_list_user(Request $request){
            $user_id = $request->login_user_id;
            $follower_id = $request->followers_id;
    
            $followers_id = followers::where('follower_id',$follower_id)->get();
            
            $followers_count = $followers_id->count();

            if($followers_count > 0 ){
            $followers_ids = array();
                
            foreach($followers_id as $data){
                $followers_ids[] = $data->user_id;
    
                $followers = DB::table('mobile_users')
                ->select ('mobile_users.id', 'mobile_users.full_name', 'mobile_users.user_name', 
                'mobile_users.email', 'mobile_users.phone_no', 'mobile_users.profile_picture') 
                ->where('mobile_users.id' , $data->user_id)
                ->where('mobile_users.id', '!=' , $follower_id)
                ->get();
    
                foreach($followers as $data)
                {                                      
                        $postdata["mobile_user_id"] = $data->id;
                        // $petani is a Std Class Object here
                        $postdata["full_name"] = $data->full_name;
                        $postdata["user_name"] = $data->user_name;
                       // $postdata["mobile_user_id"] = $data->mobile_user_id;
                        $postdata["email"] = $data->email;
                        $postdata["profile_picture"] = str_replace("/var/www/html/review/public/","http://139.59.76.151/",$data->profile_picture);
                        $postdata["phone_no"] = $data->phone_no;
                       //  $postdata["following_status"] = followers::where('user_id', $follower_id)->where('follower_id',$data->id)->count();
                        $postdata["following_status_current"] = followers::where('user_id',$data->id)->where('follower_id',$user_id)->count();
                        $postdata["other_following_status_current"] = followers::where('user_id', $data->id)->where('follower_id',$follower_id)->count();
                        $followers_list[] = $postdata;
                }
        
            }
            return response()->json([
                "status" => true,
                "post_details" => $followers_list
                ], 200);     
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "No Followers is found"
                    ], 200);
            }             
        }
        
    // function to create the review (post) by the mobile user api...
    public function unfollow_user(Request $request){

            $user_id = $request->user_id;
            $follower_id = $request->current_user_id; // current user id ..
    
            $followers_count = followers::where('user_id',$user_id)->where('follower_id',$follower_id)->count();
            
            if($followers_count == 1){
              $followers = followers::where('user_id',$user_id)->where('follower_id',$follower_id)->delete();
                if($followers){
                    return response()->json([
                        "status" => true,
                        "message" => "You are successfully un-following"
                    ], 200);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "You are not successfully un-following."
                    ], 200);
                }
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "You are already unfollowing."
                ], 200);
            }    
    }

    public function get_post_hashtags(Request $request){
        $hashtags = $request->hashtags;

        $review_hashtags_counts = Review::where('hashtags', 'LIKE', '%'.$hashtags.'%')->count();

        if($review_hashtags_counts > 0){

            $post_review = Review::where('hashtags', 'LIKE', '%'.$hashtags.'%')->get();

            foreach($post_review as $data)
            {                                      
                    $postdata["id"] = $data->id;
                    $postdata["name"] = $data->name;  // $petani is a Std Class Object here
                    $postdata["hashtags"] = $data->hashtags;
                    $postdata["mobile_user_id"] = $data->mobile_user_id;
                    $postdata["description"] = $data->description;
                    $postdata["image"] =  env('APP_URL')."/". str_replace("/var/www/html/review/public/","",$data->image);
                 //   $postdata["image"] = str_replace("/var/www/html/review/public/","http://139.59.76.151/",$data->image);
                    $postdata["rating"] = $data->rating;
                    $postdata["shorturl"] = $data->shorturl;
                    $postdata["lat"] = $data->lat;
                    $postdata["long"] = $data->long;
                    $postdata["usr_lat"] = $data->usr_lat;
                    $postdata["usr_long"] = $data->usr_long;
                    $postdata["created_at"] = $data->created_at; 
                    $postcontianer[] = $postdata;
            }
    
                return response()->json([
                "status" => true,
                "post_details" => $postcontianer
                ], 200); 

        }else{
            return response()->json([
                "status" => false,
                "message" => "No Post reveiw is not there in this hashtags."
                ], 200); 
        }
    }
    public function listPostApi(Request $request){

        $user_id = $request->user_id;
        $type = $request->type;
        $start = (!empty($request->start)) ? $request->start - 1 : 0;
        $end = (!empty($request->end)) ? $request->end - 1 : 10;

        if($type == "category" ){
            $category_id = $request->category_id;
            $start = $request->start;
            $end = $request->end;

            $postdata = array();
            
            if( $category_id > 0){
                $post_reviews_count = Review::where('categorie_id', $category_id)->count();
                if( $post_reviews_count == 0){
                    return response()->json([
                        "status" => false,
                        "message" => "No Post review is there in this categories"
                        ], 200); 
                }
                $post_review = Review::where('categorie_id', $category_id)
                ->skip($start)
                ->take($end)
                ->orderBy('id', 'desc')
                ->get();               
            }else{  
                $post_review = Review::skip($start)->take($end)->orderBy('id', 'desc')->get();
            }

        }

        if($type == "trending" ){

        $post_review = DB::table("reviews as r")
        ->join("trendings as tr","r.id","=","tr.review_id")
        ->skip($start)
        ->take($end)
        ->orderBy('r.id', 'desc')
        ->get();     
        
            if(count($post_review) == 0){
                return response()->json([
                    "status" => false,
                    "message" => "No one liked this post"
                ], 200);  
            }

        }    
        if($type == "at_the_rate" ){

            $post_review = Review::skip($start)
            ->take($end)
            ->orderBy('rating', 'desc')
            ->get();
        }

        if($type == "most_likes"){

            $result = Likes::groupBy('post_id')->select('post_id', DB::raw('count(post_id) as total'))
            ->skip($start)
            ->take($end)
            ->orderBy('total', 'desc')->get();
            $post_ids = array();
            
            foreach($result as $data){
                $post_ids[] = $data->post_id;
            }

            $post_review =  DB::table("reviews as rw")
                    ->join("likes as li","li.post_id","=","rw.id") 
                                   
                    ->whereIn('rw.id',$post_ids)
                    ->select(array('rw.*', DB::raw('COUNT(li.post_id) as post_likes_count')))
                    ->orderBy('post_likes_count','desc')
                //    ->orderBy(DB::raw('COUNT(li.post_id)','desc'))
                    ->groupBy("li.post_id")
                    ->get();

            // return $post_review;       
        }
 
        if($type == "most_viewed"){

            $result = Views::groupBy('post_id')->select('post_id', DB::raw('count(post_id) as total'))
            ->skip($start)
            ->take($end)
            ->orderBy('total', 'desc')->get();
            $post_ids = array();
            
            foreach($result as $data){
                $post_ids[] = $data->post_id;
            }

            $post_review =  DB::table("reviews as rw")
                    ->join("views as li","li.post_id","=","rw.id")
                    ->whereIn('rw.id',$post_ids)
                    ->select(array('rw.*', DB::raw('COUNT(li.post_id) as post_views_count')))
                    ->orderBy('post_views_count','desc')
                    ->groupBy("li.post_id")
                    ->get();

            // return $post_review;       
        }

        if(empty($post_review) || $post_review =='' || $post_review == " " || count($post_review) == 0) {

            return response()->json([
                "status" => false,
                "message" => "No Post is present in this limits"
                ], 200); 

        }
        foreach($post_review as $data)
        {                                      
                $postdata["id"] = $data->id;
                $postdata["name"] = $data->name;  // $petani is a Std Class Object here
                $postdata["hashtags"] = $data->hashtags;
                $postdata["mobile_user_id"] = $data->mobile_user_id;
                $postdata["description"] = $data->description;
                $postdata["image"] = str_replace("/var/www/html/review/public/","http://139.59.76.151/",$data->image);
                $postdata["rating"] = $data->rating;
                $postdata["shorturl"] = $data->shorturl;
                $postdata["lat"] = $data->lat;
                $postdata["long"] = $data->long;
                $postdata["usr_lat"] = $data->usr_lat;
                $postdata["usr_long"] = $data->usr_long;
                $postdata["created_at"] = $data->created_at;
                if($type == "most_likes"){
                   $postdata["post_likes_counts"] = $data->post_likes_count; 
                } 
                if($type == "most_viewed"){
                    $postdata['post_views_count'] = $data->post_views_count;
                }   
                $postcontianer[] = $postdata;
        }

        return response()->json([
        "status" => true,
        "post_details" => $postcontianer
        ], 200); 

    }
    
    public function User_details_By_id(Request $request) {

        $userid = $request->user_id;
        $userdata = array();
        $user_id_count = MobileUsers::where('id',$userid)->count();
        if($user_id_count == 1){
        $mobileUsers = MobileUsers::where('id', $userid)->get();

        $reviews_count = Review::where('mobile_user_id', $userid)->count();

        $followers_count = followers::join('mobile_users', 'mobile_users.id', '=', 'followers.follower_id')->where('user_id', $userid)->count();
        $followering_count = followers::join('mobile_users', 'mobile_users.id', '=', 'followers.user_id')->where('follower_id', $userid)->count();

        foreach($mobileUsers as $data)
                                    {                                      
                                      $userdata["id"] = $data->id;
                                      $userdata["full_name"] = $data->full_name;  // $petani is a Std Class Object here
                                      $userdata["email"] = $data->email;
                                      if(!empty($data->profile_picture)){
                                        $userdata["user_pic"] = env('APP_URL')."/". str_replace("/var/www/html/review/public/","",$data->profile_picture);
                                      }else{
                                        $userdata["user_pic"] = '';  
                                      }
                                      $userdata["username"] = $data->user_name;
                                      $userdata["phoneno"] = $data->phone_no;
                                      $userdata["active"] = $data->active;
                                      $userdata["createdat"] = $data->created_at;
                                      $userdata["post_review_count"] = $reviews_count;
                                      $userdata["followers_count"] = $followers_count;
                                      $userdata["followering_count"] = $followering_count;
                                    }
                                    
        return response()->json([
                "status" => true,
                "userdetails" => $userdata
        ], 201); 
    }else{
        return response()->json([
            "status" => false,
            "message" => "Userid not present in database"
    ], 201); 
    }  

    }

    public function Create_viewCount(Request $request) {

        $viewCounts = Views::where('post_id', $request->post_id)
                            ->where('user_id', $request->user_id)
                            ->count();
        
        if($viewCounts == 0 ){

            $postViews = new Views;
            $postViews->post_id = $request->post_id;
            $postViews->user_id = $request->user_id;
            $postViews->save();

            if($postViews){
                return response()->json([
                    "status" => true,
                    "message" => "You have Views this post"
                ], 200);
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "No Views this post yet."
                ], 200);
            }
        }else{
                return response()->json([
                    "status" => false,
                    "Count" => $viewCounts,
                    "message" => "Views already counted for this post"
                ], 200); 
        }
    }

    public function get_trending_post(Request $request){

       // $group_hashtags = DB::raw('group_concat(reviews.hashtags)');
        $user_id = $request->user_id;
        $group_hashtags = Review::select( DB::raw('CONCAT(hashtags) AS hashtags'))
           ->get();
       //    ->toArray();
        foreach($group_hashtags as $data){
            $hashtags[] = $data->hashtags;
        }

        //$res_hashtags = array_values( array_flip( array_flip( $hashtags ) ) );
   
        $hashtagsList = implode(',', $hashtags);
        $res_hashtags = explode(',', $hashtagsList);
        $res_hashtags = array_unique($res_hashtags);

        if( (array_count_values($res_hashtags)) > 0 ){

            foreach ($res_hashtags as $value) {
                $hash_tags_count = Review::where('hashtags', 'LIKE', '%'.$value.'%')->count(); 
                if($hash_tags_count > 1 && $value != '' ){
                $review_hashtags_counts[$value] = $hash_tags_count;
                }
              }
            
              //$result_hashtags_count = array_combine($res_hashtags,$review_hashtags_counts);
              
              // $rev_arr_res = array_reverse($result_hashtags_count, true);  
             if(empty($review_hashtags_counts)){
                    return response()->json([
                        "status" => false,
                        "message" => "No Tredening with not more than 1 hashtags"
                    ], 200);
             }

              $rev_arr_res = arsort($review_hashtags_counts);
 
              foreach($review_hashtags_counts as $key => $value){
                 $post_review =  DB::table("reviews as rw")
                 ->where('rw.hashtags', 'LIKE', '%'.$key.'%')
                 ->select(array('rw.*'))->get();

                 foreach($post_review as $data)
                    {                                      
                        $postcontianer_ids[] = $data->id;
                    }        
              }
     
                $post_review =  DB::table("reviews as rw")                   
                    ->whereIn('rw.id', $postcontianer_ids)
                    ->select(array('rw.*'))
                    ->orderBy('created_at','desc')
                    ->get();

                    foreach($post_review as $data)
                    {                                      
                            $postdata["id"] = $data->id;
                            $postdata["name"] = $data->name;  // $petani is a Std Class Object here
                            $postdata["hashtags"] = $data->hashtags;
                            $postdata["mobile_user_id"] = $data->mobile_user_id;
                            $postdata["description"] = $data->description;
                            $postdata["image"] = $data->image;
                            $postdata["rating"] = $data->rating;
                            $postdata["shorturl"] = $data->shorturl;
                            $postdata["lat"] = $data->lat;
                            $postdata["long"] = $data->long;
                            $postdata["likes_count"] = Likes::where('post_id', $data->id)->count();
                            $postdata["views_count"] = Views::where('post_id', $data->id)->count();
                            if($user_id > 0 ){
                                $postdata["user_like_status"] = Likes::where('post_id', $data->id)->where('user_id',$user_id)->count();  
                            }
                            $postdata["usr_lat"] = $data->usr_lat;
                            $postdata["usr_long"] = $data->usr_long;
                            $postdata["created_at"] = $data->created_at;  
                            $postcontianer[] = $postdata;
                    }  
                     

            return response()->json([
                "status" => true,
                // "post_details" => $postcontianer,
                "result_hashtags_count" => $review_hashtags_counts,
                "result_post_detials" => $postcontianer
               // "result_post_ids" => $postcontianer_ids
                ], 200); 

        } else{
            return response()->json([
                "status" => true,
                "message" => "No Trending Hashtags is available"
            ], 200);
        }
    }


    public function get_trending_post_new(Request $request){
        // $group_hashtags = DB::raw('group_concat(reviews.hashtags)');
        $user_id = $request->user_id;
        $date = Carbon::now()->subDays(300);
        $group_hashtags = Review::select( DB::raw('CONCAT(hashtags) AS hashtags'))
           ->where('created_at', '>=', $date)
           ->get();
       //    ->toArray();
       $hashtags_count = count($group_hashtags);
       if($hashtags_count > 0){
        foreach($group_hashtags as $data){
                $hashtags[] = $data->hashtags;

            }


            $hashtagsList = implode(',', $hashtags);
            $res_hashtags = explode(',', $hashtagsList);
            $res_hashtags = array_unique($res_hashtags);

            if( (array_count_values($res_hashtags)) > 0 ){

                foreach ($res_hashtags as $value) {
                    $hash_tags_count = Review::where('hashtags', 'LIKE', '%'.$value.'%')->where('created_at', '>=', $date)->count(); 
                    $other_hash_tags_count = Review::where('other_hashtags', 'LIKE', '%'.$value.'%')->where('created_at', '>=', $date)->count(); 
                    $comment_hash_count = Comment::where('content', 'LIKE', '%'.$value.'%')->where('created_at', '>=', $date)->count(); 
                    
                    // query to get the likes count values
                    $likes_count = DB::table('likes')
                    ->join('reviews', 'likes.post_id', '=', 'reviews.id')
                    ->select(DB::raw("count(likes.id)"))
                    ->where('reviews.hashtags', 'LIKE', '%'. $value .'%')
                    ->where('reviews.created_at', '>=', $date)->count(); 
                    
                    // query to get the views count values
                    $views_count = DB::table('views')
                    ->join('reviews', 'views.post_id', '=', 'reviews.id')
                    ->select(DB::raw("count(views.id)"))
                    ->where('reviews.hashtags', 'LIKE', '%'. $value .'%')
                    ->where('reviews.created_at', '>=', $date)->count(); 
                
                    
                    $total_hash_count = $hash_tags_count + $other_hash_tags_count + $comment_hash_count + $likes_count + $views_count;
                   // $total_hash_count = $likes_count;
                   
                   
                    if($total_hash_count > 2 && $value != '' ){
                    $review_hashtags_counts[$value] = $hash_tags_count;
                    $review_other_hashtag_counts[$value] = $other_hash_tags_count;
                    $review_comment_hashtag_counts[$value] = $comment_hash_count;
                    $review_total_hashtag_counts[$value] = $total_hash_count;
                    }
                  }
                
                  if(empty($review_total_hashtag_counts)){
                    return response()->json([
                        "status" => false,
                        "message" => "No Tredening with not more than 1 hashtags"
                    ], 200);
             }

              $rev_arr_res = arsort($review_total_hashtag_counts);  

            foreach($review_total_hashtag_counts as $key => $value){
                if($value > 2){
                    $post_review =  DB::table("reviews as rw")
                    ->where('rw.hashtags', 'LIKE', '%'.$key.'%')
                    ->select(array('rw.*'))->get();

                    foreach($post_review as $data)
                    {                                      
                        $postcontianer_ids[] = $data->id;
                    }  
                }          
             }
    
               $post_review =  DB::table("reviews as rw")                   
                   ->whereIn('rw.id', $postcontianer_ids)
                   ->select(array('rw.*'))
                   ->where('created_at', '>=', $date)
                   ->orderBy('created_at','desc')
                   ->get();

                   foreach($post_review as $data)
                   {                                      
                           $postdata["id"] = $data->id;
                           $postdata["name"] = $data->name;  // $petani is a Std Class Object here
                           $postdata["hashtags"] = $data->hashtags;
                           $postdata["other_hashtags"] = $data->other_hashtags;
                           $postdata["mobile_user_id"] = $data->mobile_user_id;
                           $postdata["description"] = $data->description;
                           $postdata["image"] = $data->image;
                           $postdata["rating"] = $data->rating;
                           $postdata["shorturl"] = $data->shorturl;
                           $postdata["lat"] = $data->lat;
                           $postdata["long"] = $data->long;
                           $postdata["likes_count"] = Likes::where('post_id', $data->id)->count();
                           $postdata["views_count"] = Views::where('post_id', $data->id)->count();
                           if($user_id > 0 ){
                               $postdata["user_like_status"] = Likes::where('post_id', $data->id)->where('user_id',$user_id)->count();  
                           }
                           $postdata["usr_lat"] = $data->usr_lat;
                           $postdata["usr_long"] = $data->usr_long;
                           $postdata["created_at"] = $data->created_at;  
                           $postcontianer[] = $postdata;
                   }  
                    

           return response()->json([
               "status" => true,
               // "post_details" => $postcontianer,
              // "result_hashtags_count" => $review_hashtags_counts,
               "result_total_hashtags_count" => $review_total_hashtag_counts,
              //  "result_post_detials" => $postcontianer
              // "result_post_ids" => $postcontianer_ids,
              // "result_total_hashtags_count_include_likes" => $total_hash_count_include_likes
               ], 200); 

            } else{
                return response()->json([
                    "status" => true,
                    "message" => "No Trending Hashtags is available"
                ], 200);
            }
        } else{
            return response()->json([
                "status" => true,
                "message" => "No Trending Hashtags is available"
            ], 200); 
        }         
    }
}
