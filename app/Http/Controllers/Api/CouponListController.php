<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\CouponList;
use App\Model\RedemptionVoucher;
use Illuminate\Support\Facades\DB;

class CouponListController extends Controller
{
    public function getCouponList(Request $request){
        $user = $request->user();
        $data = DB::table("coupon_list")
            ->where("user_id","=",$user->id)
            ->leftJoin("item_info","item_info.id","=","coupon_list.item_id")
            ->select("coupon_list.id","coupon_list.created_at","coupon_list.expired_time","coupon_list.use_time","coupon_list.state","item_info.item_name","item_info.picture")
            ->orderBy("coupon_list.expired_time","desc")
            ->paginate(7);
        return response()->json($data);
    }

    public function billCoupon(Request $request){
        $code = strtolower($request->input("code"));
        $itemId = $request->input("itemId");
        $coupon = CouponList::find($itemId);
        if(empty($coupon)) return response(json_encode(["message"=>"为找到对应的优惠券"]),422);
        $allCode = $coupon->itemInfo->code;
        $allCode = $this->sortOut(json_decode($allCode,true));
        if(!in_array($code,$allCode,true)) return response(json_encode(["message"=>"输入的验证码有误"]),422);
        $resRepeat = CouponList::where([
            ["item_id","=",$coupon->item_id],
            ["code","=",$code]
        ])->get()->isNotEmpty();
        if($resRepeat) return response(json_encode(["message"=>"该验证码已经使用过"]),422);
        $coupon->code = $code;
        $coupon->use_time = date("Y-m-d H:i:s",time());
        $coupon->state = 1;
        $resSave = $coupon->save();
        if($resSave){
            return response(json_encode(["message"=>"使用成功"]));
        }else{
            return response(json_encode(["message"=>"使用失败"]),422);
        }
    }

    public function getRedemList(Request $request){
        $user = $request->user();
        $data = RedemptionVoucher::where("user_id","=",$user->id)->orderByRaw('ABS(state)')->orderBy("expired_time")->get();
        return response()->json($data);
    }
    
    protected function sortOut($allCode){
        $newCode = [];
        foreach($allCode as $value){
            foreach($value as $code){
                $newCode[] = strtolower($code);
            }
        }
        return $newCode;
    }
}
