<?php

namespace App\Http\Controllers;
use App\Models\MobileUsers;
use App\Models\followers;
use App\Models\Review;
use App\Models\MobileAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Plivo\RestClient;
use DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Helpers\AppHelper;

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

  // function to create the review (post) by the mobile user api...
    public function create_review(Request $request){

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
        $mobileReview->rating = $request->rating;
        $mobileReview->categorie_id = $request->categorie_id;
        $mobileReview->publish = "1";

        $mobileReview->save();
       
        if($mobileReview){
              return response()->json([
                  "status" => true,
                  "message" => "mobileReview record created"
              ], 201);
          }else{
              return response()->json([
                  "status" => false,
                  "message" => "mobileReview record not created"
              ], 201);
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
           // $image->move($destinationPath, $imageName);
            if( $image->move($destinationPath, $imageName) ){
                $file_path =$destinationPath."".$imageName;
                return response()->json([
                    "status" => 200,                         
                    "message" => "Image has been uploaded successfully",
                    "img_url" => $file_path,
                    "image_url" => env('APP_URL'). "/uploads/post_media/".$imageName
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

}
