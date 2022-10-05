<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Ui\Presets\React;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

// token
use Illuminate\Support\Str;

// for upload file
use Illuminate\Support\Facades\Storage;
use Validator,Redirect,Response,File,Image;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
        $this->middleware('auth');
        $this->middleware('guest');

        if(Auth::check()) {
            if(Auth::user()->account_level == 'User' || Auth::user()->account_level == "Freelancer") {
                if(
                    (Auth::user()->is_verified == 1 && Auth::user()->account_level == "Freelancer") ||
                    (Auth::user()->is_verified == 0 && Auth::user()->account_level == "User")
                ) {
                    $room = "";
                    if(Auth::user()->account_level == "User") {
                        $room = DB::table('user_session')
                        ->where('status', 'Working')
                        ->where('user_id', Auth::user()->id)
                        ->get();
                    } else if(Auth::user()->account_level == "Freelancer") {
                        $room = DB::table('user_session')
                        ->where('status', 'Working')
                        ->where('tutor_id', Auth::user()->id)
                        ->get();
                    }

                    if($room->isEmpty()) {
                        // no session
                        if(Auth::user()->account_level == "User") {
                            $data = DB::table('user_reserves')
                            ->where('user_id', Auth::user()->id)
                            ->get();
    
                            if($data->isEmpty()) {
                                return view('user.index');
                            } else {
                                return view('loading.index');
                            }
                            
                        } else if(Auth::user()->account_level == "Freelancer") {
                            return view('freelancer.index');
                        }
                    } else {
                        // has session
                        if(Auth::user()->account_level == "User") {
                            $datas = DB::table('user_session as ur')
                            ->where('user_id', Auth::user()->id)
                            ->where('status', 'Working')
                            ->leftJoin('category as cat', 'ur.category_id', '=','cat.category_id')
                            ->get();
                            if($datas->isEmpty()) {
                                
                            } else {
                                if($datas[0]->is_pay == 0) {
                                    return view('payments.index', [
                                        'datas' => $datas
                                    ]);
                                }
                            }
                        }

                        $hash = hash('sha256',$room[0]->user_session_id . " " . $room[0]->tutor_id . " " . $room[0]->user_id);

                        return view('room.index', [
                            'room_session' => $hash,
                            'minutes' =>  $room[0]->minutes,
                            'check' => $room[0]->start_timestamp
                        ]);
                    }
                } else if(Auth::user()->is_verified == 0 && Auth::user()->account_level == "Freelancer") {
                    // no resume
                    echo 'haha';
                }
            } else if(Auth::user()->account_level == 'Admin') {
                $user = DB::table('users')->where('account_level', 'User')->count();
                $freelancer = DB::table('users')->where('account_level', 'Freelancer')->where('is_verified', 0)->count();
                $freelancer2 = DB::table('users')->where('account_level', 'Freelancer')->where('is_verified', 1)->count();


                $business_revenue = DB::table('payments as p')
                ->where('status', 'Not received')
                ->leftJoin('category as cat', 'p.category_id', '=','cat.category_id')
                ->select([
                    'cat.price'
                ])
                ->sum(DB::raw('price * 0.20'));

                $user_revenue = DB::table('payments as p')
                ->where('status', 'Not received')
                ->leftJoin('category as cat', 'p.category_id', '=','cat.category_id')
                ->select([
                    'cat.price'
                ])
                ->sum('price');


                $business_revenue2 = DB::table('payments as p')
                ->where('status', 'Received')
                ->leftJoin('category as cat', 'p.category_id', '=','cat.category_id')
                ->select([
                    'cat.price'
                ])
                ->sum(DB::raw('price * 0.20'));

                $user_revenue2 = DB::table('payments as p')
                ->where('status', 'Received')
                ->leftJoin('category as cat', 'p.category_id', '=','cat.category_id')
                ->select([
                    'cat.price'
                ])
                ->sum('price');

                

                $freelancer2 = DB::table('users')->where('account_level', 'Freelancer')->where('is_verified', 1)->count();

                $month = date("F Y", time());
                $sessions = array();
                for($z = 0; $z < 6; $z++) {
                    $start_date = strtotime($month . ' first day of this month 00:00');
                    $end_date = strtotime($month . ' last day of this month 23:59');
                    $sessions[] = array($month, DB::table('user_session')
                    ->whereBetween('timestamp', [$start_date, $end_date])
                    ->count());
                    $month = date("F Y", strtotime($month . "-1 month"));
                }
                $online = DB::table('users')->where('is_online', '1')->where('account_level', 'Freelancer')->count();
                $users = DB::table('users')->where('account_level', 'User')->count();
                $freelancers = DB::table('users')->where('account_level', 'Freelancer')->count();

                return view('admin.index', [
                    'user' => $user,
                    'freelancer' => $freelancer,
                    'freelancer2' => $freelancer2,
                    'user_revenue' => $user_revenue,
                    'business_revenue' => $business_revenue,
                    'user_revenue2' => $user_revenue2,
                    'business_revenue2' => $business_revenue2,
                    'sessions' => $sessions,
                    'online' => $online,
                    'users' => $users,
                    'freelancers' => $freelancers
                ]);
            }
        } else {
            return view('home.index');
        }
    }
    
    public function about() {
        return view('home.about');
    }
    public function offer() {
        return view('home.offer');
    }
    public function job() {
        return view('home.job');
    }
    public function support() {
        return view('home.support');
    }
    public function register(Request $request) {
        if($request->isMethod('post')) {
            if($request->type) {
                if($request->type == "user") {
                    $rules = [
                        'name'  => 'required',
                        'email'  => 'required',
                        'password'  => 'required',
                        'repassword'  => 'required',
                    ];
                    
                    $validator = Validator::make($request->all(), $rules);
                    if($validator->fails()){
                        return array("status" => "Error! There\'s something wrong!.","code"=>"203");
                    }

                    request()->validate([
                        'name'  => 'required',
                        'email'  => 'required',
                        'password'  => 'required',
                        'repassword'  => 'required',
                    ]);

                    if($request->password == $request->repassword) {
                        $search = DB::table('users')
                        ->where('email', $request->email)
                        ->get();

                        if($search->isEmpty()) {
                            $array = array(
                                'name' => $request->name,
                                'email' => $request->email,
                                'password' => Hash::make($request->password),
                                'account_level' => 'Freelancer',
                                'created_at' => now(),
                                'updated_at' => now(),
                            );
                            DB::table('users')->insert($array);
                            $getData = array('status'=>'Successfully!','code'=>200);
                            return $getData;
                        } else {
                            $getData = array('status'=>'Error! Existing email.','code'=>403);
                            return $getData;
                        }
                    } else {
                        $getData = array('status'=>'Error! Password and Confirm Password doesn\'t match!','code'=>403);
                        return $getData;
                    }
                } else if($request->type == "freelancer") {
                    $rules = [
                        'name'  => 'required',
                        'email'  => 'required',
                        'file'  => 'required|mimes:doc,docx,pdf|max:2048',
                    ];
                    
                    $validator = Validator::make($request->all(), $rules);
                    if($validator->fails()){
                        return array("status" => "The photo/file must be a file of type: doc, docx, pdf. And It must not be greater than 2048 kilobytes.","code"=>"203");
                    }

                    if($request->file_length > 0) {
                        request()->validate([
                            'name'  => 'required',
                            'email'  => 'required',
                            'file'  => 'required|mimes:doc,docx,pdf,txt,png,jpg,jpeg,gif|max:2048',
                        ]);
                    }


                    if($files = $request->file('file')) {
                        //store file into document folder
                        $file = $files->store('public/uploads');
                        $convert = str_replace("public/uploads/","",$file);

                        $is_pdf = 2;
                        $type_pdf = array(
                            '.doc',
                            '.docx',
                            '.pdf'
                        );
                        foreach($type_pdf as $val) {
                            if (strpos($convert, $val) !== FALSE) {
                                // doc, docx, pdf
                                $is_pdf = 1;
                            }
                        }
                        $search = DB::table('users')
                        ->where('email', $request->email)
                        ->get();

                        if($search->isEmpty()) {
                            $array = array(
                                'name' => $request->name,
                                'email' => $request->email,
                                'account_level' => 'Freelancer',
                                'resume' => $convert,
                                'created_at' => now(),
                                'updated_at' => now(),
                            );
                            DB::table('users')->insert($array);
                            $getData = array('status'=>'Successfully!','code'=>200);
                            return $getData;
                        } else {
                            $getData = array('status'=>'Error! Existing email.','code'=>403);
                            return $getData;
                        }
                    }

                } else {
                    return redirect()->route('home');
                }
            }
        } else if($request->isMethod('get')) {
            if($request->type) {
                if($request->type == "user") {
                    return view('home.register-user');
                } else if($request->type == "freelancer") {
                    return view('home.register-freelancer');
                } else {
                    return redirect()->route('home');
                }
            } else {
                return redirect()->route('home');
            }
        }
    }
    public function set_password(Request $request) {
        if($request->isMethod('post')) {
            $data = $request->json()->all();
            $data = response()->json($data);
            $data = (array) $data->original;
            $data = (object) $data;

            $check = db::table("users")
            ->where('email', $data->email)
            ->get();
            if($check->isEmpty()) {
                $getData = array('status'=>'Error! Not exist email.','code'=>403);
                return $getData;
            } else {
                if($check[0]->activation_token == $data->token) {
                    if(strlen($data->token) != 0) {
                        db::table('users')
                        ->where('email', $data->email)
                        ->update(
                            [
                                'activation_token' => '',
                                'password' => Hash::make($data->password)
                            ]
                        );
                        $getData = array('status'=>'Successfully!','code'=>200);
                        return $getData;
                    } else {
                        $getData = array('status'=>'Error! There\'s something wrong!','code'=>403);
                        return $getData;
                    }
                } else {
                    $getData = array('status'=>'Error! Not same token','code'=>403);
                    return $getData;
                }
            }
        } else if($request->isMethod('get')) {
            return view('home.set-password');
        } else {
            return redirect()->route('home');
        }
    }
}