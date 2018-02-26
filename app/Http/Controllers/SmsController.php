<?php
/**
 * 短信接口类
 */
namespace App\Http\Controllers;
use App\Libs\aliyun\api_demo\SmsDemo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class SmsController extends Controller
{
    public function __construct()
    {
      //
    }

  

    //php魔术方法,当调用类内没有的方法时调用此方法
    public function __call($name,$args)
    {
      return 'void';
    }
    // public $tem_code=[
    // 'bqymd_register'=>'SMS_114310017',//注册
    // 'bqymd_reset'   =>'SMS_114310016'//修改密码
    // ];
    //接收参数 发送短信
      public function sendSms($request_data)
    {
      $phone=$request_data['phone'];
      $code=$request_data['code'];
      $tem_code=$request_data['tem_code'];
      $demo = new SmsDemo(
            APP_KEY,
            APP_SECRET
        );
        
        $response = $demo->sendSms(
            "不去云门店", // 短信签名
            "$tem_code", // 短信模板编号
            "$phone", // 短信接收者
            Array(  // 短信模板中字段的值
                'code' => $code,
            )
           
        );
        Log::info('对方网址是'.$_SERVER['REMOTE_ADDR'].',数据走到了sendSMS方法这里'.$response->Code);
        if($response->Code=='OK'){
          return json_encode(['code'=>$code,'info'=>'已发送','phone'=>$phone]);;
        }
    }
}
