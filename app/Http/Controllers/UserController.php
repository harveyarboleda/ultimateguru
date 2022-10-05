<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Ui\Presets\React;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

// token
use Illuminate\Support\Str;

// for upload file
use Illuminate\Support\Facades\Storage;
use Validator,Redirect,Response,File,Image;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }
    public function start() {
        $this->middleware('auth');

        if(Auth::check()) {
            if(Auth::user()->account_level == "Freelancer") {
                if(Auth::user()->is_verified == 1) {
                    $room = DB::table('user_session')
                    ->where('tutor_id', Auth::user()->id)
                    ->where('start_timestamp', 0)
                    ->where('status', 'Working')
                    ->get();

                    if($room->isEmpty()) {
                        // no data
                        //return redirect()->route('/');
                        $array = array(
                            'status' => 'Error!',
                            'code' => 203
                        );
                        echo json_encode($array);
                    } else {
                        db::table('activity_log')
                        ->insert([
                            'timestamp' => time(),
                            'user_id' => Auth::user()->id,
                            'content' => 'started the session.'
                        ]);

                        // data
                        DB::table('user_session')
                        ->where('tutor_id', Auth::user()->id)
                        ->where('status', 'Working')
                        ->update([
                            'start_timestamp' => time()
                        ]);
                        //echo 'success! / na-pause kasi yan kaya pwede mo ulit i-start';
                        $array = array(
                            'status' => 'Success!',
                            'code' => 200
                        );
                        echo json_encode($array);
                    }
                } else {
                    return redirect()->route('/');
                }
                
            } else {
                return redirect()->route('/');
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function pause() {
        $this->middleware('auth');

        if(Auth::check()) {
            if(Auth::user()->account_level == "Freelancer") {
                if(Auth::user()->is_verified == 1) {
                    $room = DB::table('user_session')
                    ->where('tutor_id', Auth::user()->id)
                    ->where('start_timestamp', '!=', 0)
                    ->where('status', 'Working')
                    ->get();

                    if($room->isEmpty()) {
                        // no data
                        //return redirect()->route('/');
                        $array = array(
                            'status' => 'Error!',
                            'code' => 203
                        );
                        echo json_encode($array);
                    } else {
                        // data

                        $timestamp = $room[0]->start_timestamp;
                        $end_timestamp = time();
                        $result = (($end_timestamp - $timestamp) / 60);
                        DB::table('user_session')
                        ->where('tutor_id', Auth::user()->id)
                        ->where('status', 'Working')
                        ->update([
                            'minutes' => $room[0]->minutes + $result,
                            'start_timestamp' => 0
                        ]);
                        db::table('activity_log')
                        ->insert([
                            'timestamp' => time(),
                            'user_id' => Auth::user()->id,
                            'content' => 'paused the session.'
                        ]);
                        //echo 'success! / na-pause kasi yan kaya pwede mo ulit i-start';
                        $array = array(
                            'status' => 'Success!',
                            'code' => 200
                        );
                        echo json_encode($array);
                    }
                } else {
                    return redirect()->route('/');
                }
            } else {
                return redirect()->route('/');
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function end() {
        $this->middleware('auth');

        if(Auth::check()) {
            if(Auth::user()->account_level == "Freelancer") {
                if(Auth::user()->is_verified == 1) {
                    $room = DB::table('user_session')
                    ->where('tutor_id', Auth::user()->id)
                    ->where('status', 'Working')
                    ->where('minutes', '!=', '0')
                    ->get();

                    if($room->isEmpty()) {
                        // no data
                        //return redirect()->route('/');
                        $array = array(
                            'status' => 'Error!',
                            'code' => 203
                        );
                        echo json_encode($array);
                    } else {
                        // data

                        $timestamp = $room[0]->start_timestamp;
                        $end_timestamp = time();
                        $result = (($end_timestamp - $timestamp) / 60);
                        DB::table('user_session')
                        ->where('tutor_id', Auth::user()->id)
                        ->where('status', 'Working')
                        ->update([
                            'minutes' => $room[0]->minutes + $result,
                            'start_timestamp' => 0,
                            'status' => 'Completed'
                        ]);
                        db::table('activity_log')
                        ->insert([
                            'timestamp' => time(),
                            'user_id' => Auth::user()->id,
                            'content' => 'ended the session.'
                        ]);
                        //echo 'success! / na-pause kasi yan kaya pwede mo ulit i-start';
                        $array = array(
                            'status' => 'Success!',
                            'code' => 200
                        );
                        echo json_encode($array);
                    }
                } else {
                    return redirect()->route('/');
                }
            } else {
                return redirect()->route('/');
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function check() {
        $this->middleware('auth');

        if(Auth::check()) {
            if(Auth::user()->account_level == "Freelancer" || Auth::user()->account_level == "User") {
                if(
                    (Auth::user()->is_verified == 1 && Auth::user()->account_level == "Freelancer") ||
                    (Auth::user()->is_verified == 0 && Auth::user()->account_level == "User")
                ) {
                    $room = "";
                    $array = array();
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
                        $array = array(
                            'left' => 0,
                            'hasSession' => 'no'
                        );
                        
                        if(Auth::user()->account_level == "User") {
                            $data = DB::table('user_reserves')
                            ->where('user_id', Auth::user()->id)
                            ->get();
                            if($data->isEmpty()) {
                                
                            } else {
                                $array['has_reserve'] = 1;
                            }
                        }
                    } else {
                        $result = $room[0]->start_timestamp == 0 ? 
                            ($room[0]->overall_minutes - $room[0]->minutes) :
                            ($room[0]->overall_minutes - ($room[0]->minutes + ((time() - $room[0]->start_timestamp) / 60)));
                        $array = array();
                        
                        //(($room[0]->overall_minutes - $room[0]->minutes) - (()) >= 0 && $result == 0
                        //
                        if(
                            ((int) $room[0]->start_timestamp == 0 ? ($room[0]->overall_minutes - $room[0]->minutes) : ($room[0]->overall_minutes - ($room[0]->minutes + ((time() - $room[0]->start_timestamp) / 60)))) >= 0
                        ) {
                            $array = array(
                                'left' => $result,
                                'hasSession' => 'yes',
                                'timestamp' => $room[0]->start_timestamp,
                                'overall_minutes' => $room[0]->overall_minutes,
                                'minutes' => $room[0]->minutes,
                            );
                        } else {
                            $array = array(
                                'left' => $result,
                                'hasSession' => 'no',
                                'timestamp' => $room[0]->start_timestamp,
                                'overall_minutes' => $room[0]->overall_minutes,
                                'minutes' => $room[0]->minutes,
                            );
                            
                            DB::table('user_session')
                            ->where('user_session_id', $room[0]->user_session_id)
                            ->update([
                                'minutes' => $room[0]->overall_minutes,
                                'status' => 'Completed'
                            ]);
                        }
                        if(Auth::user()->account_level == "User") {
                            $datas = DB::table('user_session')
                            ->where('user_id', Auth::user()->id)
                            ->where('status', 'Working')
                            ->get();
                            if($datas->isEmpty()) {
                                
                            } else {
                                $datas2 = DB::table('category')
                                ->where('category_id', $datas[0]->category_id)
                                ->get();

                                $array['is_pay'] = $datas[0]->is_pay;
                                $array['overall_total'] = $datas2[0]->price;
                            }
                        }
                        //echo $room[0]->overall_minutes - $room[0]->minutes .' minutes have left!';
                    }

                    echo json_encode($array);
                } else {
                    return redirect()->route('/');
                }
                
            } else {
                return redirect()->route('/');
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function online() {
        $this->middleware('auth');
        if(Auth::check()) {
            if(Auth::user()->account_level == "Freelancer") {
                if(Auth::user()->is_verified == 1) {
                    $update = DB::table('users')
                    ->where('account_level', 'Freelancer')
                    ->where('id', Auth::user()->id)
                    ->get();

                    if($update[0]->is_online == 0) {
                        DB::table('users')
                        ->where('account_level', 'Freelancer')
                        ->where('id', Auth::user()->id)
                        ->update([
                            'is_online' => 1
                        ]);

                        $array = array(
                            'online' => 1
                        );
                        echo json_encode($array);
                    } else if($update[0]->is_online == 1) {
                        DB::table('users')
                        ->where('account_level', 'Freelancer')
                        ->where('id', Auth::user()->id)
                        ->update([
                            'is_online' => 0
                        ]);
                        $array = array(
                            'online' => 0
                        );
                        echo json_encode($array);
                    } else {
                        return redirect()->route('/');
                    }
                } else {
                    return redirect()->route('/');
                }
            } else {
                return redirect()->route('/');
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function payments(Request $request) {
        $this->middleware('auth');
        if(Auth::check()) {
            if(Auth::user()->account_level == "Freelancer") {
                if(Auth::user()->is_verified == 1) {
                    if($request->filter == "received" || $request->filter == "not received") {

                        $update = DB::table('payments as p')
                        ->leftJoin('users as customer', 'p.user_id', '=','customer.id')
                        ->leftJoin('category as cat', 'p.category_id', '=','cat.category_id')
                        ->leftJoin('user_session as session', 'p.user_session_id', '=','session.user_session_id')
                        ->where('p.tutor_id', Auth::user()->id)
                        ->where('p.status', $request->filter)
                        ->select('p.payments_id as id','customer.name','cat.category_name','cat.price','p.status','p.timestamp as timestamp')
                        ->get();
                        return $update;
                    } else if($request->filter == "all") {
                        $update = DB::table('payments as p')
                        ->leftJoin('users as customer', 'p.user_id', '=','customer.id')
                        ->leftJoin('category as cat', 'p.category_id', '=','cat.category_id')
                        ->leftJoin('user_session as session', 'p.user_session_id', '=','session.user_session_id')
                        ->where('p.tutor_id', Auth::user()->id)
                        ->select('p.payments_id as id','customer.name','cat.category_name','cat.price','p.status','p.timestamp as timestamp')
                        ->get();
                        return $update;
                    } else {
                        return redirect()->route('/');
                    }
                } else {
                    return redirect()->route('/');
                }
            } else {
                return redirect()->route('/');
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function student_available(Request $request) {
        $this->middleware('auth');
        if(Auth::check()) {
            if(Auth::user()->account_level == "Freelancer") {
                if(Auth::user()->is_verified == 1) {
                    $update = DB::table('user_reserves as ur')
                    ->leftJoin('users as customer', function($join){
                        $join->on('ur.user_id', '=','customer.id')
                        ->where('customer.account_level', '=','User');
                    })
                    ->leftJoin('category as cat', 'ur.category_id', '=','cat.category_id')
                    ->leftJoin('subjects as sub', 'ur.subjects_id', '=','sub.subjects_id')
                    ->select([
                        'ur.user_reserves_id as id',
                        'customer.id as customer_id',
                        'customer.name as name',
                        'cat.category_name as plan',
                        'cat.price as plan_price',
                        'cat.minutes as plan_minutes',
                        'sub.subject_name',
                        'ur.timestamp as timestamp',
                    ])
                    ->where('ur.tutor_id', Auth::user()->id)
                    ->get();
                    $count = 0;
                    foreach($update as $data) {
                        $db = db::table("user_reserves_tags as urt")
                        ->where('urt.user_reserves_id', '=', $data->id)
                        ->select(DB::raw("group_concat(name) as name"))
                        ->get();
                        $array = explode(',', $db[0]->name);

                        $update[$count]->tags = $array;
                        $count++;
                    }
                    return $update;
                } else {
                    return redirect()->route('/');
                }
            } else {
                return redirect()->route('/');
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function approve(Request $request) {
        $this->middleware('auth');
        if(Auth::check()) {
            if(Auth::user()->account_level == "Freelancer") {
                if(Auth::user()->is_verified == 1) {
                    $getData = db::table('user_reserves')
                    ->where('user_reserves_id', $request->id)
                    ->where('tutor_id', Auth::user()->id)
                    ->get();
                    
                    if($getData->isEmpty()) {
                        $array = array(
                            'code' => '203',
                            'status' => 'Error!',
                        );
    
                        return $array;
                    } else {
                        $getCategory = db::table('category')
                        ->where('category_id', $getData[0]->category_id)
                        ->get();

                        $insert = array(
                            'tutor_id'          => $getData[0]->tutor_id,
                            'user_id'           => $getData[0]->user_id,
                            'overall_minutes'   => $getCategory[0]->minutes,
                            'minutes'           => 0,
                            'start_timestamp'   => 0,
                            'category_id'       => $getData[0]->category_id,
                            'subjects_id'       => $getData[0]->subjects_id,
                            'status'            => 'Working',
                            'is_pay'            => 0
                        );
                        
                        db::table('user_session')
                        ->insert($insert);

                        db::table('user_reserves_tags')
                        ->where('user_reserves_id', $request->id)
                        ->delete();
                        
                        db::table('user_reserves')  
                        ->where('user_reserves_id', $request->id)
                        ->delete();

                        // remove all request of this tutor.
                        db::table('user_reserves')  
                        ->where('tutor_id', $getData[0]->tutor_id)
                        ->delete();

                        db::table('activity_log')
                        ->insert([
                            'timestamp' => time(),
                            'user_id' => Auth::user()->id,
                            'content' => 'approved the student available.'
                        ]);

                        $array = array(
                            'code' => '200',
                            'status' => 'Success!',
                        );

                        return $array;
                    }
                } else {
                    return redirect()->route('/');
                }
            } else {
                return redirect()->route('/');
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function decline(Request $request) {
        $this->middleware('auth');
        if(Auth::check()) {
            if(Auth::user()->account_level == "Freelancer") {
                if(Auth::user()->is_verified == 1) {
                    $getData = db::table('user_reserves')
                    ->where('user_reserves_id', $request->id)
                    ->where('tutor_id', Auth::user()->id)
                    ->get();
                    
                    if($getData->isEmpty()) {
                        $array = array(
                            'code' => '203',
                            'status' => 'Error!',
                        );
    
                        return $array;
                    } else {
                        db::table('user_reserves_tags')
                        ->where('user_reserves_id', $request->id)
                        ->delete();

                        db::table('user_reserves')
                        ->where('user_reserves_id', $request->id)
                        ->delete();

                        db::table('activity_log')
                        ->insert([
                            'timestamp' => time(),
                            'user_id' => Auth::user()->id,
                            'content' => 'declined the student available.'
                        ]);

                        $array = array(
                            'code' => '200',
                            'status' => 'Success!',
                        );

                        return $array;
                    }
                } else {
                    return redirect()->route('/');
                }
            } else {
                return redirect()->route('/');
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function findSubjects(Request $request) {
        $this->middleware('auth');
        if(Auth::check()) {
            if(Auth::user()->account_level == "User") {
                $getData = db::table('subjects')
                ->get();
                
                if($getData->isEmpty()) {
                    $array = array(
                        'code' => '203',
                        'status' => 'Error!',
                    );

                    return $array;
                } else {
                    return $getData;
                }
            } else {
                $array = array(
                    'code' => '203',
                    'status' => 'Error!',
                );
                return $array;
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function findPlan(Request $request) {
        $this->middleware('auth');
        if(Auth::check()) {
            if(Auth::user()->account_level == "User") {
                $getData = db::table('category')
                ->get();
                
                if($getData->isEmpty()) {
                    $array = array(
                        'code' => '203',
                        'status' => 'Error!',
                    );

                    return $array;
                } else {
                    return $getData;
                }
            } else {
                $array = array(
                    'code' => '203',
                    'status' => 'Error!',
                );
                return $array;
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function findSubjectsAvailable(Request $request) {
        $this->middleware('auth');
        if(Auth::check()) {
            if(Auth::user()->account_level == "User") {
                $getData = db::table('freelance_subjects as fs')
                ->leftJoin('users as tutor', function($join){
                    $join->on('fs.tutor_id', '=','tutor.id')
                    ->where('tutor.is_online', '=','1');
                })
                ->where('fs.subject_id', '=', $request->subject_id)
                ->where('tutor.is_online', '=', '1')
                ->select('fs.*','tutor.id as tutor_id', 'tutor.name')
                ->get();
                
                if($getData->isEmpty()) {
                    $array = array(
                        'code' => '203',
                        'status' => 'Error!',
                    );

                    return $array;
                } else {
                    $count = 0;
                    foreach($getData as $data) {
                        $db = db::table("freelance_subjects as fs")
                        ->leftJoin('subjects as s', function($join){
                            $join->on('fs.subject_id', '=','s.subjects_id');
                        })
                        ->where('fs.tutor_id', '=', $data->tutor_id)
                        ->select(DB::raw("group_concat(s.subject_name) as name"))
                        ->get();
                        $array = explode(',', $db[0]->name);

                        $getData[$count]->tags = $array;
                        $count++;
                    }

                    return $getData;
                }
            } else {
                $array = array(
                    'code' => '203',
                    'status' => 'Error!',
                );
                return $array;
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function choose(Request $request) {
        $data = $request->json()->all();
        $data = response()->json($data);
        $data = (array) $data->original;
        $data = (object) $data;

        $this->middleware('auth');
        if(Auth::check()) {
            if(Auth::user()->account_level == "User") {
                $getData = db::table('user_reserves')
                ->where('user_id', Auth::user()->id)
                ->get();

                if($getData->isEmpty()) {
                    
                    $checkOnline = db::table('users')
                    ->where('id', $data->tutor_id)
                    ->where('is_online', 1)
                    ->get();
                    if($checkOnline->isEmpty()) {
                        $array = array(
                            'code' => '203',
                            'status' => 'Error! / That tutor doesn\'t online.',
                        );
                        return $array;
                    } else {
                        $insert = array(
                            'tutor_id'      => $data->tutor_id,
                            'user_id'       => Auth::user()->id,
                            'category_id'   => $data->category_id,
                            'subjects_id'   => $data->subjects_id,
                            'timestamp'     => time()
                        );
                        
                        db::table('user_reserves')->insert($insert);

                        

                        if(count($data->tags) != 0) {
                            $getData2 = db::table('user_reserves')
                            ->where('tutor_id', $data->tutor_id)
                            ->where('user_id', Auth::user()->id)
                            ->get();

                            for($i = 0; $i < count($data->tags); $i++) {
                                $insert2 = array(
                                    'name' => $data->tags[$i],
                                    'user_id' => Auth::user()->id,
                                    'user_reserves_id' => $getData2[0]->user_reserves_id
                                );
                                db::table('user_reserves_tags')->insert($insert2);
                            }
                        }
                        
                        db::table('activity_log')
                        ->insert([
                            'timestamp' => time(),
                            'user_id' => Auth::user()->id,
                            'content' => 'chose the category.'
                        ]);

                        $array = array(
                            'code' => '200',
                            'status' => 'Success!',
                        );
                        return $array;
                    }
                    
                } else {
                    $array = array(
                        'code' => '203',
                        'status' => 'Error! / Already exist',
                    );
                    return $array;
                }
            } else {
                $array = array(
                    'code' => '203',
                    'status' => 'Error!',
                );
                return $array;
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function generateTransactionId(){
        mt_srand((double) microtime() * 10000);
        $charid = md5(uniqid(rand(), true));
        $c = unpack("C*", $charid);
        $c = implode("",$c);
        return substr($c, 0, 20);
    }
    public function successPlan(Request $request) {
        $this->middleware('auth');
        if(Auth::check()) {
            if(Auth::user()->account_level == "User") {
                
                $getData = db::table('user_session')
                ->where('user_id', Auth::user()->id)
                ->where('is_pay', 0)
                ->where('status', 'Working')
                ->get();

                if($getData->isEmpty()) {
                    $array = array(
                        'code' => '203',
                        'status' => 'Error!',
                    );
                    return $array;
                } else {
                     
                    if ($request->isMethod('post')) {
                        $data = $request->json()->all();
                        $data = response()->json($data);
                        $data = (array) $data->original;
                        $data = (object) $data;

                        //paypal
                        // show all errors
                        ini_set('display_errors', 1);
                        ini_set('display_startup_errors', 1);
                        error_reporting(E_ALL);
                        
                        // initialize CURL
                        $ch = curl_init();
                        
                        // set path to PayPal API to generate token
                        // remove "sandbox" from URL when in live
                        curl_setopt($ch, CURLOPT_URL, 'https://api-m.sandbox.paypal.com/v1/oauth2/token');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
                        // write your own client ID and client secret in following format:
                        // {client_id}:{client_secret}
                        curl_setopt($ch, CURLOPT_USERPWD, 'Aelsfb31JSCWfxFawi30PIVuqtoLr4Ek84-NffkGx3cjRdWZmKu9cKWA0-59dj07Eou-E8vIzVlIVArq:EAEebAXqDFfifWH_mS83E_1vcU8jRwg1d4PO3fwJO5_wsb89mPtZF0gz8Olf0uf8nm6dDZIHtmznBksz');
                        
                        // set headers
                        $headers = array();
                        $headers[] = 'Accept: application/json';
                        $headers[] = 'Accept-Language: en_US';
                        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        
                        // call the CURL request
                        $result = curl_exec($ch);
                        
                        // check if there is any error in generating token
                        if (curl_errno($ch))
                        {
                            echo json_encode([
                                "status" => "error",
                                "message" => curl_error($ch)
                            ]);
                            exit();
                        }
                        curl_close($ch);
                        
                        // the response will be a JSON string, so you need to decode it
                        $result = json_decode($result);
                        
                        // get the access token
                        $access_token = $result->access_token;
                        
                        // we only need the second part of orderID variable from client side
                        $payment_token_parts = explode("-", $data->orderID);
                        $payment_id = "";
                        
                        if (count($payment_token_parts) > 1)
                        {
                            $payment_id = $payment_token_parts[1];
                        }
                        
                        // initialize another CURL for verifying the order
                        $curl = curl_init();
                        
                        // call API and send the payment ID as parameter
                        curl_setopt($curl, CURLOPT_URL, 'https://api-m.sandbox.paypal.com/v2/checkout/orders/' . $payment_id);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                        
                        // set headers for this request, along with access token
                        $headers = array();
                        $headers[] = 'Content-Type: application/json';
                        $headers[] = 'Authorization: Bearer ' . $access_token;
                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                        
                        // executing the request
                        $result = curl_exec($curl);
                        
                        // check if there is any error
                        if (curl_errno($curl))
                        {
                            echo json_encode([
                                "status" => "error",
                                "message" => "Payment not verified. " . curl_error($curl)
                            ]);
                            exit();
                        }
                        curl_close($curl);
                        
                        // get the response JSON decoded
                        $result = json_decode($result);
                        
                        // you can use the following if statement to make sure the payment is verified
                        // if ($result->status == "COMPLETED")
                        
                        $getCat = db::table("category")
                        ->where("category_id", $getData[0]->category_id)
                        ->get();

                        // send the response back to client
                        $insert = array(
                            'transaction_id'    => $data->orderID,
                            'tutor_id'          => $getData[0]->tutor_id,
                            'user_id'           => $getData[0]->user_id,
                            'category_id'       => $getData[0]->category_id,
                            'user_session_id'       => $getData[0]->user_session_id,
                            'status'            => 'Not received',
                            'total'            => $getCat[0]->price,
                            'type'             => 0,
                            'timestamp' => time()
                        );
                        

                        db::table('payments')
                        ->insert($insert);

                        db::table('user_session')
                        ->where('user_id', Auth::user()->id)
                        ->where('is_pay', 0)
                        ->where('status', 'Working')
                        ->update(['is_pay' => 1]);
                        
                        return array(
                            "status" => "success",
                            "message" => "Payment verified.",
                            "result" => $result
                        );
                    } else {
                        $getCat = db::table("category")
                        ->where("category_id", $getData[0]->category_id)
                        ->get();

                        // googlepay
                        $insert = array(
                            'transaction_id'    => $this->generateTransactionId(),
                            'tutor_id'          => $getData[0]->tutor_id,
                            'user_id'           => $getData[0]->user_id,
                            'category_id'       => $getData[0]->category_id,
                            'user_session_id'       => $getData[0]->user_session_id,
                            'status'            => 'Not received',
                            'total'            => $getCat[0]->price,
                            'type'             => 1,
                            'timestamp' => time()
                        );
                        
                        db::table('payments')
                        ->insert($insert);

                        db::table('user_session')
                        ->where('user_id', Auth::user()->id)
                        ->where('is_pay', 0)
                        ->where('status', 'Working')
                        ->update(['is_pay' => 1]);
                        
                        $array = array(
                            'code' => '200',
                            'status' => 'Success!',
                        );
                        return $array;
                    }

                    db::table('activity_log')
                    ->insert([
                        'timestamp' => time(),
                        'user_id' => Auth::user()->id,
                        'content' => 'payed the transaction.'
                    ]);
                }

            } else {
                $array = array(
                    'code' => '203',
                    'status' => 'Error!',
                );
                return $array;
            }
        } else {
            return redirect()->route('/');
        }
    }
    public function cancelPlan(Request $request) {
        $this->middleware('auth');
        if(Auth::check()) {
            if(Auth::user()->account_level == "User") {
                
                $getData = db::table('user_session')
                ->where('user_id', Auth::user()->id)
                ->where('is_pay', 0)
                ->where('status', 'Working')
                ->get();

                if($getData->isEmpty()) {
                    $array = array(
                        'code' => '203',
                        'status' => 'Error!',
                    );
                    return $array;
                } else {
                    db::table('user_session')
                    ->where('user_id', Auth::user()->id)
                    ->where('is_pay', 0)
                    ->where('status', 'Working')
                    ->delete();

                    db::table('activity_log')
                    ->insert([
                        'timestamp' => time(),
                        'user_id' => Auth::user()->id,
                        'content' => 'canceled the transaction and find another tutor.'
                    ]);

                    $array = array(
                        'code' => '200',
                        'status' => 'Success!',
                    );
                    return $array;
                }
            } else {
                $array = array(
                    'code' => '203',
                    'status' => 'Error!',
                );
                return $array;
            }
        } else {
            return redirect()->route('/');
        }
    }
}