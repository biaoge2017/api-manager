<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use alipay\aop\AopClient;
use alipay\aop\request\AlipayFundTransToaccountTransferRequest;
use alipay\aop\SignData;
use alipay\aop\request\AlipayTradePrecreateRequest;
use alipay\aop\request\AlipayTradeQueryRequest;
use alipay\aop\request\AlipayTradeCancelRequest;
use Log;
use App\Events\F2fpayEvent;
use Event;

class PayController extends Controller

{ 

    public $flag='not_pay';
    public $time_long=1;
    // //接收主函数分发的数据，并进行生成二维码，轮询支付状态等操作
    //  public function getInfo($bizContent){
    //     $res=json_decode($this->f2fpay($bizContent));
      
    //     if($res->code==200){        
    //        Event(new F2fpayEvent($res->data->out_trade_no));
    //        return json_encode($res->data);   
    //     }else{
    //       return json_encode($res->data);
    //     }

    //  }

    //支付宝面对面付款(扫码付款功能)
    //var $bizContent(array)
    
    public function f2fpay($bizContent)
    {
      $total_amount = $bizContent['total_amount'];
      $user_id = $bizContent['user_id'];
      $department_id = $bizContent['department_id'];
      $subject = $bizContent['subject'];
      $seller = $bizContent['seller'];
      $out_trade_no=$bizContent['out_trade_no'];
      require_once (base_path().'/app/Libs/alipay/aop/AopClient.php');
      require_once (base_path().'/app/Libs/alipay/aop/SignData.php');
      $aop = new AopClient ();
      $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
      $aop->appId = ALIPAY_APP_ID;
      $aop->rsaPrivateKey =RSAPRIVATEKEY;
      $aop->alipayrsaPublicKey=ALIPAYRSAPUBLICKEY;
      $aop->apiVersion = '1.0';
      $aop->signType = 'RSA2';
      $aop->postCharset='GBK';
      $aop->format='json';
      require_once (base_path().'/app/Libs/alipay/aop/request/AlipayTradePrecreateRequest.php');
      $request = new AlipayTradePrecreateRequest ();
      //$otn = $this->make_otn();
      //2088621736705502为不去科技的alipay账户pid
      $request->setBizContent('{
      "out_trade_no":"'.$out_trade_no.'",
      "seller_id":"'.$seller.'",
      "total_amount":"'.$total_amount.'",
      "subject":"'.$subject.'",
      "operator_id":"'.$user_id.'",
      "store_id":"'.$department_id.'",
      "extend_params":{
        "sys_service_provider_id":"2088621736705502"
        },
      "timeout_express":"3m"
      }');
      $result = $aop->execute ( $request);
      //var_dump($result);exit;
      $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
      $resultCode = $result->$responseNode->code;
      $needinfo = $result->$responseNode;
      
      if(!empty($resultCode)&&$resultCode == 10000){ 
        return json_encode($needinfo->qr_code);
      } else {
        return json_encode($responseNode->sub_msg);
      }
    }


    //支付订单撤销
    //var $order_code(string) _订单编号
    public function cancel_order($order_code)
    {
      require_once (base_path().'/app/Libs/alipay/aop/AopClient.php');
      require_once (base_path().'/app/Libs/alipay/aop/SignData.php');
      $aop = new AopClient ();
      $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
      $aop->appId = ALIPAY_APP_ID;
      $aop->rsaPrivateKey = RSAPRIVATEKEY;
      $aop->alipayrsaPublicKey=ALIPAYRSAPUBLICKEY;
      $aop->apiVersion = '1.0';
      $aop->signType = 'RSA2';
      $aop->postCharset='GBK';
      $aop->format='json';
      require_once (base_path().'/app/Libs/alipay/aop/request/AlipayTradeCancelRequest.php');
      $request = new AlipayTradeCancelRequest ();
      $request->setBizContent('{
      "out_trade_no":"'.$order_code.'" 
      }');
      $result = $aop->execute ( $request);
      
      $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
      $resultCode = $result->$responseNode->code;
      $needinfo = $result->$responseNode;
      if(!empty($resultCode)&&$resultCode == 10000){

      return json_encode(['code'=>10009,'msg'=>$result->$responseNode->msg,'info'=>'订单已撤销']);
      } else {
      return json_encode(['code'=>$resultCode,'msg'=>'error','info'=>$result->$responseNode->sub_msg]);
      }
    }
      //获取支付状态
      public function get_pay_status($data){
       $out_trade_no=$data['out_trade_no'];   
       $start_time=time();
       $res='';
       while($this->flag=='not_pay' && time()-$start_time<120){
         sleep(3);
         $res=$this->query_pay_status($out_trade_no);
         $this->time_long+=3;
         
       }
      
       if($res['code']==200 && $res['data']->trade_status=='TRADE_SUCCESS'){
            //成功支付
            $info=['code'=>$res['data']->code,'msg'=>$res['data']->msg,'buyer_user_id'=>$res['data']->buyer_logon_id,'pay_time'=>$res['data']->send_pay_date,'out_trade_no'=>$res['data']->out_trade_no,'receipt_amount'=>$res['data']->receipt_amount,'trade_no'=>$res['data']->trade_no];
               return json_encode($info);
            
          }else{
            //未支付，发起撤销订单
            $res=$this->cancel_order($out_trade_no); 
            return $res;
          }
      }

         //支付宝面对面转账,轮询支付状态
    //var $order_code(string) _订单编号
    public function query_pay_status($order_code)
    {
             
              require_once (base_path().'/app/Libs/alipay/aop/AopClient.php');
              require_once (base_path().'/app/Libs/alipay/aop/SignData.php');
              $aop = new AopClient();
              $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
              $aop->appId = ALIPAY_APP_ID;
              $aop->rsaPrivateKey = RSAPRIVATEKEY;
              $aop->alipayrsaPublicKey=ALIPAYRSAPUBLICKEY;
              $aop->apiVersion = '1.0';
              $aop->signType = 'RSA2';
              $aop->postCharset='GBK';
              $aop->format='json';
              require_once (base_path().'/app/Libs/alipay/aop/request/AlipayTradeQueryRequest.php');
              $request = new AlipayTradeQueryRequest ();
              $request->setBizContent('{
              "out_trade_no":"'.$order_code.'"
              }');
        

              $result = $aop->execute ( $request);
              
              $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
              $resultCode = $result->$responseNode->code;
              $needinfo = $result->$responseNode;
              //var_dump($result);
              if(!empty($resultCode)&&$resultCode == 10000){
                if($needinfo->trade_status=='TRADE_SUCCESS'){
                  $this->flag='already_pay';
                   
                }
              return (['code'=>200,'msg'=>'ok','data'=>$needinfo]);
              } else {
              return (['code'=>0,'msg'=>'error','data'=>$result->$responseNode->sub_msg]);
              }
    }
}
