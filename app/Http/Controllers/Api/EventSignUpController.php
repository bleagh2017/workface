<?php

namespace App\Http\Controllers\Api;

use App\Model\ConfigTable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Model\SignUp;
use App\Model\Event;
use App\Model\User;
use App\Model\UserDetail;
use Illuminate\Support\Facades\DB;

class EventSignUpController extends Controller
{
    public function signUp(Request $request){
        $user =  $request->user();
        $param = $request->input("param");
        $eventId = $param["eventId"];
        $formId = $param["formId"];
        if(empty($param["real_name"]) || empty($param["age"]) || empty($param["channel"]) || empty($param["job"])){
            return response(json_encode(["message"=>"报名信息缺失"]),422);
        }  
        $this->userDetail($param,$user);
        if(empty($eventId) || empty($formId)){
            return response(json_encode(["message"=>"参数异常"]),422);
        }
        $event = Event::find($eventId);
        if(empty($event)){ return response(json_encode(["message"=>"未找到对应活动"]),422);}
        if($event->state != 1){ return response(json_encode(["message"=>"这次错过了下次吧"]),422);}
        if(SignUp::where(["user_id"=>$user->id,'event_id'=>$eventId])->get()->isNotEmpty()){
            return response(json_encode(["message"=>"您已经报名"]),422);
        }
        if(strtotime($event->sign_up_time) < time()) {return response(json_encode(["message"=>"报名已经截止,下次要提早哦"]),422);}
        $signResult = 0 ;
        $reason = "";

        $config = ConfigTable::where(["config_name"=>"auto_check_in"])->first();
        if($event->max_num > SignUp::where(['event_id'=>$eventId,"result"=>1] )->get()->count() && $user->state != -1 && $config->value == 1 ){
            $signResult = 1;
            $reason = "自动报名成功";
        }
        if($user->state == -1){
            $signResult = -1;
            $reason = "抱歉报名人数已满请下次提早哦";
        }
        $res = $this->insertSignUp($eventId,$user->id,$signResult,$reason,$param["channel"]);
        //todo 事务
        $nowNum = SignUp::where(['event_id'=>$eventId,"result"=>1])->count();
        if($event->max_num <= $nowNum){
            $event->state = 2 ;
            $event->save();
        }
        //发送服务信息或存储formId
        if($signResult == 1){
            $data = $this->_messageData($event->type,$template,$event,$user,$reason);
            $resStr = sendTemplate($user->wx_openid,$formId,$template,$data);
            Log::getMonolog()->popHandler();
            Log::useDailyFiles(storage_path('logs/query/send_template.log'));
            Log::info(json_encode($resStr));
        }elseif($signResult == -1) {
            $data = $this->_messageData(-1,$template,$event,$user,$reason);
            $resStr = sendTemplate($user->wx_openid,$formId,$template,$data);
            Log::getMonolog()->popHandler();
            Log::useDailyFiles(storage_path('logs/query/send_template.log'));
            Log::info(json_encode($resStr));
        }else{
            $userItem = User::find($user->id);
            $userItem->form_id = $formId;
            $resStr = $userItem->save();
            Log::getMonolog()->popHandler();
            Log::useDailyFiles(storage_path('logs/database/formId.log'));
            $str = "userId => {$user->id},formId => {$formId} ,res => ";
            $resStr = $resStr?"success":"false";
            Log::info($str.$resStr);
        }
        if($res){
            return response(json_encode(["message" => "报名信息提交成功"]));
        }else{
            return response(json_encode(["message" => "报名信息生成失败"],422));
        }
    }

    public function insertSignUp($eventId,$userId,$result,$reason,$channel){
        return  SignUp::create([
            'event_id' => $eventId,
            'user_id' => $userId,
            'result' => $result,
            'result_reason' => $reason,
            'channel' => $channel
        ]);
    }

    public function signUpList(Request $request){
        $eventId = $request->input("event_id");
        if(empty($eventId)){return response(json_encode(["message"=>"未获取活动信息"]),422);}
        $res = DB::table('event_sign_up')
            ->leftJoin('users', 'event_sign_up.user_id', '=', 'users.id')
            ->select(DB::raw('users.id u_id,event_sign_up.id,users.wx_name, users.head, users.state,event_sign_up.created_at,event_sign_up.result'))
            ->where('event_id','=',$eventId)
            ->orderByRaw('ABS(result)')
            ->orderBy('result')
            ->get();
        return response()->json($res);
    }

    public function passSign(Request $request){
        //todo 路由验证参数
        $signId = $request->input("id");
        $result = $request->input("result");
        $reason = $request->input("reason");
        $signItem = SignUp::find($signId);
        if(empty($signItem)){return response(["message"=>"未找到对应报名信息"],422);}
        $user = $signItem->user;
        $event = SignUp::find($signId)->event;

        $signItem->result = $result;
        $signItem->result_reason = $result == 1 ?"管理员通过":$reason;
        $res = $signItem->save();

        if(!$res){return response(["message"=>"审核失败"],422);}
        $data = $this->_messageData($result == 1 ?$event->type:-1,$template,$event,$user,$signItem->result_reason);
        $resStr = sendTemplate($user->wx_openid,$user->form_id,$template,$data);
        Log::getMonolog()->popHandler();
        Log::useDailyFiles(storage_path('logs/query/send_template.log'));
        Log::info(json_encode($resStr));
        return response(["message"=>"审核成功"]);
    }
    
    //出让名额(取消报名)
    public function transferSign(Request $request){
        $sign = SignUp::find($request->input("id"));
        if(empty($sign) || $sign->user_id != $request->user()->id){
            return response(json_encode(["message"=>"未找到对应报名信息"]),422);
        }
        if($sign->event->state == 3){
            return response(json_encode(["message"=>"活动已结束,无法进行名额出让"]),422);
        }
        if(strtotime($sign->event->event_time) < time()){
            return response(json_encode(["message"=>"活动已开始,无法进行名额出让"]),422);
        } 
        //todo 判断用户已经签到
        //todo 添加事务
        //计算当前活动报名人数
        $nowNum = SignUp::where(["event_id"=>$sign->event_id,"result"=>1])->count();
        $event = Event::find($sign->event_id);
        $maxNum = $event->max_num;
        
        if($nowNum-1 < $maxNum){
            $userId = DB::table('event_sign_up')
                ->select('id',"user_id")
                ->where(["event_id"=>$sign->event_id,"result"=>0])
                ->orderBy('created_at')
                ->first();
            if(!empty($userId)){
                $res = SignUp::find($userId->id)->update(["result"=>1,"result_reason"=>"名额转让成功"]);
                if($res){
                    $user = User::find($userId->user_id);
                    $data = $this->_messageData($event->type,$template,$event,$user);
                    $resStr = sendTemplate($user->wx_openid,$user->form_id,$template,$data);
                    Log::getMonolog()->popHandler();
                    Log::useDailyFiles(storage_path('logs/query/send_template.log'));
                    Log::info(json_encode($resStr));
                }
            }
        }
        $sign->result = 2;
        $sign->result_reason = "手动取消报名";
        $res = $sign->save();
        //todo 发送取消推送
        if($res){
            return response(json_encode(["message"=>"转让成功"]));
        }else{
            return response(json_encode(["message"=>"取消失败"]),422);
        }
    }
    
    //取消报名(未报名成功)
    public function cancelSign(Request $request){
        $sign = SignUp::find($request->input("id"));
        if(empty($sign) || $sign->user_id != $request->user()->id){
            return response(json_encode(["message"=>"未找到对应报名信息"]),422);
        }
        if($sign->event->state == 3){
            return response(json_encode(["message"=>"活动已结束,无法进行名额出让"]),422);
        }
        if(strtotime($sign->event->event_time) < time()){
            return response(json_encode(["message"=>"活动已开始,无法进行名额出让"]),422);
        }
        if($sign->result == 1){
            return response(json_encode(["message"=>"您已通过审核,如果放弃名额会自动转让给其他facer"]));
        }
        $sign->result = 2;
        $sign->result_reason = "手动取消报名";
        $res = $sign->save();
        //todo 发送取消推送
        if($res){
            return response(json_encode(["message"=>"取消成功"]));
        }else{
            return response(json_encode(["message"=>"取消失败"]),422);
        }
    }
    
    //获取当前用户报名信息
    public function signInfo(Request $request){
        $userId = $request->user()->id;
        $res = DB::table('event_sign_up')
            ->leftJoin('event_release', 'event_sign_up.event_id', '=', 'event_release.id')
            ->select(DB::raw('event_sign_up.*,event_release.event_name,event_release.picture,event_release.type,event_release.event_time,event_release.state e_state'))
            ->where('event_sign_up.user_id','=',$userId)
            ->orderBy('event_sign_up.created_at')
            ->get();
        return response()->json($res);
    }

    //type  1为付费活动成功信息  2 普通活动成功信息  3 拒绝信息
    protected function _messageData($type,&$template,$event,$user,$reason = null){
        switch($type){
            case 1:
                $template = config("app.normalEvent");
                $data = [
                    "keyword1" => ["value" => $event->event_name ],
                    "keyword2" => ["value" => $user->wx_name],
                    "keyword3" => ["value" => $event->event_time],
                    "keyword4" => ["value" => ""],
                    "keyword5" => ["value" => ""],
                ];
            break;
            case 2:
                $template = config("app.payedEvent");
                $data = [
                    "keyword1" => ["value" => $event->event_name ],
                    "keyword2" => ["value" => $user->wx_name],
                    "keyword3" => ["value" => ""],
                    "keyword4" => ["value" => $event->event_time],
                    "keyword5" => ["value" => $event->price."元"],
                    "keyword6" => ["value" => ""],
                ];
                break;
            case -1: //todo增加拒绝模板
                $template = config("app.falieEvent");
                $data = [
                    "keyword1" => ["value" => $event->event_name ],
                    "keyword2" => ["value" => $user->wx_name],
                    "keyword3" => ["value" => $event->event_time],
                    "keyword4" => ["value" => $reason],
                ];
                break;
        }
        return $data;
    }
    
    public function userDetail($param,$user){
        $userDetail = UserDetail::where("user_id","=",$user->id);
        if($userDetail->get()->isEmpty()){
            UserDetail::create([
                "user_id" => $user->id,
                "real_name" => $param["real_name"],
                "age" => $param["age"],
                "job" => $param["job"]
            ]);
        }else{
            $userDetail->update([
                "real_name" => $param["real_name"],
                "age" => $param["age"],
                "job" => $param["job"]
            ]);
        }
    }
}
