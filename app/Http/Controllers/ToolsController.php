<?php
/*
|------------------------------------------
|工具类 create by liusen at 2017/11/14
|------------------------------------------
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Log;

class ToolsController extends Controller{
  //curl请求
  public static function curl_request_with_header($url,$type="post",$data="",$headers)
  {
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
    curl_setopt($ch,CURLOPT_HEADER,0);
    curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);

    $type = strtolower($type);
    switch($type){
      case 'get':
          break;
      case 'post':
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
          break;
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  }

//无自定义header头的curl请求
  public static function curl_request_without_header($url,$type="post",$data="")
  {
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
    curl_setopt($ch,CURLOPT_HEADER,0);

    $type = strtolower($type);
    switch($type){
      case 'get':
          break;
      case 'post':
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
          break;
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  }

  public static function curl_request_with_timeLimit($url,$type="post",$data="")
  {
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
    curl_setopt($ch,CURLOPT_HEADER,0);
    curl_setopt($ch,CURLOPT_TIMEOUT,1);

    $type = strtolower($type);
    switch($type){
      case 'get':
          break;
      case 'post':
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
          break;
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  }

//测试用接口回调函数类
  public function getdata(Request $request)
  {
    $data = $request->all();
    $res = json_encode(array('code'=>0,'msg'=>'OK'));
    // Log::info($data);
    return $res;
  }

}
