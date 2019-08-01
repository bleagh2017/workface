<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\ConfigTable;



class ConfigController extends Controller
{
    public function setConfig(Request $request){
        $coins_rate = $request->input("coins_rate");
        $auto_check_in = $request->input("auto_check_in");
        $banner = $request->input("banner");
        $channel = $request->input("channel");
        if(empty($coins_rate) || empty($banner) || empty($channel)) return response(json_encode(["message"=>"参数异常"]),422);

        ConfigTable::where(["config_name"=>"coins_rate"])->update(["value"=>$coins_rate]);
        ConfigTable::where(["config_name"=>"auto_check_in"])->update(["value"=>$auto_check_in]);
        ConfigTable::where(["config_name"=>"banner"])->update(["value"=>json_encode($banner)]);
        ConfigTable::where(["config_name"=>"channel"])->update(["value"=>json_encode($channel)]);
        return ConfigTable::all()->pluck("value","config_name");
       
    }
}
