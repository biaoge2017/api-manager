<?php
/**
 * 测试用天气接口类
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WeatherController extends Controller
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

    public function weather($request_data)
    {
      $host = "https://ali-weather.showapi.com";
      $path = "/spot-to-weather";
      $method = "GET";
      $appcode = "c09416c7e9da473e83996eb0fcdf013a";
      $headers = array();
      array_push($headers, "Authorization:APPCODE " . $appcode);
      $querys = "area=$request_data";
      $bodys = "";
      $url = $host . $path . "?" . $querys;

      $curl = curl_init();
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($curl, CURLOPT_FAILONERROR, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HEADER, true);
      if (1 == strpos("$".$host, "https://"))
      {
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      }
      $response = curl_exec($curl);
      return $response;
    }
}
