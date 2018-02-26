<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
use Illuminate\Support\Facades\Redis;

$router->get('/', function () use ($router) {
    echo  'hello lemen';
});
$router->get('test','testController@test');
//接口 返回检测60条检测数据
$router->post('api_watch','WatchController@return_api_data');
$router->get('dispatch',function(){
	Redis::hset('lumens', 'Hello', 5);
  echo Redis::hget('lumens','Hello');
});

$router->post('station','StationController@main');
$router->get('station','StationController@main');
$router->post('getdata','ToolsController@getdata');
