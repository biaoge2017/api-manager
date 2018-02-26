<?php
/**
 * 日志类
 */
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redis;
use App\Jobs\DiyLog;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $id;
    protected $msg = [
                'requester_ip'=>null,
                'name'=>null,
                'async_state'=>null,
                'backurl'=>null,
                'controller_name'=>null,
                'request_time'=>null,
                'response_time'=>null,
                'response_status'=>null,
                'msg'=>null,
    ];

    public function __construct($log)
    {
        $this->id = mt_rand(0,9999).microtime(true);

        foreach($log as $k=>$v){
            if(array_key_exists($k,$this->msg)){
                $this->msg[$k] = $v;
            }
        }
        Redis::hmset($this->id,$this->msg);
    }
    public function add_log($log)
    {
        foreach($log as $k=>$v) {
            if (array_key_exists($k, $this->msg)) {
                $old_msg = Redis::hget($this->id, "{$k}");
                Redis::hset($this->id, "{$k}", "{$old_msg}{$v}");
            }
        }
    }
    public function save_log()
    {
        dispatch(new DiyLog($this->id));
    }
    public function update_log($log)
    {    
        //var_dump($log);
        foreach($log as $k=>$v){
            if(array_key_exists($k,$this->msg)){
                Redis::hset($this->id,"{$k}","{$v}");
            }
        }

    }
    //
}
