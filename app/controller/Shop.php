<?php
declare (strict_types = 1);

namespace app\controller;

use app\model;
use think\exception\ValidateException;
use think\Request;
use think\Response;

class Shop
{
    public function CatList($limit=0,$page=1)
    {
        if($limit==0)
        return json(Restful(model\GoodsCat::select()->toArray()));
        else {
            $cat=new model\GoodsCat();
            $count=$cat->count();
            $data=$cat->limit($limit*($page-1))->select()->toArray();
            return json(['data'=>$data,'count'=>$count,'msg'=>'','code'=>0]);
        }
    }
    public function CatById($id){
        $cat=model\GoodsCat::find($id);
        if($cat==null)return json(Restful([],-3,"分类id未找到"));
        return json(Restful($cat));
    }
    public function Goods($id)
    {
        $goods=model\Goods::find($id);
        if($goods==null)return json(Restful([],-3,"商品id未找到"));
        return json(Restful($goods));
    }
    public function TopGoods()
    {
        return json(Restful(model\Goods::where('IsTop','=','1')->select()->toArray()));
    }
    public function GoodsByCatname($catname)
    {
        $cat=(new model\GoodsCat())->GetOneByName($catname);
        if($cat==null)return json(Restful([],-3,'未找到catname'));
        else{
            return $this->GoodsByCatid($cat->Id);
            //return $cat;
        }

    }
    public function GoodsByCatid($catid)
    {
        if(model\GoodsCat::find($catid)==null)return json(Restful([],-1,"分类id未找到"));
        $goods=new model\Goods();
        return json(Restful($goods->where('CatId','=',$catid)->column('Id,PicUrl,Name,Price')));
    }
    public function AddCart($userid,$goodsid,$count=1)
    {
        $user=model\User::find($userid);
        if($user==null)return json(Restful([],-1,"用户不存在"));
        $goods=model\Goods::find($goodsid);
        if($goods==null)return json(Restful([],-1,"商品不存在"));
        $cart=model\ShoppingCart::where('UserId','=',$userid)->where('GoodsId','=',$goodsid)->find();
        if($cart!=null)
            $cart->Count=$cart->Count+$count;
        else{
            $cart=new model\ShoppingCart();
            $cart->UserId=$userid;
            $cart->GoodsId=$goodsid;
            $cart->Count=$count;
        }
        if($cart->save())return json(Restful());
        else return json(Restful([],-5,"添加失败"));
    }
    public function Buy($userid,$goodsid,$count=1)
    {
        $user=model\User::find($userid);
        if($user==null)return json(Restful([],-1,"用户不存在"));
        $goods=model\Goods::find($goodsid);
        if($goods==null)return json(Restful([],-1,"商品不存在"));
        $order=new model\ShoppingOrder();
        $order->UserId=$userid;
        $order->GoodsId=$goodsid;
        $order->Count=$count;
        if($order->save())return json(Restful());
        else return json(Restful([],-5,"添加失败"));
    }
    public function CartChangeCount($id,$newcount)
    {
        $cart=model\ShoppingCart::find($id);
        if($cart==null)return json(Restful([],-1,'未找到该购物车id'));
        if($newcount<=0)
        {
            $cart->delete();return json(Restful());
        }
        $cart->Count=$newcount;
        if($cart->save())return json(Restful());
        else return json(Restful([],-5,'修改错误'));
    }
    public function DeleteCart($id)
    {
        $cart=model\ShoppingCart::find($id);
        if($cart==null)return json(Restful([],-1,'购物车记录id未找到'));
        if($cart->delete())return json(Restful());
        else return json(Restful([],-5,'删除失败'));
    }
    public function AddCat($name)
    {
        $cat=new model\GoodsCat();
        if($cat->where('Name','=',$name)->find()!=null)
            return json(Restful([],-4,"商品分类名称已存在"));
        $cat->Name=$name;
        $cat->save();
        return json(Restful());
    }
    public function ChangeCat($id,$name)
    {
        $cat=model\GoodsCat::find($id);
        if($cat==null)return json(Restful([],-1,'商品分类未找到'));
        if($cat->where('Name','=',$name)->find()!=null)
            return json(Restful([],-4,"商品分类名称已存在"));
        $cat->Name=$name;
        if($cat->save())return json(Restful());
        else return json(Restful([],5,'修改失败'));
    }
    public function DeleteCat($id)
    {
        $cat=model\GoodsCat::find($id);
        if($cat==null)return json(Restful([],"-1","分类id不存在"));
        if($cat->delete())
        {
            return json(Restful());
        }
        else return json(Restful([],-5,"删除失败"));
    }

    public function UploadPic()
    {
        if(request()->isPost())
        {
            $type='image';
            try {
                $file = request()->file('image');
                if($file==null)return json(Restful([],-2,'参数缺失'));
                //从config/upload.php配置文件中读取允许上传的文件后缀和大小
                $suffix_config = config('upload.suffix_arr');
                $size_config = config('upload.size_arr');
                if (empty($size_config[$type]) || empty($size_config[$type])){
                    return json(Restful([],-5,'系统错误'));
                }else{
                    $suffix = $suffix_config[$type];
                    $size = $size_config[$type];
                }
                //验证器验证上传的文件
                validate(['file'=>[
                    //限制文件大小
                    'fileSize'      =>  $size * 1024 * 1024,
                    //限制文件后缀
                    'fileExt'       =>  $suffix
                ]],[
                    'file.fileSize' =>  '上传的文件大小不能超过'.$size.'M',
                    'file.fileExt'  =>  '请上传后缀为:'.$suffix.'的文件'
                ])->check(['file'=>$file]);
                //上传文件到本地服务器
                $filename = \think\facade\Filesystem::disk('public')->putFile('GoodsPic', $file);
                if ($filename){
                    $src = 'http://ich.laoluoli.cn/uploads/'.$filename;
                    return json(Restful(['url'=>$src]));
                }else{
                    return json(['code'=>-5,'msg'=>'上传失败','data'=>[]]);
                }
            }catch (ValidateException $e){
                return json(['code'=>-1,'msg'=>$e->getMessage()]);
            }
        }else{
            return json(['code'=>-5,'msg'=>'非法请求']);
        }
    }

    public function AddGoods($name,$price,$brief,$picurl,$catid)
    {
        $goods=new model\Goods();
        $goods->Name=$name;
        $goods->Price=$price;
        $goods->Brief=$brief;
        $goods->PicUrl=$picurl;
        $goods->CatId=$catid;
        if($goods->save())return json(Restful());
        else return json(Restful([],-5),'添加失败');
    }
    public function GoodsList($limit=0,$page=1)
    {
        $goods=new model\Goods();
        if($limit==0){
            return json(Restful($goods->select()->toArray()));
        }
        else {
            $count=$goods->count();
            $data=$goods->limit($limit*($page-1),$limit)->select()->toArray();
            return json(['data'=>$data,'count'=>$count,'msg'=>'','code'=>0]);
        }
    }
    public function ChangeGoods($id,$name,$price,$brief,$picurl,$catid)
    {
        $goods=model\Goods::find($id);
        if($goods==null)return json(Restful([],-1,'商品id未找到'));
        $goods->Name=$name;
        $goods->Price=$price;
        $goods->Brief=$brief;
        $goods->PicUrl=$picurl;
        $goods->CatId=$catid;
        if($goods->save())return json(Restful());
        else return json(Restful([],-5),'修改失败');
    }
    public function DeleteGoods($id)
    {
        $goods=model\Goods::find($id);
        if($goods==null)return json(Restful([],-1,'商品id未找到'));
        if($goods->delete())return json(Restful());
        else return json(Restful([],-5),'删除失败');
    }
    public function UserCart($userid)
    {
        if(model\User::find($userid)==null)return json(Restful([],-1,'用户Id未找到'));
        return json(Restful(model\ShoppingCart::where('UserId','=',$userid)->select()->toArray()));
    }
    public function UserOrder($userid)
    {
        if(model\User::find($userid)==null)return json(Restful([],-1,'用户Id未找到'));
        return json(Restful(model\ShoppingOrder::where('UserId','=',$userid)->order('CreateTime','desc')->select()->toArray()));
    }
    public function AllOrder($limit=0,$page=1)
    {
        if($limit==0)
        return json(Restful(model\ShoppingOrder::select()->toArray()));
        else{
            $order=new model\ShoppingOrder();
            $count=$order->count();
            $data=$order->limit($limit*($page-1),$limit)->select()->toArray();
            return json(['data'=>$data,'count'=>$count,'msg'=>'','code'=>'0']);
        }
    }
}
