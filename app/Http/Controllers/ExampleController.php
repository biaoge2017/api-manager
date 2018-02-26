<?php
/**
 * 样板类,创建新控制器时可以直接复制此类然后修改
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExampleController extends Controller
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

    public function example($request_data)
    {
      //
    }
}
