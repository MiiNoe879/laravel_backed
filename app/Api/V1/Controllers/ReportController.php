<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Api\V1\Requests\ReportRequest;
use JWTAuth;
use App\Report;
use Dingo\Api\Routing\Helpers;
use App\User;

class ReportController extends Controller
{
    use Helpers;

    //get all report list
    public function index(Request $request){
        $currentUser = $_SESSION['user'];
        return $currentUser
                ->reports()
                ->orderBy('created_at', 'DESC')
                ->get()
                ->toArray();
    }

    //post report
    public function store(Request $request){
        $currentUser = $_SESSION['user'];
        $report = new Report;
        
        $report->reason = $request->get('reason');
        $report->image_id = $request->get('image_id');
    
        if($currentUser->reports()->save($report))
            return response()
            ->json([
                'status' => 'ok',
                'status_code' =>  200
            ]);
        else
            return response()
                ->json([
                        'status' => 'fail',
                        'status_code' =>  422
                ]);
    }
}
