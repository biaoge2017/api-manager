<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class WatchController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function return_api_data(Request $request)
    {
        //暂不做分页
        if($request->isMethod('post')){
            $data = $request->all();
            $password = $data['password'];
//            $page = $data['page'];
//            if($page>3 || $page<0){
//                $page = 1;
//            }
            if($password == 'buqu506'){
//                $table_num = 20;
//                $skip = $table_num*($page-1);
                $data = DB::table('requester')->orderBy('id','desc')->take(60)->get();
//                $count = DB::table('requester')->count();
//                $data->count = $count;
                exit(json_encode($data));
            }
        }
    }
}
