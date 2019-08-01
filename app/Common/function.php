<?php
/**
 * Created by PhpStorm.
 * User: LINZI
 * Date: 2019/5/10
 * Time: 14:43
 */
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
//通用函数封装

//获取accessToken
function getAccessToken(){
    $url = config("app.accessToken")."&appid=".config('app.appid')."&secret=".config('app.secret');
    $client = new \GuzzleHttp\Client();
    $res = $client->request('GET', $url);
    $resultArr = json_decode($res->getBody());
    return $resultArr->access_token;
}

function AccessToken($redis = false){
    if($redis){
        return getAccessToken();
    }
    //缓存令牌
    $token = Redis::get('access_token');
    if(empty($token)){
        $token = getAccessToken();
        //记录缓存
        Redis::setex ('access_token',6000,$token);
    }
    return $token;
}

function sendTemplate($wxOpenID,$formId,$template,$data){
    $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".AccessToken();
    $client = new \GuzzleHttp\Client();
    Log::getMonolog()->popHandler();
    Log::useDailyFiles(storage_path('logs/query/send_template.log'));
    Log::info($template);
    $res = $client->request('POST', $url, [
        "json"=>[
            'access_token' => AccessToken(),
            'touser' => $wxOpenID,
            'template_id' => $template,
            'form_id' => $formId,
            "data" =>$data
        ]
    ]);
    return json_decode($res->getBody());
}
