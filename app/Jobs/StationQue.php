<?php
/**
 * 请求接口处理总队列
 */
namespace App\Jobs;

use Log;
use DB;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\LogController;

class StationQue extends Job
{
  public $tries = 5;


  protected $class;
  protected $action_name;
  protected $request_data;
  protected $back_url;
  protected $request_ip;
  protected $ntime;
  protected $className;
  protected $lg;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($class,$action_name,$request_data,$back_url,$request_ip,$ntime,$className,$lg)
    {
        $this->class=$class;
        $this->action_name = $action_name;
        $this->request_data = $request_data;
        $this->back_url = $back_url;
        $this->request_ip = $request_ip;
        $this->ntime = $ntime;
        $this->className=$className;
        $this->lg = $lg;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        //利用call_user_func调用函数
        $data = call_user_func(array($this->class,$this->action_name),$this->request_data);
        //如果调用函数不存在,就返回名称错误
        if($data=='void') $data = '{"error":"5","msg":"invalid name","data":""}';

        //尝试给回调地址发送数据,每两秒钟尝试一次
        $json = ToolsController::curl_request_without_header($this->back_url,$type="post",$data="$data");
        $remsg = json_decode($json,true);
        $res = $remsg['code'];
        if($res==200){
          $logmsg = "[异步]-[来源ip:$this->request_ip]-[时间:$this->ntime]-[目标:$this->className]-[回调地址:$this->back_url]-[尝试:1]-[回调结果:$json]";
          Log::info($logmsg);
          $time60 = date("Y-m-d h:i:s",time());
          $this->lg->update_log(['response_time'=>$time60,'msg'=>$json]);
        }
        if($res!=200){
          sleep(5);
          $json2 = ToolsController::curl_request_without_header($this->back_url,$type="post",$data="$data");
          $remsg2 = json_decode($json2,true);
          $res2 = $remsg2['code'];
          $logmsg = "[异步]-[来源ip:$this->request_ip]-[时间:$this->ntime]-[目标:$this->className]-[回调地址:$this->back_url]-[尝试:2]-[回调结果:$json]";
          Log::info($logmsg);
          $time68 = date("Y-m-d h:i:s",time());
          $this->lg->update_log(['response_time'=>$time68,'msg'=>$json]);
          if($res2!=200){
            sleep(5);
            $json3 = ToolsController::curl_request_without_header($this->back_url,$type="post",$data="$data");
            $remsg3 = json_decode($json3,true);
            $res3 = $remsg3['code'];
            $logmsg = "[异步]-[来源ip:$this->request_ip]-[时间:$this->ntime]-[目标:$this->className]-[回调地址:$this->back_url]-[尝试:3]-[回调结果:$json]";
            Log::info($logmsg);
            $time76 = date("Y-m-d h:i:s",time());
            $this->lg->update_log(['response_time'=>$time76,'msg'=>$json]);
            if($res3!=200){
              //三次失败就存到数据表中
              $now = date("Y-m-d h:i:s",time());
              $resdb = DB::table('back_request_failed')->insert(['back_url'=>$this->back_url,'data'=>$data,'time'=>$now]);
              if($resdb){
                $logmsg = "[异步]-[来源ip:$this->request_ip]-[时间:$this->ntime]-[目标:$this->className]-[回调地址:$this->back_url]-[尝试:失败入库]-[回调结果:$json]";
                Log::info($logmsg);
                $time84 = date("Y-m-d h:i:s",time());
                $this->lg->update_log(['response_time'=>$time84,'msg'=>$json]);
              }else{
                $logmsg = "[异步]-[来源ip:$this->request_ip]-[时间:$this->ntime]-[目标:$this->className]-[回调地址:$this->back_url]-[尝试:失败入库未成功]-[回调结果:$json]";
                Log::info($logmsg);
                $time89 = date("Y-m-d h:i:s",time());
                $this->lg->update_log(['response_time'=>$time89,'msg'=>$json]);
              }
            }
          }
        }
        $this->lg->save_log();
    }
}
