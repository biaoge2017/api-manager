<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class YizhifuController extends Controller
{
    public function __construct()
    {
      //
    }
    private $key='AAD575A487CBF00D62C1CE25D66FAB252530280094217553';//商户数据 KEY,需申请
    private $merchantPwd='340815';//商户交易key 需申请

    public $pay_url="https://webpaywg.bestpay.com.cn/barcode/placeOrder";//pos机扫码收银请求接口
    public $queryOrder_url="https://webpaywg.bestpay.com.cn/query/queryOrder";//订单交易查询接口
    //public $creatTimestamp_url = "https://webpaywg.bestpay.com.cn/createTimeStamp.do";//时间戳生成接口
    public $rebund_url="https://webpaywg.bestpay.com.cn/refund/commonRefund";//退款申请接口  
    //php魔术方法,当调用类内没有的方法时调用此方法
    public function __call($name,$args)
    {
      return 'voided';
    }
    //生成二维码供扫枪扫码支付
    public function create_code($request_data)
    {
      // if(empty($request_data)){
      //   return false;
      // }
      $key=$this->key;//商户数据key
      //Log::info('对方网址是'.$_SERVER['REMOTE_ADDR'].',数据走到了create_code方法这里'.$request_data['orderAmt'].$key);
      $merchantId=$request_data['merchantId'];//商户号
      $barcode=$request_data['barcode'];//商户POS扫描用户客户端条形码
      $orderNo=$request_data['orderNo'];//订单号由商户平台提供，支持纯数字、纯字母、字母+数字组成，全局唯一（如果需要使用条码退款业务，订单号必须为偶数位）
      $orderReqNo=$request_data['orderReqNo'];//订单请求交易流水号 与订单号一致
      $channel='05';//默认05
      $goodsDetail=$request_data['goodsDetail'];//商品详情
      $storeId=$request_data['storeId'];//门店号
      $orderDate=$request_data['orderDate'];//订单日期 长度14位,格式yyyyMMddhhmmss(必须是24小时制)
      $orderAmt=$request_data['orderAmt'];//订单总金额 单位分 订单总金额 = 产品金额+附加金额
      $productAmt=$request_data['productAmt'];//产品金额 默认与订单总金额一致
      $attachAmt=$request_data['attachAmt'];//附加金额就是除了产品金额之外的金额,比如邮费
      $goodsName=$request_data['goodsName'];
      //$curType=array_key_exists('curtype',$request_data) ? $request_data['curType'] : 'RMB';//默认填 RMB
      $encodeType=array_key_exists('encodeType',$request_data) ? $request_data['encodeType'] : 5;//默认填：5，是翼支付定制CA加密；1，是MD5加密方式；6，是标准CA加密
      //$merchantUrl=$request_data['merchantUrl'];//商户提供的用于接收交易返回的前台url，前台页面显示订单状态结果
      //$backMerchantUrl=$request_data['backMerchantUrl'];//商户提供的用于接收交易返回的后台url，后台异步通知订单状态结果
      $busiType=$request_data['busiType'];//业务类型 默认填0000001，普通订单
      //$productId=array_key_exists('productId',$request_data) ? $request_data['productId'] : '04';//业务标识 默认04
      //$customerId=$request_data['customerId'];//登录手机号
     

      //提取组装mac所需要的字段767408995
     $params=[];
     $params['orderReqNo']=$orderReqNo;
     $params['merchantId'] =$merchantId;
     $params['orderNo']=$orderNo;
     $params['orderDate'] =$orderDate;
     $params['orderAmt']=$orderAmt;
     //$params['clientIp']=$clientIp;
     $params['key']=$key;
     $params['barcode']=$barcode;
     $mac=$this->create_mac($params);
        //提取组装sign所需要的字段
       
          //$timestamp_res=$this->create_timestramp($merchantId,$orderSeq,$orderReqTanseq,$mac);
          // if($timestamp_res['success']===false){
          //   return $timestamp_res;
          // }else{
          //   $timestamp=$timestamp_res['result'];
          // }
          //拼接请求字符串
          $request_string='merchantId='.$merchantId.'&orderNo='.$orderNo.'&orderReqNo='.$orderReqNo.'&orderDate='.$orderDate.'&orderAmt='.$orderAmt.'&productAmt='.$productAmt.'&attachAmt='.$attachAmt.'&busiType='.$busiType.'&goodsName='.$goodsName.'&barcode='.$barcode.'&channel='.$channel.'&goodsDetail='.$goodsDetail.'&storeId='.$storeId.'&mac='.$mac;
          //log::info(urlencode($request_string));
          //请求接口
            $pay_res=$this->post_curl($request_string,$this->pay_url);
            //var_dump($request_string);
            //Log::info("走到这里了".$pay_res['success']);
            //exit;
            if($pay_res['success']==true && $pay_res['result']['transStatus']=='B'){
              //组装sign，进行验签
              $sign_res=$this->verify_sign($pay_res['result']);
              
                $pay_res['api_status']=$sign_res['status'];
                $pay_res['api_msg']=$sign_res['msg'];
                return $pay_res;
              
            }else{
                $pay_res['api_status']=100;//尚未支付成功
                $pay_res['api_msg']='尚未成功支付';
                return $pay_res;
            }
        
    }
    //查询订单的验签
    public function verify_sign($params_sign){
     $sign_string='MERCHANTID='.$params_sign['merchantId'].'&ORDERNO='.$params_sign['orderNo'].'&ORDERREQNO='.$params_sign['orderReqNo'].'&ORDERDATE='.$params_sign['orderDate'].'&OURTRANSNO='.$params_sign['ourTransNo'].'&TRANSAMT='.$params_sign['transAmt'].'&TRANSSTATUS='.$params_sign['transStatus'].'&ENCODETYPE='.$params_sign['encodeType'].'&KEY='.$this->key;
     $sign=strtoupper(md5($sign_string));
     if($sign==$params_sign['sign']){
      return array('api_status'=>200,'api_msg'=>'验签成功');
     }else{
      return array('api_status'=>0,'api_msg'=>'验签失败');
     }
   }
     //条码支付的验签
     public function pay_verify_sign($params_sign){

      $sign_string='MERCHANTID='.$params_sign['merchantId'].'&ORDERNO='.$params_sign['orderNo'].'&ORDERREQNO='.$params_sign['orderReqNo'].'&ORDERDATE=null&OURTRANSNO='.$params_sign['ourTransNo'].'&TRANSAMT='.$params_sign['transAmt'].'&TRANSSTATUS='.$params_sign['transStatus'].'&ENCODETYPE='.$params_sign['encodeType'].'&KEY='.$this->key;
      $sign=strtoupper(md5($sign_string));
        if($sign==$params_sign['sign']){
           return array('api_status'=>200,'api_msg'=>'验签成功');
        }else{
           return array('api_status'=>0,'api_msg'=>'验签失败');
        }
    }
      public function rebund_verify_sign($params_sign){
     $sign_string='OLDORDERNO='.$params_sign['oldOrderNo'].'&REFUNDREQNQ='.$params_sign['refundreqno'].'&TRANSAMT='.$params_sign['transAmt'].'&KEY='.$this->key;
     $sign=strtoupper(md5($sign_string));
     if($sign==$params_sign['sign']){
      return array('api_status'=>200,'api_msg'=>'验签成功');
     }else{
      return array('api_status'=>0,'api_msg'=>'验签失败');
     }
     

    }

    //扫码二维码支付
    public function scan_code($request_data)
    {
      $merchantId=$request_data['merchantId'];//商户号
      $barcode=$request_data['barcode'];//商户POS扫描用户客户端条形码
      $orderNo=$request_data['orderNo'];//订单号由商户平台提供，支持纯数字、纯字母、字母+数字组成，全局唯一（如果需要使用条码退款业务，订单号必须为偶数位）
      $orderReqNo=$request_data['orderReqNo'];//订单请求交易流水号 与订单号一致
      $channel='05';//默认05
      //$goodsDetail=$request_data['goodsDetail'];//商品详情
      $storeId=$request_data['storeId'];//门店号
      $orderDate=$request_data['orderDate'];//订单日期 长度14位,格式yyyyMMddhhmmss(必须是24小时制)
      $orderAmt=$request_data['orderAmt'];//订单总金额 单位分 订单总金额 = 产品金额+附加金额
      $productAmt=$request_data['productAmt'];//产品金额 默认与订单总金额一致
      $attachAmt=$request_data['attachAmt'];//附加金额就是除了产品金额之外的金额,比如邮费
      //$goodsName=$request_data['goodsName'];
      //$curType=array_key_exists('curtype',$request_data) ? $request_data['curType'] : 'RMB';//默认填 RMB
      //$encodeType=array_key_exists('encodeType',$request_data) ? $request_data['encodeType'] : 5;//默认填：5，是翼支付定制CA加密；1，是MD5加密方式；6，是标准CA加密
      //$merchantUrl=$request_data['merchantUrl'];//商户提供的用于接收交易返回的前台url，前台页面显示订单状态结果
      //$backMerchantUrl=$request_data['backMerchantUrl'];//商户提供的用于接收交易返回的后台url，后台异步通知订单状态结果
      $busiType='0000001';//业务类型 默认填0000001，普通订单

      //提取组装mac所需要的字段767408995
     $params=[];
     $params['orderReqNo']=$orderReqNo;
     $params['merchantId'] =$merchantId;
     $params['orderNo']=$orderNo;
     $params['orderDate'] =$orderDate;
     $params['orderAmt']=$orderAmt;
     //$params['clientIp']=$clientIp;
     $params['key']=$this->key;
     $params['barcode']=$barcode;
     $mac=$this->create_mac($params);
     $request_string='merchantId='.$merchantId.'&orderNo='.$orderNo.'&orderReqNo='.$orderReqNo.'&orderDate='.$orderDate.'&orderAmt='.$orderAmt.'&productAmt='.$productAmt.'&attachAmt='.$attachAmt.'&busiType='.$busiType.'&barcode='.$barcode.'&channel='.$channel.'&storeId='.$storeId.'&mac='.$mac;
          //log::info(urlencode($request_string));
          //请求接口
            $pay_res=$this->post_curl($request_string,$this->pay_url);
            $pay_res = json_decode($pay_res,true);
            if($pay_res['success']=="true"){
              $sign_res=$this->pay_verify_sign($pay_res['result']);//请求成功后进行验签
                if($sign_res['api_status']==200 &&$pay_res['result']['transStatus']=="B"){
                    $data=['code'=>10000,'out_trade_no'=>$pay_res['result']['orderNo'],'trade_no'=>$pay_res['result']['ourTransNo'],'receipt_amount'=>$pay_res['result']['transAmt'],'buyer_user_id'=>$pay_res['result']['customerId'],'pay_time'=>''];
                }else{
                  $data=['code'=>10009,"errorMsg"=>'订单未支付成功或验签错误',"errorCode"=>'100009'];
                }

            }else{
              $data=['code'=>10009,"errorMsg"=>$pay_res['errorMsg'],"errorCode"=>$pay_res['errorCode']];
            }
            
            return json_encode($data);
    }
    
    
    //生成时间戳
    // public function create_timestramp($merchantId,$orderSeq,$orderReqTanseq,$mac){
     
    //       if (empty($merchantId) || empty($orderSeq || empty($orderReqTranseq) || empty($mac))) {
    //         return false;
    //     }
        
    //     $url = $this->creatTimestamp_url;
    //     $request_string = 'MERCHANTID='.$merchantId.'&ORDERSEQ='.$orderSeq.'&ORDERREQTRANSEQ='.$orderReqTranseq.'&MAC='.$mac;
    //     $res=$this->post_url($request_string,$url);
    //     return $res;
        
    // }
    //生成mac值
    public function create_mac($params){
     $merchantId=$params['merchantId'];
     $orderNo=  $params['orderNo'];
     $orderReqNo=  $params['orderReqNo'];
     $orderDate=$params['orderDate'];
     $orderAmt=$params['orderAmt'];
     //$clientIp=$params['clientIp'];
     $key=$params['key'];
     $barcode=$params['barcode'] ;
  
      $mac_string='MERCHANTID='.$merchantId.'&ORDERNO='.$orderNo.'&ORDERREQNO='.$orderReqNo.'&ORDERDATE='.$orderDate.'&BARCODE='.$barcode.'&ORDERAMT='.$orderAmt.'&KEY='.$key;
      $mac=strtoupper(md5($mac_string)) ;
       //返回mac值
      return $mac;
     }
   
    
    //curl 请求支付接口
    public function post_curl($request_string,$url){
      if (empty($request_string)) {
            return json_encode('no requeset_string');
        }
        //$headers=array();
        //$headers[]='Content-Type: application/x-www-form-urlencoded; charset=utf-8';
        $postUrl = $url;
        //$curlPost =urlencode($request_string);
        $curlPost=$request_string;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE); 
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//不直接输出在屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $res =curl_exec($ch);//运行curl
        curl_close($ch);
        return $res;
    } 

    //订单查询
    public function query_order($request_data){
     
      $merchantId=$request_data['merchantId'];
      $orderNo=$request_data['orderNo'];
      $orderReqNo=$request_data['orderReqNo'];
      $orderDate=$request_data['orderDate'];
      $key=$this->key;
      $mac_string='MERCHANTID='.$merchantId.'&ORDERNO='.$orderNo.'&ORDERREQNO='.$orderReqNo.'&ORDERDATE='.$orderDate.'&KEY='.$key;
      $mac=strtoupper(md5($mac_string));
      $request_string='merchantId='.$merchantId.'&orderNo='.$orderNo.'&orderReqNo='.$orderReqNo.'&orderDate='.$orderDate.'&mac='.$mac;
      
      $query_res=$this->post_curl($request_string,$this->queryOrder_url);
      $query_res=json_decode($query_res,true);
      $res=['merchantId'=>$query_res['result']['merchantId'],'receipt_amount'=>$query_res['result']['transAmt'],'orderDate'=>$query_res['result']['orderDate'],'buyer_user_id'=>$query_res['result']['customerId'],'trade_no'=>$query_res['result']['orderNo']];
      //$data=['code'=>10000,'out_trade_no'=>$pay_res['result']['orderNo'],'trade_no'=>$pay_res['result']['ourTransNo'],'receipt_amount'=>$pay_res['result']['transAmt'],'buyer_user_id'=>$pay_res['result']['customerId'],'pay_time'=>''];
      //var_dump($query_res);echo "666666";
          if($query_res['success']==true && $query_res['result']['transStatus']=="B"){
              //组装sign，进行验签
              $sign_res=$this->verify_sign($query_res['result']);
              
                $res['api_status']=$sign_res['api_status'];
                $res['api_msg']=$sign_res['api_msg'];
            }else{
                $res['api_status']=100;//尚未支付成功
                $res['api_msg']='订单未查到或尚未支付';  
            }
            return json_encode($res);
    }

   //退款申请接口
   public function rebund($request_data){
      if(empty($request_data)){
        return false;
      }
      //$request_data=json_decode($request_data);
      $merchantId=$request_data['merchantId'];
      $merchantPwd=$this->merchantPwd;
      //$orderSeq=$request_data['orderSeq'];
      //$orderReqTranseq=$request_data['orderReqTranseq'];
      //$orderDate=$request_data['orderDate'];
      //$orderAmount=$request_data['orderAmount'];
      //$productAmount=$request_data['productAmount'];
      
      $oldOrderNo=$request_data['oldOrderNo'];
      $oldOrderReqNo=$request_data['oldOrderReqNo'];
      $refundReqNo=$request_data['refundReqNo'];//退款流水号 唯一,不能和oldOrderReqNo same 
      $refundReqDate=$request_data['refundReqDate'];//退款请求日期  yyyyMMDD
      $transAmt=$request_data['transAmt'];//退款交易金额 小于等于订单金额 单位为分
      $channel='05';//默认01
      //$ledgerDetail=array_key_exists('ledgerDetail',$request_data) ? $request_data['ledgerDetail'] :NULL;//默认填 null
      $mac_string='MERCHANTID='.$merchantId.'&MERCHANTPWD='.$merchantPwd.'&OLDORDERNO='.$oldOrderNo.'&OLDORDERREQNO='.$oldOrderReqNo.'&REFUNDREQNO='.$refundReqNo.'&REFUNDREQDATE='.$refundReqDate.'&TRANSAMT='.$transAmt.'&LEDGERDETAIL=null&KEY='.$this->key;
      //var_dump($mac_string);
      $mac=strtoupper(md5($mac_string));
      $request_string='merchantId='.$merchantId.'&merchantPwd='.$this->merchantPwd.'&oldOrderNo='.$oldOrderNo.'&oldOrderReqNo='.$oldOrderReqNo.'&refundReqNo='.$refundReqNo.'&refundReqDate='.$refundReqDate.'&transAmt='.$transAmt.'&channel='.$channel.'&mac='.$mac;
       $res=$this->post_curl($request_string,$this->rebund_url);
       //var_dump($res);exit;
        if($rebund_res['success']==true){
              //组装sign，进行验签
              $sign_res=$this->rebund_verify_sign($rebund_res['result']);
              
                $rebund_res['api_status']=$sign_res['status'];
                $rebund_res['api_msg']=$sign_res['msg'];
                return $rebund_res;
              
            }else{
                $rebund_res['api_status']=100;//尚未支付成功
                $rebund_res['api_msg']='订单查询失败,无需验签';
                return $rebund_res;
            }

   }
}
