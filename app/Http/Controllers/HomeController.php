<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;
use App\Model\CouponList;
use App\Model\Sharer;
use App\Model\User;
use App\Model\ItemInfo;
use App\Model\RedemptionVoucher;
use Maatwebsite\Excel\Excel;

class HomeController extends Controller
{
    protected $localAddr = array(
        "::1", "127.0.0.1", "localhost","47.100.111.31"
    );
    protected function localCheck(){
        
        if(!in_array($_SERVER['REMOTE_ADDR'], $this->localAddr)){
            Log::getMonolog()->popHandler();
            Log::useDailyFiles(storage_path('logs/auto/auto.log'));
            Log::info("非法调用");
            exit();
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }
    //优惠券
    public function checkExpiredCoupon(){
        $this->localCheck();
        $num = CouponList::where([["expired_time","<",date("Y-m-d H:i:s",time())],["state","=",0]])->update(["state"=>-1]);
        
        Log::getMonolog()->popHandler();
        Log::useDailyFiles(storage_path('logs/auto/auto.log'));
        Log::info("今日优惠券有{$num}张过期");
        
    }
    //兑换券
    public function checkExpiredRedem(){
        $this->localCheck();
        DB::beginTransaction();
        $expId = [];
        RedemptionVoucher::where([["expired_time","<",date("Y-m-d H:i:s",time())],["state","=",0]])->get(['user_id'])->each(function ($item,$key)use (&$expId){
            $expId[] = $item->user_id;
        });
        $userNum = User::whereIn("id",$expId)->where("redemption_num",">",0)->decrement("redemption_num");
        $coupon = RedemptionVoucher::where([["expired_time","<",date("Y-m-d H:i:s",time())],["state","=",0]])->update(["state"=>-1]);
        Log::getMonolog()->popHandler();
        Log::useDailyFiles(storage_path('logs/auto/auto.log'));
        if($userNum == $coupon){
            DB::commit();
            Log::info("今日兑换券有{$coupon}张过期");
        } else{
            DB::rollback();
            Log::warning("兑换券销毁失败");
        }
        

    }
    
    public function test(Excel $excel)
    {
        var_dump(config("app.normalEvent"));
    }
    public function import(Excel $excel){
        $cellData = [
            1,2,3,4,5,56,346,56,457
        ];

        $name = iconv('UTF-8', 'GBK', '成员信息3');
        $excel->create($name,function($excel) use ($cellData){
            $excel->sheet('score', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->store('xls');
    }

        
    
}
    
