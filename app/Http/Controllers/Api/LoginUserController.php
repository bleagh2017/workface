<?php

namespace App\Http\Controllers\Api;

use App\Model\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoginUserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers ;
    
    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'wx_openid';
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
   // protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->middleware('checkPower')->only(["getUserList",""]);
    }
    
    public function login(Request $request)
    {
        //获取数据
        $code = $request->post('code');
        $encryptedData = $request->post('encryptedData');
        $iv = urldecode($request->post('iv'));
        $appid = config('app.appid');
        $secret = config('app.secret');
        //交换openid
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', config('app.jscode2session'), [
            'query' => ['appid' => $appid,
                'secret' => $secret,
                'js_code' => $code,
                'grant_type' => 'authorization_code']
        ]);
        $body = json_decode($res->getBody());
        $session_key = $body->session_key;
        $openid = $body->openid;

        $user = User::where('wx_openid', $openid);
        $api_token = str_random(64);
        //第一次使用小程序
        if ($user->get()->isEmpty()) {
            //解码信息
            $userifo = new \WXBizDataCrypt($appid, $session_key);
            $errCode = $userifo->decryptData($encryptedData, $iv, $data);
            if ($errCode != 0) return ($errCode);
            $info = json_decode($data);
//            $unionId = $info->unionId;
            User::create([
                'wx_name' => $info->nickName,
                'wx_openid' => $openid,
                'wx_unionid' => "empty",
                'head' => $info->avatarUrl,
                'api_token' => $api_token,
                'sex' => $info->gender
            ]); 
        } else {
            $user->update(["api_token" => $api_token]);
        }
        $config = DB::table('config_table')->pluck("value","config_name");
        //todo 数据异常???
        $userArr = User::with('user_detail')->find($user->first()->id,["id","wx_name","head","state","awesome_num","api_token","user_type","redemption_num"])->toArray();
        return response()->json([$config,$userArr]);
    }
    /*
    public function test (Request $request) {
        
        return response()->json();
    }
    
    public function checkin(Request $request)
    {
        Log::getMonolog()->popHandler();
        Log::useDailyFiles(storage_path('logs/info.log'));
        Log::info(json_encode($request));
    }*/

    public function getUserList(Request $request)
    {
        $like = empty($request->input("keyword"))?"%%":"%{$request->input("keyword")}%";
        $data = DB::table('users')
            ->where("wx_name","like",$like)
            ->orderBy('created_at',"desc")
            ->paginate(7);
        
        return response()->json($data);
    }
    //用户详情
    public function getUserInfo(Request $request){
        $userId = $request->input("id");
        if(empty(User::find($userId)))return response(json_encode(["message"=>"未查询到对应用户"],422));

        $data = DB::table('users')
            ->leftJoin('event_sign_up', 'event_sign_up.user_id', '=', 'users.id')
            ->leftJoin('check_in', 'check_in.user_id', '=', 'users.id')
            ->where("users.id","=",$userId)
            ->select(DB::raw("users.* ,count(event_sign_up.id) '报名次数',count(event_sign_up.result = 1 or null) '报名通过次数',count(check_in.id) '到会次数',sum(check_in.if_ontime) '准点次数' "))
            ->groupBy("users.id")
            ->first();
        return response()->json($data);
    }
    //修改用户状态
    public function changeUserType(Request $request){
        $type = $request->input("type");
        $userId = $request->input("id");
        if(empty($type)) return response(json_encode(["message"=>"没有传递操作参数"]),422); 
        switch ($type){
            case "pigeon":
                $res = User::where(['id' => $userId])->update(["state" => -1]);
                break;
            case "restore":
                $res = User::where(['id' => $userId])->update(["state" => 1]);
                break;
            case "manage":
                if($request->user()->user_type != 9){ return response(json_encode(["message"=>"您不是超管没有权限"]),422);}
                $res = User::where(['id' => $userId])->update(["user_type" => 1]);
                break;
            case "remanage":
                if($request->user()->user_type != 9){ return response(json_encode(["message"=>"您不是超管没有权限"]),422);}
                $res = User::where(['id' => $userId])->update(["user_type" => 0]);
                break;
        }
        if($res){
            return response(json_encode(["message"=>"success"]));
        }else{
            return response(json_encode(["message"=>"修改失败"]),422);
        }
        
    }

}
