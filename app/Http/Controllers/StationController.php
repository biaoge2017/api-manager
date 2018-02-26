<?php
/**
 * api分发中心站
 *    version 1.0
 *    create by liusen at 2018-1-4
 *    用于处理接口请求分发的主要类
 */
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Jobs\StationQue;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\LogController;
class StationController extends Controller
{
    protected $logmsg;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //验证分发中心
    public function main(Request $request)
    {
      $data = $request->all();
      $request_ip = $request->ip();
      $ntime = date("Y-m-d h:i:s",time());
      $safetyToken = $data['safetyToken'];
      if(!$this->vsafetyToken($safetyToken)){
        return $this->reportError(1);
      }
      if(empty($data)){
        return $this->reportError(4);
      }
      //接口名称 string
      $class_action = $data['base_info']['name'];

      if(!strpos($class_action,'@')){
        return $this->reportError(2);
      }
      $class_action = explode('@', $class_action);

      $class_name = $class_action[0];
      $action_name = $class_action[1];
      //是否异步 bool,默认为同步,默认的enuse值为false
      $async = isset($data['base_info']['async']) ? $data['base_info']['async']['enuse'] : false;
      if($async === "false") $async=false;
      //回调地址 string
      $back_url = isset($data['base_info']['async']['back_url']) ? $data['base_info']['async']['back_url'] : null;
      if($async && $back_url==null) return $this->reportError(2);
      //数据格式 string
      $data_type = isset($data['base_info']['data_type']) ? $data['base_info']['data_type'] : 'json';
      //请求数据 array
      $request_data = isset($data['data']) ? $data['data'] : null;
      $className = 'App\Http\Controllers';
      $className .= "\\$class_name";
      $className .= 'Controller';
      if(!class_exists($className)) return $this->reportError(5);
      $class = new $className;
      //日志第一式,开门见山
      $lg = new LogController(array());
     
        $logasync = $async ? 1 : 0;
        $lg->update_log(['controller_name'=>$className,
        'requester_ip'=>$request_ip,
        'name'=>$class_name,
        'async_state'=>$logasync,
        'request_time'=>$ntime,
        'backurl'=>$back_url
        ]);
       
      //判断是否异步
      if(!$async){
        
        $data = call_user_func(array($class,$action_name),$request_data);
        
        if($data){
          if($data=='void') return $this->reportError(5);
           //return $this->reportError(0,$data);
       
        }else{
          return $this->reportError(7);
        }
      }else{
       
        //触发异步队列
        dispatch(new StationQue($class,$action_name,$request_data,$back_url,$request_ip,$ntime,$className,$lg));
        return $this->reportError(6);
      }
     
     $this->logmsg ="[同步]-[来源ip:$request_ip]-[时间:$ntime]-[目标:$className@$action_name]-[结果:$data]";
     Log::info($this->logmsg);
      $time87 = date("Y-m-d h:i:s",time());
   
      $lg->update_log(['response_time'=>$time87,'msg'=>$data]);
      $lg->save_log();
        //$a=Redis::keys('*');
        //var_dump($a);
      return $this->reportError(0,$data);
    }

    //错误返回信息方法
    public function reportError($index,$data=null)
    {
      $report = array(
        '{"error":"0","msg":"OK","data":'.$data.'}',
        '{"error":"1","msg":"safetyToken is invalid","data":"'.$data.'"}',
        '{"error":"2","msg":"data format is invalid","data":"'.$data.'"}',
        '{"error":"3","msg":"request timeout","data":"'.$data.'"}',
        '{"error":"4","msg":"invalid Json data","data":"'.$data.'"}',
        '{"error":"5","msg":"invalid name","data":"'.$data.'"}',
        '{"error":"6","msg":"OK,正在执行,请等待返回","data":"'.$data.'"}',
        '{"error":"7","msg":"request is failed","data":"'.$data.'"}'
      );
      return $report[$index];
    }

    //验证safetyToken
    public function vsafetyToken($safetyToken)
    {
      $key = md5('buqukeji506');//923cfd0e6895e59d0881bc68bb58f362
      $salt = 'createbyliusen';
      $servKey = sha1($key.$salt);
      if(sha1($safetyToken.$salt) === $servKey){
        return 1;
      }else{
        return 0;
      }
    }
}
