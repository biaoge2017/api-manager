<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class Yizhifu_pcController extends Controller
{
    public function __construct()
    {
      //
    }
    private $key='AAD575A487CBF00D62C1CE25D66FAB252530280094217553';//商户数据 KEY,需申请
    private $merchantPwd='340815';//商户交易key 需申请

    public $pay_url="https://webpay.bestpay.com.cn/index.html";//支付请求接口
    public $queryOrder_url="https://webpaywg.bestpay.com.cn/query/queryOrder";//订单查询接口
    public $creatTimestamp_url = "https://webpaywg.bestpay.com.cn/createTimeStamp.do";//时间戳生成接口
    public $refund_url="https://webpaywg.bestpay.com.cn/refund/commonRefund";//退款申请接口  
    //php魔术方法,当调用类内没有的方法时调用此方法
    public function __call($name,$args)
    {
      return 'void';
    }
    //生成二维码供扫枪扫码支付
    public function create_code($request_data)
    {
       
      if(empty($request_data)){
        return false;
      }

      $key=$this->key;//商户数据key
      $merchantId=$request_data['merchantId'];//商户号
      $orderSeq=$request_data['orderSeq'];//订单号
      $orderReqTranseq=$request_data['orderReqTranseq'];//订单请求交易流水号 与订单号一致
      $orderDate=$request_data['orderDate'];//订单日期 长度14位,格式yyyyMMddhhmmss
      $orderAmount=$request_data['orderAmount'];//订单总金额 单位分 订单总金额 = 产品金额+附加金额
      $productAmount=$request_data['productAmount'];//产品金额 默认与订单总金额一致
      $attachAmount=array_key_exists('attachamount',$request_data) ? $request_data['attachAmount'] : 0;//附加金额就是除了产品金额之外的金额
      $curType=array_key_exists('curtype',$request_data) ? $request_data['curType'] : 'RMB';//默认填 RMB
      $encodeType=array_key_exists('encodeType',$request_data) ? $request_data['encodeType'] : 5;//默认填：5，是翼支付定制CA加密；1，是MD5加密方式；6，是标准CA加密
      $merchantUrl=array_key_exists('merchantUrl', $request_data) ? $request_data['merchantUrl']:'';//商户提供的用于接收交易返回的前台url，前台页面显示订单状态结果
      $backMerchantUrl=array_key_exists('backMerchantUrl', $request_data) ? $request_data['backMerchantUrl']:'';//商户提供的用于接收交易返回的后台url，后台异步通知订单状态结果
      $busicode=array_key_exists('busicode',$request_data) ? $request_data['busicode'] : '0000001';//默认填0000001，普通订单
      $productId=array_key_exists('productId',$request_data) ? $request_data['productId'] : '04';//业务标识 默认04
      $customerId=$request_data['customerId'];//登录手机号
      $goodsName=$request_data['goodsName'];//
      $clientIp='111';
      //提取组装mac所需要的字段
     $params=[];
     $params['merchantId'] =$merchantId;
     $params['orderSeq']=$orderSeq;
     $params['orderDate'] =$orderDate;
     $params['orderAmount']=$orderAmount;
     $params['clientIp']=$clientIp;
     $params['key']=$key;
     $params['encodeType']=$encodeType;
     $mac=$this->create_mac($params);
     //提取组装timestamp_mac所需要的字段
     $params_mac=[];
     $params_mac['merchantId'] =$merchantId;
     $params_mac['orderSeq']=$orderSeq;
     $params_mac['orderReqTranseq']=$orderReqTranseq;
     $params_mac['key']=$key;
     $timestamp_mac=$this->create_timestamp_mac($params_mac);
     
          $timestamp_res=$this->create_timestramp($merchantId,$orderSeq,$orderReqTranseq,$timestamp_mac);
          var_dump($timestamp_res);exit;
          if($timestamp_res['success']===false){
            return $timestamp_res;
          }else{
            $timestamp=$timestamp_res['result'];
          }
          //拼接请求字符串
          $request_string='merchantId='.$merchantId.'&orderSeq='.$orderSeq.'&orderReqTranseq='.$orderReqTranseq.'&orderDate='.$orderDate.'&orderAmount='.$orderAmount.'&productAmount='.$productAmount.'&attachAmount='.$attachAmount.'&curType='.$curType.'&encodeType='.$encodeType.'&merchantUrl='.$merchantUrl.'&backMerchantUrl='.$backMerchantUrl.'&busicode='.$busicode.'&productId='.$productId.'&customerId='.$customerId.'&goodsName='.$goodsName.'&timestamp='.$timestamp;
          
            $pay_res=$this->post_curl($request_string,$this->pay_url);
        
    }
    //请求时间戳时候所需要的mac字符串
    public function create_timestamp_mac($params_mac){
        $merchantId=$params_mac['merchantId'];
         $orderSeq=$params_mac['orderSeq'];
         $orderReqTranseq=$params_mac['orderReqTranseq'];
         $key=$params_mac['key'];
         //$mac_string='MERCHANTID='.$merchantId.'&ORDERSEQ='.$orderSeq.'&ORDERREQTRANSEQ='.$orderReqTranseq.'&KEY='.$key;
         $mac_string='MERCHANTID=02410109020603170&ORDERSEQ=20060314&ORDERREQTRANSEQ=20060314&KEY=AAD575A487CBF00D62C1CE25D66FAB252530280094217553';
         $mac=strtoupper(md5($mac_string));
         var_dump($mac);
         return $mac;
    }
    //扫码二维码支付
    public function scan_code($request_data)
    {
      //
    }
    //生成时间戳
    public function create_timestramp($merchantId,$orderSeq,$orderReqTranseq,$timestamp_mac){
     
          if (empty($merchantId) || empty($orderSeq || empty($orderReqTranseq) || empty($timestamp_mac))) {
            return false;
        }
        
        $url = $this->creatTimestamp_url;
        $request_string = 'MERCHANTID='.$merchantId.'&ORDERSEQ='.$orderSeq.'&ORDERREQTRANSEQ='.$orderReqTranseq.'&MAC='.$timestamp_mac;
        //$request_string ='MERCHANTID=02410109020603170&ORDERSEQ=20060314&ORDERREQTRANSEQ=20060314&KEY=AAD575A487CBF00D62C1CE25D66FAB252530280094217553';
        
        $res=$this->post_curl($request_string,$url);
        return $res;
        
    }
    //生成mac值
    public function create_mac($params){
     $merchantId=$params['merchantId'];
     $orderSeq=  $params['orderSeq'];
     $orderDate=$params['orderDate'];
     $orderAmount=$params['orderAmount'];
     $clientIp=$params['clientIp'];
     $key=$params['key'];
     $encodeType=$params['encodeType'] ;
     if($encodeType==5){
      $mac_string='MERCHANTID='.$merchantId.'&ORDERSEQ='.$orderSeq.'&ORDERDATE='.$orderDate.'&ORDERAMOUNT='.$orderAmount.'&KEY='.$key;
     }elseif($encodeType==1){
      $mac_string='MERCHANTID='.$merchantId.'&ORDERSEQ='.$orderSeq.'&ORDERDATE='.$orderDate.'&ORDERAMOUNT='.$orderAmount.'&CLIENTIP='.$clientIp.'&KEY='.$key;
      $mac=strtoupper(md5($mac_string)) ;
     }
    //返回mac值
    return $mac;
    }
    //curl 请求支付接口
    public function post_curl($request_string,$url){
      if (empty($request_string)) {
            return false;
        }
        
        $postUrl = $url;
        $curlPost = $request_string;
        //$headers=array();
        //$headers[]='Content-Type:application/x-www-form-urlencoded; charset=UTF-8';
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//不直接输出在屏幕上
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE); 
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $res = json_decode(curl_exec($ch));//运行curl
        var_dump($res);
        curl_close($ch);
        return $res;
    } 


    //订单查询
    public function queryOrder($request_data){
      $merchantId=$request_data['merchantId'];
      $orderNo=$request_data['orderNo'];
      $orderReqNo=$request_data['orderReqNo'];
      $orderDate=$request_data['orderDate'];
      $key=$this->key;
      $mac_string='MERCHANTID='.$merchantId.'&ORDERNO='.$orderNo.'&ORDERREQNO='.$orderReqNo.'&ORDERDATE='.$orderDate.'&KEY='.$key;
      $mac=strtoupper(md5($mac_string));
      $request_string='MERCHANTID='.$merchantId.'&ORDERNO='.$orderNo.'&ORDERREQNO='.$orderReqNo.'&ORDERDATE='.$orderDate.'&MAC='.$mac;
      $res=$this->post_url($request_string,$this->queryOrder_url);
      return $res;
    }

   //退款申请接口
   public function rebund($request_data){
      if(empty($request_data)){
        return false;
      }
      $merchantId=$request_data['merchantId'];
      $orderSeq=$request_data['orderSeq'];
      $orderReqTranseq=$request_data['orderReqTranseq'];
      $orderDate=$request_data['orderDate'];
      $orderAmount=$request_data['orderAmount'];
      $productAmount=$request_data['productAmount'];
      $merchantPwd=$this->merchantPwd;
      $oldOrderNo=$request_data['oldOrderNo'];
      $oldOrderReqNo=$request_data['oldOrderReqNo'];
      $refundReqNo=$request_data['refundReqNo'];//退款流水号 唯一
      $refundReqDate=$request_data['refundReqDate'];//退款请求日期  yyyyMMDD
      $transAmt=$request_data['transAmt'];//退款交易金额 小于等于订单金额 单位为分
      $channel='01';//默认01
      $mac_string='MERCHANTID='.$merchantId.'&MERCHANTWD='.$this->merchantPwd.'&OLDODERNO='.$oldOrderNo.'&OLDORDERREQNO='.$oldOrderReqNo.'&REFUNDREQNO='.$refundReqNo.'&REFUNDREQDATE='.$refundReqDate.'&TRANSAMT='.$transAmt.'&KEY='.$this->key;
      $mac=strtoupper(md5($mac_string));
      $request_string='MERCHANTID='.$merchantId.'&MERCHANTWD='.$this->merchantPwd.'&OLDODERNO='.$oldOrderNo.'&OLDORDERREQNO='.$oldOrderReqNo.'&REFUNDREQNO='.$refundReqNo.'&REFUNDREQDATE='.$refundReqDate.'&TRANSAMT='.$transAmt.'&CHANNEL='.$channel.'&MAC='.$mac;
       $res=$this->post_url($request_string,$this->rebund_url);
      return $res;

   }
}
