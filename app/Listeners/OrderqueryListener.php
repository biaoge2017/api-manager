<?php

namespace App\Listeners;
use alipay\aop\AopClient;
use alipay\aop\request\AlipayFundTransToaccountTransferRequest;
use alipay\aop\SignData;
use alipay\aop\request\AlipayTradePrecreateRequest;
use alipay\aop\request\AlipayTradeQueryRequest;
use alipay\aop\request\AlipayTradeCancelRequest;
use App\Events\F2fpayEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class OrderqueryListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public $flag='not_pay';
    public $time_long=1;
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ExampleEvent  $event
     * @return void
     */
    public function handle(F2fpayEvent $event)
    { 
        
       //$start_time=time();
       $out_trade_no=$event->out_trade_no;
       $res='';
       while($this->flag=='not_pay' && $this->time_long<30){
         sleep(3);
         $res=$this->query_pay_status($out_trade_no);
         $this->time_long+=3;
       }
       
       if($res['code']==200){
            //成功支付
            return $res;
          }else{
            //未支付，发起撤销订单
            $res=$this->cancel_order($out_trade_no);
            echo 222;var_dump($res);echo 111;exit;
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
              $request->setBizContent("{" .
              "\"out_trade_no\":\"$order_code\"" .
              "}");
              $result = $aop->execute ( $request);
             
              $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
              $resultCode = $result->$responseNode->code;
              $needinfo = $result->$responseNode;
              if(!empty($resultCode)&&$resultCode == 10000){
              $this->flag='already_pay';
              return (['code'=>200,'msg'=>'ok','data'=>$needinfo]);
              } else {
              return (['code'=>0,'msg'=>$result->$responseNode->sub_msg]);
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
      $request->setBizContent("{" .
      "\"out_trade_no\":\"$order_code\"" .
      "}");
      $result = $aop->execute ( $request);

      $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
      $resultCode = $result->$responseNode->code;
      $needinfo = $result->$responseNode;
      if(!empty($resultCode)&&$resultCode == 10000){
      return json_encode(['code'=>200,'msg'=>'ok','data'=>$needinfo]);
      } else {
      return json_encode(['code'=>0,'msg'=>'error','data'=>$result->$responseNode->sub_msg]);
      }
    }
}
