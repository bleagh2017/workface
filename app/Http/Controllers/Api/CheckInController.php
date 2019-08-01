<?php

namespace App\Http\Controllers\Api;

use App\Model\CheckIn;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Model\Event;
use App\Model\SignUp;
use App\Model\User;
use App\Model\RedemptionVoucher;
use App\Model\ConfigTable;
use TheSeer\Tokenizer\Exception;


class CheckInController extends Controller
{
    public function checkIn(Request $request){
		$eventId = decrypt($request->input("code"));
		DB::beginTransaction();
		$event = Event::find($eventId);
		$userId = $request->user()->id;
		if(empty($event)) return response(["message"=>"未找到活动信息"],422);

		if(strtotime($event->event_time) - time() > 3600 )return response(["message"=>"距离活动开始还有一小时以上,在等等"],422);
		if($event->state == 3 )return response(["message"=>"活动已结束"],422);
		if(CheckIn::where(["user_id"=>$userId,'event_id'=>$eventId])->get()->isNotEmpty()) return response(["message"=>"你已经签到过啦"]);


		$sign = SignUp::where(["user_id"=>$userId,'event_id'=>$eventId])->first();
		$ifSign = 0;
		$ifOntime = 0;
		if(!empty($sign) && $sign->result == 1){$ifSign = 1;}
		if(time() < strtotime($event->event_time)){$ifOntime = 1;}
		try{
			CheckIn::create([
				'user_id' => $userId,
				'event_id' => $eventId,
				'if_sign' => $ifSign,
				'if_ontime' => $ifOntime,
				'check_time'=> date("Y-m-d H:i:s",time())
			]);
		}catch(Exception $e){
			DB::rollBack();
			return response(["message"=>"签到失败"],422);
		}

		if($ifSign && $ifOntime ){
			$user = User::find($userId);
			$rate = ConfigTable::where(["config_name"=>"coins_rate"])->first()->value;

			if($user->awesome_num+1 ==  $rate){
				//todo 兑换比率修改后 生成优惠券规则
				//集满赞自动兑换券
				$user->redemption_num = $user->redemption_num + 1;
				$user->awesome_num = 0;
				try{
					RedemptionVoucher::create([
						'user_id' => $userId,
						'expired_time' => date("Y-m-d H:i:s",strtotime("+1 month"))
					]);
				}catch(Exception $e){
					Log::getMonolog()->popHandler();
					Log::useDailyFiles(storage_path('logs/database/save_error.log'));
					Log::info("活动id:{$eventId},用户id:{$userId},兑换券生成失败:{$e}");
					DB::rollBack();
					return response(["message"=>"签到失败"],422);
				}
			}else{
				$user->awesome_num = $user->awesome_num + 1;
			}
			try{
			    $redem = $user->awsome_num == 0 ? true : false;
				$user->save();
				DB::commit();
				$data = $user->find($userId,["wx_name","head","state","awesome_num","api_token","user_type","redemption_num"]);
				return response()->json(["message"=>"签到成功,赞+1","user"=>$data,"redem"=>$redem]);
			}catch(Exception $e){
				DB::rollBack();
				Log::getMonolog()->popHandler();
				Log::useDailyFiles(storage_path('logs/database/save_error.log'));
				Log::info("活动id:{$eventId},用户id:{$userId},集赞失败:{$e}");
				return response()->json(["message"=>"集赞失败,请良联系管理员"]);
			}
		}else{
			DB::commit();
			return response()->json(["message"=>"签到成功"]);
		}

	}
}
