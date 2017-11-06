<?php
namespace App\Http\Middleware; 

use Closure;  
use Illuminate\Contracts\Auth\Guard;  
use Response;
use App\User;
use App\Image;
  
class CheckHeader  
{  
    /** 
     * The Guard implementation. 
     * 
     * @var Guard 
     */  
  
    /** 
     * Handle an incoming request. 
     * 
     * @param  \Illuminate\Http\Request  $request 
     * @param  \Closure  $next 
     * @return mixed 
     */  
    public function handle($request, Closure $next)  
    {  
        if(!isset($_SERVER['HTTP_TOKEN'])){//no token header  
            return Response::json(
                array(
                    'status' => 'fail',
                    'code' =>  422
                )
            );  
        }

        $token = $_SERVER['HTTP_TOKEN'];
        $user = User::where('token', $token)->first();
        if($user){
            $_SESSION['user'] = $user;
        }
        else{//authentication fail
            return Response::json(
                array(
                    'status' => 'fail',
                    'code' =>  421
                )
            );
        }
        return $next($request);  
    }  
}
