<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use alipay_barcode\aop\AopClient;
//use alipay_barcode\aop\request\AlipayFundTransToaccountTransferRequest;
//use alipay_barcode\aop\SignData;
use alipay_barcode\aop\request\AlipayTradePayRequest;
//use alipay_barcode\aop\request\AlipayTradeQueryRequest;
//use alipay_barcode\aop\request\AlipayTradeCancelRequest;
class PayBarcodeController extends Controller
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
    //
    
    //支付宝扫码支付主函数
    public function barcode_pay($bizContent){

          $total_amount = $bizContent['total_amount'];
          $user_id = $bizContent['user_id'];
          $department_id = $bizContent['department_id'];
          $subject = $bizContent['subject'];
          $seller = $bizContent['seller'];
          $out_trade_no=$bizContent['out_trade_no'];
          $auth_code=$bizContent['auth_code'];
      require_once (base_path().'/app/Libs/alipay_barcode/aop/AopClient.php');
      require_once (base_path().'/app/Libs/alipay_barcode/aop/SignData.php');
            $aop = new AopClient ();
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $aop->appId = ALIPAY_APP_ID;
            $aop->rsaPrivateKey =RSAPRIVATEKEY;
            $aop->alipayrsaPublicKey=ALIPAYRSAPUBLICKEY;
            
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset='GBK';
            $aop->format='json';
       require_once (base_path().'/app/Libs/alipay_barcode/aop/request/AlipayTradePayRequest.php');
            $request = new AlipayTradePayRequest ();
            $request->setBizContent('{
            "out_trade_no":"'.$out_trade_no.'",
            "scene":"bar_code",
            "auth_code":"'.$auth_code.'", 
            "subject":"'.$subject.'", 
            
            "seller_id":"'.$seller.'",
            "total_amount":'.$total_amount.',
            
            "operator_id":"'.$user_id.'",
            "store_id":"'.$department_id.'",
           
            "extend_params":{
            "sys_service_provider_id":"2088511833207846"
             },
            "timeout_express":"3m" 
            }');
            $result = $aop->execute ( $request); 
            
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;
            if(!empty($resultCode)&&$resultCode == 10000){
                $data=['code'=>$result->$responseNode->code,'buyer_user_id'=>$result->$responseNode->buyer_logon_id,'msg'=>$result->$responseNode->msg,'pay_time'=>$result->$responseNode->gmt_payment,'out_trade_no'=>$result->$responseNode->out_trade_no,'receipt_amount'=>$result->$responseNode->receipt_amount,'trade_no'=>$result->$responseNode->trade_no];
               return json_encode($data);
            } else {
 
               return json_encode(['code'=>$result->$responseNode->code,'msg'=>$result->$responseNode->msg]);
            }
    }
}
