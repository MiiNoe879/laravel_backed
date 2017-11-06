<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Api\V1\Requests\UserInfoRequest;
use Dingo\Api\Routing\Helpers;
use DB;
use App\User;
use App\Image;
use App\Report;

class UserController extends Controller
{
    use Helpers;

    //get user information
    public function getUserInfo(UserInfoRequest $request)
    {
        $currentUser = $_SESSION['user'];
        $count = Image::where('user_id', $currentUser->id)->count();
        $currentUser->count = $count;
        return $currentUser;
    }

    //update user information
    public function updateUserInfo(UserInfoRequest $request)
    {
        $user = $_SESSION['user'];
        $user->nickname = $request->get('nickname');
        if($user->save())
            return response()
                ->json([
                    'status' => 'ok',
                    'code' =>  200
                ]);
        else
            return response()
                ->json([
                        'status' => 'fail',
                        'code' =>  420
                ]);
    }
    //get leaderboard
    public function getLeaderBoard(Request $request){
        $count_per_page = $request->get('count_per_page');

        $result = DB::table('users')
        ->join('images', 'users.id', '=', 'images.user_id')
        ->select('users.id', 'users.nickname', DB::raw("count(images.id) as count"))
        ->groupBy('users.id','users.nickname')
        ->orderBy('count', 'DESC')
        ->paginate($count_per_page);
        return $result;
        
    }
}
