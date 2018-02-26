<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class GoodsController extends Controller
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
     public $get_need_url='';//php第一次向java发请求，请求获取需求数据
     public $response_need_url='';//
     private $token='buqukeji';
    //管理平台请求erp数据
    public function php_pull_need(){
      $request_string='Token='.$token;
      $res=$this->post_url($request_string,$this->get_need_url);
      return $res;

    }
    public function php_push_response($request_data){

       $goods_id=$request_data['goods_id'];
       $num=$request_data['num'];
       $dead_line=$request_data['dead_line'];
       $price=$request_data['price'];
       $request_string='GOODS_ID='.$goods_id.'&NUM='.$num.'&DEAD_LINE='.$dead_line.'&PRICE='.$price;
       $res=$this->post_url($request_string,$this->get_need_url);
       return $res;
    }
    //转发管理平台的请求，去调java的接口获取商户的需求
    public function post_curl($request_string,$url){
      if (empty($request_string)) {
            return false;
        }
        $headers=array();
        $headers[]='Content-Type:application/x-www-form-urlencoded; charset=UTF-8';
        $postUrl = $url;
        $curlPost = $request_string;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, $headers);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//不直接输出在屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $res = json_decode(curl_exec($ch));//运行curl
        curl_close($ch);

        return $res;
    } 
  //转发
    
}
