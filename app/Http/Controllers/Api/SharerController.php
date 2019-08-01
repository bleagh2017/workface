<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Sharer;
use App\Model\Event;
use Illuminate\Support\Facades\DB;

class SharerController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->middleware('checkPower')->except("getName");
        //可以添加only  except 指定方法加载
    }

    public function getName(Request $request){
        $sharerId = $request->input("id");
        if(!empty($sharerId)){
            $data = Sharer::find($sharerId);
            if(empty($data)){
                return response(json_encode(["message"=>"未找到对应分享者"]),422);
            }
            if(!empty($request->input("link"))){
                $event = Event::where(["sharer_id"=>$sharerId])->get(["event_name","link"]);
                return response()->json(["data"=>$data,"event"=>$event]);
            }
            return response()->json($data);
        }
        if(!empty($request->input("sharer"))){
            return response()->json(Sharer::all("id","sharer_name"));
        }
        if(!empty($request->input("item"))){
            return response()->json(Sharer::where("brand_name","<>","")->get(["id","brand_name","brand_logo"]));
        }
        $data = DB::table('sharer')
            ->where("sharer_name","like","%{$request->input("keyword")}%")
            ->select("id","sharer_name","title","head")
            ->paginate(10);
        return response()->json($data);
    }
    
    public function insertSharer(Request $request){
        $this->_check($request);
        //保存数据
        $result = $this->_insert($request->all());
        if($result){
            return response(json_encode(["message"=>"保存成功"]));
        }else{
            return response(json_encode(["message"=>"数据保存失败"]),422);
        }
    }
    
    public function updateSharer(Request $request){
        $sharerItem = Sharer::find($request->input("id"));
        if(empty($sharerItem)){
            return response(json_encode(["message"=>"更新异常,找不到对应分享者"]),422);
        }

        $this->_check($request);
        $sharerItem->sharer_name = $request->input("sharer_name");
        $sharerItem->sex = $request->input("sex");
        $sharerItem->title = $request->input("title");
        $sharerItem->head = $request->input("head");
        $sharerItem->content = $request->input("content");
        $sharerItem->brand_name = $request->input("brand_name");
        $sharerItem->brand_logo = $request->input("brand_logo");
        $res = $sharerItem->save();
        if($res){
            return response(json_encode(["message"=>"更新成功"]));
        }else{
            return response(json_encode(["message"=>"数据更新失败"]),422);
        }
    }

    protected function _check(Request $request){
        //参数验证
        $messages = [
            'required' => ':attribute 字段未提交',
            'max'      => ':attribute 输入内容过多' ,
        ];
        $this->validate($request, [
            'sharer_name' => 'required|max:20|bail',
            'sex' => 'required|bail',
            'title' => 'required|max:50|bail',
            'head' => 'required|bail',
            'content' => 'required|bail'
        ],$messages);
    }
    
    protected function _insert(array $data){
        return  Sharer::create([
            'sharer_name' => $data['sharer_name'],
            'sex' => $data['sex'],
            'title' => $data['title'],
            'head' => $data['head'],
            'content' => $data['content'],
            'brand_name' => $data['brand_name'],
            'brand_logo' => $data['brand_logo']
        ]);
    }
}
