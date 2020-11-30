<?php
declare (strict_types = 1);

namespace app\controller;

use think\exception\ValidateException;
use think\facade\Db;
use think\Request;
use app\model;
use think\Response;
class ICHTalk
{
   public function List($limit=0,$page=1)
   {
       if($limit==0)
       {
           $res=model\ICHTalk::Order("CreateDate","desc")->select()->toArray();
           return json(Restful($res));
       }
       else {
           $talk=new model\ICHTalk();
           $count=$talk->count();
           $res=$talk->limit($limit*($page-1),$limit)->Order("CreateDate","desc")->select()->toArray();
           return json(['code'=>0,'msg'=>"",'data'=>$res,'count'=>$count]);
       }
   }

//    public function ListPage($limit,$page)
//    {
//        //无限加载逻辑
//        $talk=new model\ICHTalk();
//        $count=$talk->count();
//        if($count==0)return  return ['code'=>'-3','msg'=>'未找到数据！','data'=>[]];
//        if($limit>$count)
//        {
//            $left=$limit;
//            while($left>0){
//            }
//            $res=$talk->select()->toArray();
//            array_merge()
//        }
//        $num=($limit*($page-1))%$count;
//
//        $count=$talk->count();
//        $res=$talk->limit($limit*($page-1),$limit)->select()->toArray();
//        return json(['code'=>0,'msg'=>"",'data'=>$res,'count'=>$count]);
//
//    }
   public function TopList()
   {
       return json(Restful(model\ICHTalk::where('IsTop','=','1')->select()->toArray()));
   }
   public function CommentsById($talkid)
   {
       $res=model\ICHTalkComment::where('TalkId','=',$talkid)->Order('CreateDate','asc')->select()->toArray();
       return json(Restful($res));
   }
   public function CommentsList($limit=0,$page=1)
   {
       if($limit==0)
       {
           $res=model\ICHTalkComment::Order("CreateDate","desc")->select()->toArray();
           return json(Restful($res));
       }
       else {
           $comment=new model\ICHTalkComment();
           $count=$comment->count();
           $res=$comment->limit($limit*($page-1),$limit)->select()->toArray();
           return json(['code'=>0,'msg'=>"",'data'=>$res,'count'=>$count]);
       }
   }
   public function Like($talkid){
       Db::table('ICHTalk')
           ->where('Id', '=',$talkid)
           ->inc('LikeCount')
           ->update();
   }
    public function CancelLike($talkid){
        Db::table('ICHTalk')
            ->where('Id', '=',$talkid)
            ->dec('LikeCount')
            ->update();
    }
   public function AddComment($talkid,$userid,$content)
   {
       $talk=model\ICHTalk::find($talkid);
       if($talkid==null)return json(Restful([],-1,"非遗说id未找到"));
       $comment=new model\ICHTalkComment();
       $comment->UserId=$userid;
       $comment->TalkId=$talkid;
       $comment->Content=$content;
       if($comment->save())return json(Restful());
       else return json(Restful([],-5,'添加错误'));
   }
   public function Forward($talkid,$userid){
       $oldtalk=model\ICHTalk::find($talkid);
       if($talkid==null)return json(Restful([],-1,"非遗说id未找到"));
       $talk=new model\ICHTalk();
       $talk->UserId=$userid;
       $talk->Content=$oldtalk->Content;
       $talk->Photo1Url=$oldtalk->Photo1Url;
       $talk->Photo2Url=$oldtalk->Photo2Url;
       $talk->Photo3Url=$oldtalk->Photo3Url;
       $talk->LikeContent=0;
       if($talk->save())return json(Restful());
       else return json(Restful([],-5,'转发错误'));
   }
   public function UserList($userid,$limit=0,$page=1){
       if($limit==0)
       {
           $res=model\ICHTalk::where('UserId','=',$userid)->Order("CreateDate","desc")->select()->toArray();
           return json(Restful($res));
       }
       else {

           $res=model\ICHTalk::where('UserId','=',$userid)->Order("CreateDate","desc")->select()->toArray();
           $count=count($res);
           return json(['code'=>0,'msg'=>"",'data'=>$res,'count'=>$count]);
       }
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
               $filename = \think\facade\Filesystem::disk('public')->putFile('ICHTalkPic', $file);
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
   public function Add($userid,$content,$photo1url="",$photo2url="",$photo3url="")
    {
        $talk=new model\ICHTalk();
        $talk->UserId=$userid;
        $talk->Content=$content;
        $talk->Photo1Url=$photo1url;
        $talk->Photo2Url=$photo2url;
        $talk->Photo3Url=$photo3url;
        $talk->LikeContent=0;
        if($talk->save())return json(Restful());
        else return json(Restful([],-5,'添加错误'));
    }
    public function Edit($id,$content,$photo1url="",$photo2url="",$photo3url="")
    {
        $talk=model\ICHTalk::find($id);
        if($talk==null)return json(Restful([],-1,"非遗说id未找到"));
        $talk->Content=$content;
        $talk->Photo1Url=$photo1url;
        $talk->Photo1Ur2=$photo2url;
        $talk->Photo1Ur3=$photo3url;
        if($talk->save())return json(Restful());
        else return json(Restful([],-5,'修改错误'));
    }
    public function Delete($id)
    {
        $talk=model\ICHTalk::find($id);
        if($talk==null)return json(Restful([],-1,"非遗说id未找到"));
        if($talk->delete())return json(Restful());
        else return json(Restful([],-5,'删除错误'));
    }

}
