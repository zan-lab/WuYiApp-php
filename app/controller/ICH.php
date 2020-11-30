<?php
declare (strict_types = 1);

namespace app\controller;

use think\exception\ValidateException;
use think\Request;
use app\model;
use think\Response;

class ICH
{

    public function CatList($limit=0,$page=1)
    {
        $cat=new model\ICHCat();
        if($limit==0)
        {
            $res=$cat->select()->toArray();
            return json(Restful($res));
        }
        else {
            $count=$cat->count();
            $res=$cat->limit($limit*($page-1),$limit)->select()->toArray();
            return json(["code"=>0,"msg"=>"","count"=>$count,"data"=>$res]);
        }

    }
    public function TopList()
    {
        return json(Restful(model\ICH::where('IsTop','=','1')->select()->toArray()));
    }
    public function TopArticleList(){
        $atc=new model\ICHArticle();
        return json(Restful($atc->where('IsTop','=','1')->field("ICHId,Title")->select()->toArray()));
    }
    public function ListByCatname($catname){
        $cat=model\ICHCat::where('Name','=',$catname)->find();
        if($cat==null)return json(Restful([],-3,"分类名称未找到"));
        else{
            return $this->ListByCatId($cat->Id);
        }
    }
    public function ListByCatId($catid)
    {
        if(model\ICHCat::find($catid)==null){return json(Restful([],-1,"分类id不可用"));
        }
        return json(Restful(model\ICH::where('CatId','=',$catid)->select()->toArray()));
    }
    public function ArticleByIchid($ichid)
    {
        if(model\ICH::find($ichid)==null){
            return json(Restful([],-1,"非遗id不可用"));
        }
        return json(Restful(model\ICHArticle::where('IchId','=',$ichid)->select()->toArray()));
    }
    public function ArticleById($id)
    {
        $atc=model\ICHArticle::find($id);
        if($atc==null) return json(Restful([],-3,"文章id未找到"));
        else{
            return json(Restful($atc));
        }
    }
    public function AddCat($name)
    {
        $cat=new model\ICHCat();
        if($cat->where('Name','=',$name)->find()!=null)
            return json(Restful([],-4,"非遗分类名称已存在"));
        $cat->Name=$name;
        $cat->save();
        return json(Restful());
    }
    public function DeleteCat($id)
    {
        $cat=model\ICHCat::find($id);
        if($cat==null)return json(Restful([],"-1","分类id不存在"));
        if($cat->delete())
        {
            return json(Restful());
        }
        else return json(Restful([],-5,"删除失败"));
    }
    public function ChangeCat($id,$name)
    {
        $cat=model\ICHCat::find($id);
        if($cat==null)return json(Restful([],-1,'非遗分类未找到'));
        if($cat->where('Name','=',$name)->find()!=null)
            return json(Restful([],-4,"非遗分类名称已存在"));
        $cat->Name=$name;
        if($cat->save())return json(Restful());
        else return json(Restful([],5,'修改失败'));
    }
    public function UploadPic()
    {
        if(request()->isPost())
        {
            $type='image';
            try {
                $file = request()->file('file');
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
                $filename = \think\facade\Filesystem::disk('public')->putFile('ICHPic', $file);
                if ($filename){
                    $src = 'http://ich.laoluoli.cn/uploads/'.$filename;
                    return json(Restful(['src'=>$src]));
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

    public function Add($name,$catid,$picurl="",$address="")
    {
        #定义默认值
        $defaultpicurl="";
        $defaultaddress="";

        $ich=new model\ICH();
        if($ich->where('Name','=',$name)->find()!=null)
            return json(Restful([],-4,'非遗名称已存在'));
        if($picurl==="")$picurl=$defaultpicurl;
        if($address==="")$address=$defaultaddress;
        $ich->Name=$name;
        $ich->PicUrl=$picurl;
        $ich->Address=$address;
//        $cat=new model\ICHCat();
//        $ich->CatId=$cat->GetIdByName($catname);
        $ich->CatId=$catid;
        if($ich->save())return json(Restful());
        else return json(Restful([],-5,"添加失败"));

    }
    public function Edit($id,$name,$picurl,$address,$catid)
    {
        $ich=model\ICH::find($id);
        if($ich==null)return json(Restful([],-1,"非遗id未找到"));
        $ich->Name=$name;
        $ich->PicUrl=$picurl;
        $ich->Address=$address;
//        $cat=new model\ICHCat();
//        $ich->CatId=$cat->GetIdByName($catname);
        $ich->CatId=$catid;
        if($ich->save())return json(Restful());
        else return json(Restful([],-5,"修改失败"));
    }
    public function Delete($id)
    {
        $ich=model\ICH::find($id);
        if($ich==null)return json(Restful([],-1,"非遗id未找到"));
        if($ich->delete())return json(Restful());
        return json(Restful([],-5,"删除失败"));
    }
    public function AddArticle($ichid,$title,$content,$istop)
    {
        $atc=new model\ICHArticle();
        $atc->ICHId=$ichid;
        $atc->Title=$title;
        $atc->Content=$content;
        if($istop==1)$atc->IsTop=1;
        else $atc->IsTop=0;
        if($atc->save())return json(Restful());
        else return json(Restful([],-5,"添加失败"));
    }
    public function ChangeArticle($id,$ichid,$title,$content,$istop)
    {
        $atc=model\ICHArticle::find($id);
        if($atc==null)return json(Restful([],-1,"文章id未找到"));
        $atc->Title=$title;
        $atc->IchId=$ichid;
        $atc->Content=$content;
        $atc->IsTop=$istop;
        if($atc->save())return json(Restful());
        else return json(Restful([],-5,"修改失败"));
    }
    public function TopArticle($id,$istop)
    {
        $atc=model\ICHArticle::find($id);
        if($atc==null)return json(Restful([],-1,"文章id未找到"));
        $atc->IsTop=$istop;
        if($atc->save())return json(Restful());
        else return json(Restful([],-5,"修改失败"));
    }
    public function DeleteArticle($id)
    {
        $atc=model\ICHArticle::find($id);
        if($atc==null)return json(Restful([],-1,"文章id未找到"));
        if($atc->delete())return json(Restful());
        else return json(Restful([],-5,"删除失败"));
    }
    public function List($limit=0,$page=1)
    {
        $ich=new model\ICH();
        if($limit==0)
        return json(Restful($ich->select()->toArray()));
        else{
            $count=$ich->count();
            $data=$ich->limit($limit*($page-1),$limit)->select()->toArray();
            return json(["code"=>0,"msg"=>"","count"=>$count,"data"=>$data]);
        }
    }
    public function ArticleList($limit=0,$page=1)
    {
        if($limit==0)
        return json(Restful(model\ICHArticle::selete()->toArray()));
        else {
            $article=new model\ICHArticle();
            $count=$article->count();
            $res=$article->limit(($page-1)*$limit,$limit)->select()->toArray();
            return json(["code"=>0,"msg"=>"","count"=>$count,"data"=>$res]);
        }
    }
}
