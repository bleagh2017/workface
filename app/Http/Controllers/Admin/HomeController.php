<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\Model\Admins;

class HomeController extends Controller
{
    //使用中间件登录
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(){
        return view("admin/home");
    }
    public function test(Request $request){
       
    }
}
