<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

// token
use Illuminate\Support\Str;

// for upload file
use Illuminate\Support\Facades\Storage;
use Validator,Redirect,Response,File,Image;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function list() {
        if(Auth::check()) {
            if(Auth::user()->account_level == 'Admin') {
                return view('admin.list', []);
            } else {
                return redirect()->route('home');
            }
        } else {
            return redirect()->route('home');
        }
    }
    public function getList(Request $request) {
        //->where('title', 'LIKE', "%{$search}%")
        //  ->orWhere('body', 'LIKE', "%{$search}%")
        $data = $request->json()->all();
        $data = response()->json($data);
        $data = (array) $data->original;
        $data = (object) $data;

        if(Auth::check()) {
            if(Auth::user()->account_level == 'Admin') {
                try {
                    $getData = array();
                    $search = $data->search;
                    $filter = $data->filter;
                    if($filter == "All") {
                        $getData = DB::table('users')
                        ->where(function($query) use ($search) {
                            $query->where('name', 'LIKE',  "%{$search}%")
                            ->orWhere('email', 'LIKE',  "%{$search}%");
                        })->get();
                    } else if($filter == "1") {
                        $getData = DB::table('users')
                        ->where('account_level', 'User')
                        ->where(function($query) use ($search) {
                            $query->where('name', 'LIKE',  "%{$search}%")
                            ->orWhere('email', 'LIKE',  "%{$search}%");
                        })->get();
                    } else if($filter == "2") {
                        $getData = DB::table('users')
                        ->where('account_level', 'Freelancer')
                        ->where('is_verified', 1)
                        
                        ->where(function($query) use ($search) {
                            $query->where('name', 'LIKE',  "%{$search}%")
                            ->orWhere('email', 'LIKE',  "%{$search}%");
                        })->get();

                    } else if($filter == "3") {
                        $getData = DB::table('users')
                        ->where('account_level', 'Freelancer')
                        ->where('is_verified', 0)
                        ->where(function($query) use ($search) {
                            $query->where('name', 'LIKE',  "%{$search}%")
                            ->orWhere('email', 'LIKE',  "%{$search}%");
                        })->get();
                    } else if($filter == "4") {
                        $getData = DB::table('users')
                        ->where('account_level', 'Admin')
                        ->where(function($query) use ($search) {
                            $query->where('name', 'LIKE',  "%{$search}%")
                            ->orWhere('email', 'LIKE',  "%{$search}%");
                        })->get();
                    } else {
                        $getData = array('status'=>'Error 1','code'=>403);
                    }
                    return $getData;
                } catch (\Throwable $th) {
                    return $th;
                }
            } else {
                return redirect()->route('home');
            }
        } else {
            return redirect()->route('home');
        }
    }
    public function payment(Request $request) {

        if(Auth::check()) {
            if(Auth::user()->account_level == 'Admin') {
                $getData = DB::table('users')
                ->where('account_level', 'Freelancer')
                ->where('id', $request->user_id)
                ->get();

                if($getData->isEmpty()) {
                    return view('admin.payment', [
                        'user_id' => ''
                    ]);
                } else {
                    return view('admin.payment', [
                        'user_id' => $request->user_id,
                        'name' => $getData[0]->name
                    ]);
                }
            } else {
                return redirect()->route('home');
            }
        } else {
            return redirect()->route('home');
        }
    }
    public function action(Request $request) {
        //->where('title', 'LIKE', "%{$search}%")
        //  ->orWhere('body', 'LIKE', "%{$search}%")
        $data = $request->json()->all();
        $data = response()->json($data);
        $data = (array) $data->original;
        $data = (object) $data;

        if(Auth::check()) {
            if(Auth::user()->account_level == 'Admin') {
                $getData = DB::table('users')
                ->where('account_level', 'Freelancer')
                ->where('id', $request->user_id)
                ->get();

                if($getData->isEmpty()) {
                    return redirect()->route('home');
                } else {
                    $array = array();
                    $hash = Hash::make(time());
                    $hash = (strlen($hash) > 17) ? substr($hash,4,20): $string;

                    if($getData[0]->is_verified == 0) {
                        db::table('users')
                        ->where('id', $request->user_id)
                        ->update(
                            [
                                'is_verified' => 1,
                                'activation_token' => $hash
                            ]
                        );
                        $array = array("status" => "Success!", "code" => 200);
                    } else if($getData[0]->is_verified == 1) {
                        db::table('users')
                        ->where('id', $request->user_id)
                        ->update(
                            [
                                'is_verified' => 0,
                                'activation_token' => $hash
                            ]
                        );
                        $array = array("status" => "Success!", "code" => 200);
                    }

                    return $array;
                }
            } else {
                return redirect()->route('home');
            }
        } else {
            return redirect()->route('home');
        }
    }
    public function getPayment(Request $request) {
        //->where('title', 'LIKE', "%{$search}%")
        //  ->orWhere('body', 'LIKE', "%{$search}%")
        $data = $request->json()->all();
        $data = response()->json($data);
        $data = (array) $data->original;
        $data = (object) $data;

        if(Auth::check()) {
            if(Auth::user()->account_level == 'Admin') {
                try {
                    $getData = array();
                    $user_id = $data->user_id;
                    $filter = $data->filter;
                    if($filter == "All") {
                        if($user_id) {
                            $getData = DB::table('payments as p')
                            ->leftJoin('users as ur', 'p.tutor_id', '=','ur.id')
                            ->where('ur.id', '=', $user_id)
                            ->select("p.*", "ur.name")
                            ->get();
                        } else  {
                            $getData = DB::table('payments as p')
                            ->leftJoin('users as ur', 'p.tutor_id', '=','ur.id')
                            ->select("p.*", "ur.name")
                            ->get();
                        }
                    } else if($filter == "1") {
                        if($user_id) {
                            $getData = DB::table('payments as p')
                            ->leftJoin('users as ur', 'p.tutor_id', '=','ur.id')
                            ->where('status', '=', 'received')
                            ->where('ur.id', '=', $user_id)
                            ->select("p.*", "ur.name")
                            ->get();
                        } else {
                            $getData = DB::table('payments as p')
                            ->leftJoin('users as ur', 'p.tutor_id', '=','ur.id')
                            ->where('status', '=', 'received')
                            ->select("p.*", "ur.name")
                            ->get();
                        }
                    } else if($filter == "2") {
                        if($user_id) {
                            $getData = DB::table('payments as p')
                            ->leftJoin('users as ur', 'p.tutor_id', '=','ur.id')
                            ->where('status', '=', 'Not received')
                            ->where('ur.id', '=', $user_id)
                            ->select("p.*", "ur.name")
                            ->get();
                        } else {
                            $getData = DB::table('payments as p')
                            ->leftJoin('users as ur', 'p.tutor_id', '=','ur.id')
                            ->where('status', '=', 'Not received')
                            ->select("p.*", "ur.name")
                            ->get();
                        }
                    } else {
                        $getData = array('status'=>'Error 1','code'=>403);
                    }
                    return $getData;
                } catch (\Throwable $th) {
                    return $th;
                }
            } else {
                return redirect()->route('home');
            }
        } else {
            return redirect()->route('home');
        }
    }
    public function successSalary(Request $request) {
        $this->middleware('auth');
        if(Auth::check()) {
            if(Auth::user()->account_level == "Admin") {
                
                $getData = db::table('payments')
                ->where('payments_id', $request->payments_id)
                ->where('status', 'Not received')
                ->get();

                if($getData->isEmpty()) {
                    $array = array(
                        'code' => '203',
                        'status' => 'Error!',
                    );
                    return $array;
                } else {
                    db::table('payments')
                    ->where('payments_id', $request->payments_id)
                    ->where('status', 'Not received')
                    ->update(['status' => 'received']);
                    
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
    public function show(Request $request) {  
        
        $is_type = 2;  
        $path = 'storage/uploads/'. $request->file;

        $type = array(
            '.pdf',
        );
        
        foreach($type as $val) {
            if (strpos($path, $val) !== FALSE) {
                // jpg, jpeg, gif and png
                $is_type = 1;
            }
        }

        
        if (!File::exists($path)) {

            abort(404);

        }

        $file = File::get($path);

        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        if($is_type == 1) {
            
            return $response;
        } else {
            return response()->download($path);
        }
    }
}