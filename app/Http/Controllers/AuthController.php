<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Validator,Redirect,Response;
Use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\MobileUsers;
use App\Models\Review;
use Session;
use DB;
 
class AuthController extends Controller
{
 
    public function index()
    {
      if(Auth::check()){
        return Redirect::to("dashboard");
      }else{
        return view('login');
      } 
    }  

    public function swaggerlist(){
      if(Auth::check()){
        return view("swagger.index");
      }else{
        return view('login');
      }
    }

 
    public function registration()
    {
        return view('registration');
    }

    public function viewReview(){
        return view('viewreview');
    }
     
    public function postLogin(Request $request)
    {
        request()->validate([
        'email' => 'required',
        'password' => 'required',
        ]);
 
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            // Authentication passed...
            return redirect()->intended('dashboard');
        }
        return Redirect::to("login")->withSuccess('Oppes! You have entered invalid credentials');
    }
 
    public function postRegistration(Request $request)
    {  
        request()->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6',
        ]);
         
        $data = $request->all();
 
        $check = $this->create($data);
       
        return Redirect::to("dashboard")->withSuccess('Great! You have Successfully loggedin');
    }
     
    public function dashboard()
    {
 
      if(Auth::check()){
        $mobileUsers = MobileUsers::all();
        return view('dashboard', compact('mobileUsers'));
      }
       return Redirect::to("login")->withSuccess('Opps! You do not have access');
    }

    public function moblieUserDashboard(){

      if(Auth::check()){
        $mobileUsers = MobileUsers::all();
        return view('tables', compact('mobileUsers'));
      }
       return Redirect::to("login")->withSuccess('Opps! You do not have access');

    }
 
    public function create(array $data)
    {
      return User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password'])
      ]);
    }
     
    public function logout() {
        Session::flush();
        Auth::logout();
        return Redirect('login');
    }

    public function disp_review(){
        return view('display_review');
    }

    public function review_disp($id){

      $reviewdetail = Review::where('id',$id)->get();


      return view('reviewdetail', compact('reviewdetail','review_userid'));
    }
}