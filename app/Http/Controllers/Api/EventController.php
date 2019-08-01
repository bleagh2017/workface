<?php

namespace App\Http\Controllers\Api;

use App\Model\Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class EventController extends Controller
{
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->middleware('checkPower')->except("getList");
        //可以添加only  except 指定方法加载
    }
    
    public function create (Request $request) {
        $this->check($request);
        //保存数据
        $result = $this->insertEvent($request->all());
        if($result){
            if($request->input("state") == 0){
                return response(json_encode(["message"=>"保存成功"]));
            }else{
                return response(json_encode(["message"=>"发布成功"]));
            }
        }else{
            return response(json_encode(["message"=>"数据保存失败"]),422);
        }
    }

    public function update(Request $request){
        $eventItem = Event::find($request->input("id"));
        if(empty($eventItem)){
            return response(json_encode(["message"=>"更新异常,找不到对应活动"]),422);
        }

        $this->check($request);
        $eventItem->event_name = $request->input("event_name");
        $eventItem->event_time = $request->input("event_time");
        $eventItem->sign_up_time = $request->input("sign_up_time");
        $eventItem->sharer_id = $request->input("sharer_id");
        $eventItem->picture = $request->input("picture");
        $eventItem->link = $request->input("link");
        $eventItem->type = $request->input("type");
        $eventItem->state =  $eventItem->state == 0 ? $request->input("state") : 1;
        $eventItem->max_num =  $request->input("max_num");
        $eventItem->price =  $request->input("price");
        $res = $eventItem->save();
        if($res){
            return response(json_encode(["message"=>"更新成功"]));
        }else{
            return response(json_encode(["message"=>"数据更新失败"]),422);
        }
    }
    
    public function release(Request $request){
        $eventItem = Event::find($request->input("id"));
        $type = $request->input("type");
        $str = $type == "release"?"活动发布":"活动结束";
        if(empty($eventItem)){
            return response(json_encode(["message"=>"{$str}异常,找不到对应活动"]),422);
        }
        if($type == "release"){
            $eventItem->state = 1;
        }else if($type == "end"){
            $eventItem->state = 3;
        }
        $res = $eventItem->save();
        if($res){
            return response(json_encode(["message"=>"{$str}成功"]));
        }else{
            return response(json_encode(["message"=>"{$str}失败"]),422);
        }
    }

    public function check(Request $request){
        //参数验证
        $messages = [
            'required' => ':attribute 字段未提交',
            'date'     => ':attribute 日期格式错误' ,
            'max'      => ':attribute 输入内容过多' ,
        ];
        $this->validate($request, [
            'event_name' => 'required|max:255|bail',
            'event_time' => 'required|date|bail',
            'sign_up_time' => 'required|date|bail',
            'sharer_id' => 'required|bail',
            'picture' => 'required|bail',
            'link' => 'required|bail',
            'type' => 'required|bail',
            'state' => 'required|bail',
            'max_num' => 'required|bail',
            'price' => 'required|bail',
        ],$messages);
    }

    public function upload(Request $request){
        $fileCharater = $request->file('picture');
        $type = $request->input('type');

        if (!empty($fileCharater) && $fileCharater->isValid()) {
            //获取文件的扩展名
            $ext = $fileCharater->getClientOriginalExtension();
            //获取文件的绝对路径
            $path = $fileCharater->getRealPath();
            //文件哈希存为文件名
            $filename = md5_file($path).date("Ymd").'.'.$ext;
            //判断是否重复
            $imageList = json_decode(Redis::get("imageHasH"),true);
            if(!empty($imageList) && !empty($imageList[$type])){
                if(in_array($filename,$imageList[$type])){
                    $filePath = Storage::disk($type)->url($filename);
                    return response(json_encode(["message"=>"上传成功","url"=>$filePath]));
                }
            }
            //压缩文件
            $img = Image::make($path)->resize(300, null, function ($constraint) { $constraint->aspectRatio(); } )
                ->encode($ext ,80);
            //存储文件。disk里面的public。总的来说，就是调用disk模块里的public配置
            $res = Storage::disk($type)->put($filename, $img);
            $imageList[$type][] = $filename;
            Redis::set("imageHasH",json_encode($imageList));            
            if(!$res){
                return response(json_encode("文件上传失败"),422);
            }
            $filePath = Storage::disk($type)->url($filename);
            return response(json_encode(["message"=>"上传成功","url"=>$filePath]));
        }else{
            return response(json_encode(["message"=>"文件上传失败"]),422);
        }
    }
    
    public function getList(Request $request){
        //更新时根据id查询单条记录
        if($request->input("id")){
            return response()->json(Event::find($request->input("id")));
        }
        //活动列表 查询报名人数
        if($request->input("param") == "check"){
            $where = empty($request->input("keyword"))?"%%":"%{$request->input("keyword")}%";
            $data = DB::table('event_release')
                ->leftJoin('event_sign_up', 'event_sign_up.event_id', '=', 'event_release.id')
                ->select(DB::raw('event_release.*,count(event_sign_up.result = 1 or null) `pass`,count(event_sign_up.result = 0 or null) `undetermined`'))
                ->where("event_name","like",$where)
                ->groupBy("event_release.id")
                ->orderBy('event_time','desc')
                ->paginate(7);
            return response()->json($data);
        }
        $where = [];
        $order = "";
        //倒序列表 发布页面 及首页 近期活动
        if($request->input("param") == "release" || $request->input("param") == 2){
            $where[] = ['state', '>', 0];
            $order = "desc";
        }
        //正序  全部活动
        if($request->input("param") == 1){
            $where[] = ['state', '>', 0];
            $order = "asc";
        }
        //番外活动筛选
        if($request->input("param") == 3){
            $where[] = ['state', '>', 0];
            $where[] = ["type","=",2];
            $order = "desc";
        }
        //搜索
        if(!empty($request->input("keyword"))){
            $where[] = ["event_name","like","%{$request->input("keyword")}%"];
        }
        $data = DB::table('event_release')
            ->where($where)
            ->select("event_release.id","event_name","event_time","sign_up_time","picture","link","type","state","max_num","price","result")
            ->orderBy('event_time',$order)
            ->leftJoin('event_sign_up', function ($join) use($request) {
                $join->on('event_sign_up.event_id', '=', 'event_release.id')
                    ->where("event_sign_up.user_id","=",$request->user()->id);
            })
            ->paginate(10);
        return response()->json($data);
    }
    
    public function insertEvent(array $data){
        return  Event::create([
            'event_name' => $data['event_name'],
            'event_time' => $data['event_time'],
            'sign_up_time' => $data['sign_up_time'],
            'sharer_id' => $data['sharer_id'],
            'picture' => $data['picture'],
            'link' => $data['link'],
            'type' => $data['type'],
            'state' => $data['state'],
            'max_num' => $data['max_num'],
            'price' => $data['price'],
        ]);
    }

   public function getCode(Request $request){
       $event_id = $request->input("id");
       if(empty($event_id) || empty(Event::find($event_id))) return response(["message"=>"获取活动失败"],422);

       return response(encrypt($event_id));
   }
}
