<?php

namespace App\Http\Middleware;

use Closure;

class Corss
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      $response = $next($request);
      $response->header('Access-Control-Allow-Origin', '*');
      $response->header('Access-Control-Allow-Headers', 'x-requested-with, Origin, Content-Type, Cookie, Accept, multipart/form-data, application/json');
      $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS');
      $response->header('Access-Control-Allow-Credentials', 'false');
      return $response;
    }
}
