<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\ItemInfo;
use App\Model\User;
use App\Model\CouponList;
use App\Model\RedemptionVoucher;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\Mail;

class ItemInfoController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->middleware("checkPower")->except(["exchange","getShopItem"]);
    }
    
    public function exchange(Request $request){
        DB::beginTransaction();
        $item = ItemInfo::find($request->input("id"));
        if(empty($item)){
            DB::rollBack();
            return response(json_encode(["message"=>"未发送福利标识"]),422);
        } 
        $user = $request->user();
        if($user->redemption_num < $item->price) {
            DB::rollBack();
            return response(json_encode(["message"=>"您的兑换券不足,再努力集赞吧"]),422);
        }
        //用户兑换券数量变化
        $user = User::find($user->id);
        $user->redemption_num = $user->redemption_num - 1;
        $resUser = $user->save();
        if(!$resUser) {
            DB::rollBack();
            return response(json_encode(["message"=>"数据库异常兑换失败"]),422);
        }
        //兑换券状态变化
        $redemption = RedemptionVoucher::where([
            ["user_id","=",$user->id],
            ["state","=",0],
            ["expired_time",">=",date("Y-m-d H:i:s",time())]])
            ->orderBy("created_at")
            ->first();
        if(empty($redemption)) {
            DB::rollBack();
            return response(json_encode(["message"=>"兑换券数量异常"]),422);
        }
        $redemption->state = 1;
        $redemption->use_time = date("Y-m-d H:i:s",time());
        $resRedemption = $redemption->save();
        if(!$resRedemption) {
            DB::rollBack();
            return response(json_encode(["message"=>"数据库异常兑换失败"]),422);
        }
        //生成优惠券
        $resCoupon = CouponList::create([
            'user_id' => $user->id,
            'shop_id' => $item->admin_id,
            'item_id' => $item->id,
            'expired_time' => date("Y-m-d H:i:s",strtotime("+1 month")),
            'price' => $item->price,
            'state' => 0
        ]);
        if(!$resCoupon) {
            DB::rollBack();
            return response(json_encode(["message"=>"数据库异常兑换失败"]),422);
        }
        //福利库存变化
        $item->num = $item->num - 1;
        $resItem = $item->save();
        if(!$resItem) {
            DB::rollBack();
            return response(json_encode(["message"=>"数据库异常兑换失败"]),422);
        }
        DB::commit();
        return response()->json(["message"=>"兑换成功"]);
        
        
    }
    
    public function getShopItem(Request $request){
        if(empty($request->input("id"))) return response(json_encode(["message"=>"未发送品牌标识"]),422);
        $where[] = ["admin_id","=",$request->input("id")];
        if(!empty($request->input("keyword"))) $where[] = ["item_name","like","%{$request->input("keyword")}%"];
        $where[] = ["invalid_time",">=",date("Y-m-d",time())];
        $where[] = ["state","=",1];
        $item = DB::table("item_info")
            ->where($where)
            ->select("id","item_name","picture","price","description","num","invalid_time")
            ->orderBy('created_at',"desc")
            ->paginate(7);
        return  response()->json($item);
    }

    public function itemList(Request $request){
        if(!empty($request->input("id"))){
            return response()->json(ItemInfo::find($request->input("id")));
        }
        $like = empty($request->input("keyword"))?"%%":"%{$request->input("keyword")}%";
        $data = DB::table('item_info')
            ->where("item_name","like",$like)
            ->orderBy('created_at',"desc")
            ->paginate(7);

        return response()->json($data);
    }
    
    public function insertItem(Request $request){
        $this->_check($request);
        //生成每个商品对应的验证码
        $num = $request->input("num");
        $code = $this->randomStr($num);
        //保存数据
        $result = $this->_insert($request->all(),$code);
        if($result){
            return response(json_encode(["message"=>"保存成功"]));
        }else{
            return response(json_encode(["message"=>"数据保存失败"]),422);
        }
    }
    
    public function updateItem(Request $request){
        $Item = ItemInfo::find($request->input("id"));
        if(empty($Item)){
            return response(json_encode(["message"=>"更新异常,找不到对应福利"]),422);
        }

        $this->_check($request);
        $Item->item_name = $request->input("item_name");
        $Item->invalid_time = $request->input("invalid_time");
        $Item->admin_id = $request->input("admin_id");
        $Item->picture = $request->input("picture");
        $Item->price = $request->input("price");
        $Item->description = $request->input("description");
        $res = $Item->save();
        if($res){
            return response(json_encode(["message"=>"更新成功"]));
        }else{
            return response(json_encode(["message"=>"数据更新失败"]),422);
        }
    }
    
    public function changeItemState(Request $request){
        $item = ItemInfo::find($request->input("id"));
        $type = $request->input("type");
        if(empty($item)) return response(json_encode(["message"=>"没有对应的商品信息"]),422);
        if($item->state == $type) {
            $str = $type == 1?"该商品已上架":"该商品已下架";
            return response(json_encode(["message"=>$str."无需再次变更"]),422);
        }
        $item->state = $type;
        $res = $item->save();
        if($res){
            return response(json_encode(["message"=>"修改成功"]));
        }else{
            return response(json_encode(["message"=>"修改失败"]),422);
        }
    }
    
    public function addItemNum(Request $request){
        $item = ItemInfo::find($request->input("id"));
        $num = $request->input("num");
        if(empty($item)) return response(json_encode(["message"=>"没有对应的商品信息"]),422);
        if(!is_numeric($num)) return response(json_encode(["message"=>"请输入数字"]),422);
        $nowCode = json_decode($item->code,true);
        $newCode = $this->randomStr($num,$nowCode);
        $item->code = json_encode(array_merge($newCode,$nowCode));
        $item->num = $item->num + $num;
        $res = $item->save();
        if($res){
            return response(json_encode(["message"=>"添加成功"]));
        }else{
            return response(json_encode(["message"=>"添加失败"]),422);
        }
    }
    
    public function getItemCode(Request $request){
        $item = ItemInfo::find($request->input("id"));
        if(empty($item)) return response(json_encode(["message"=>"没有对应的商品信息"]),422);
        return response()->json($item->code);
    }

    public function saveCode(Request $request,Excel $excel){
        $item = ItemInfo::find($request->input("id"));

        if(empty($item)){return response("false",422);}

        $code = \GuzzleHttp\json_decode($item->code);
        $codeAll = [];
        foreach($code as $value){
            $codeAll = array_merge($codeAll,$value);
        }
        
        $img = $this->createImg($codeAll);
        return $img->response();
    }
    
    public function sendExcel(Request $request,Excel $excel){
        $item = ItemInfo::find($request->input("id"));
        $email = $request->input("email");
        if(empty($item)) return response(json_encode(["message"=>"没有对应的商品信息"]),422);
        $pattern="/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
        if(empty($email) || !preg_match($pattern,$email)){
            return response(json_encode(["message"=>"邮箱格式错误"]),422);
        }
        
        $code = \GuzzleHttp\json_decode($item->code);
        $codeAll = [];
        foreach($code as $value){
            $codeAll = array_merge($codeAll,$value);
        }
        
        $excelCode = $this->formatExcel($codeAll);
        $excel->create("itemCode",function($excel) use ($excelCode){
            $excel->sheet('score', function($sheet) use ($excelCode){
                $sheet->rows($excelCode);
            });
        })->store('xls');
        
        $text = "福利 <{$item->item_name}> 的验证码列表";
        Mail::raw($text,function($message)  use ($email){
            $path = storage_path('exports/itemCode.xls');
            $message->to($email)->subject('福利验证码');
            $message->attach($path,['as'=>'code.xls']);
        });
        if (count(Mail::failures()) > 0) {
            return response(json_encode(["message"=>"邮件发送失败"]),422);
        }else{
            return response(json_encode(["message"=>"邮件发送成功"]));
        }
    }

    protected function createImg($code){
        $length = count($code);
        $height = ceil($length/3) * 40 + 20;

        $img = Image::canvas(600,$height,"#fff");
        $i = 0;
        while($i < $length){
            $p = intval($i/3);
            $q = $i%3;
            $img->text($code[$i], 50+($q*180), 40+($p*40), function ($font) {
                $font->file('storage/SF Arch Rival Extended.ttf');
                $font->size(32);
            });
            $i++;
        }
        return $img;

    }
    
    protected function formatExcel($code){
        $i = 0;
        $row = [];
        $result = [];
        while($i <= count($code)){
            if(($i%3 == 0 && $i > 0) || $i == count($code)){
                $result[] = $row;
                $row = [];
                if($i == count($code)) break;
            }
            $row[] = $code[$i];
            $i++;
        }
        return $result;
    }
    
    protected function randomStr($num,$nowCode = []){
        $check = [];
        foreach ($nowCode as $value){
            $check = array_merge($value,$check);
        }
        $codeArr = [];
        $i = 0;
        $date = date("Y-m-d H:i:s",time());
        while($i<$num){
            $str = str_random(5);
            if (!in_array($str,$check)){
                $codeArr[$date][] = $str;
                $check[] = $str;
                $i++;
            }
        }
        return $codeArr;
    }

    protected function _check(Request $request){
        //参数验证
        $messages = [
            'required' => ':attribute 字段未提交',
            'max'      => ':attribute 输入内容过多' ,
        ];
        $this->validate($request, [
            'item_name' => 'required|max:20|bail',
            'admin_id' => 'required|bail',
            'price' => 'required|max:50|bail',
            'picture' => 'required|bail',
            'num' => 'required|bail',
            'invalid_time' => 'required|bail',
            'state' => 'required|bail',
            "description" => "required|bail"
        ],$messages);
    }

    protected function _insert(array $data,$code){
        return  ItemInfo::create([
            'item_name' => $data['item_name'],
            'admin_id' => $data['admin_id'],
            'price' => $data['price'],
            'picture' => $data['picture'],
            'num' => $data['num'],
            'invalid_time' => $data['invalid_time'],
            'code' => json_encode($code),
            'description' => $data['description']
        ]);
    }
}
