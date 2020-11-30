<?php
declare (strict_types = 1);

namespace app\controller;


use think\exception\ValidateException;
use think\Request;
use app\model;
class User
{
    public function List($limit=0,$page=1)
    {
        $user=new model\User();
        if($limit==0)
        {
            $res=$user->select()->toArray();
        }
        $res=$user->limit($limit*($page-1),$limit)->select()->toArray();
        $count=$user->count();
        return json(["code"=>0,"msg"=>"","count"=>$count,"data"=>$res]);
    }


    public function Login($username,$password)
    {
        $user=new model\User();
        if($user->existUsername($username))
        {
            if($user->checkPassword($username,$password)){
                $res=$user->where('Username','=',$username)->find();
                return json(Restful($res));
            }
            else return json(Restful([],-3,'密码错误！'));
        }
        else return json(Restful([],-3,'用户名不存在！'));
    }
    public function index($id)
    {
        $user=model\User::find($id);
        if($user){
            return json(Restful($user));
        }
        else return json(Restful([],-3,"用户id不存在！"));
    }

    public function UploadPhoto($userid)
    {
        $user=model\User::find($userid);
        if($user){
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
                    $filename = \think\facade\Filesystem::disk('public')->putFile('ProfilePic', $file);
                    if ($filename){
                        $src = 'http://ich.laoluoli.cn/uploads/'.$filename;
                        $user->ProfilePicUrl=$src;
                        if($user->save())
                            return json(Restful());
                        else
                            return json(Restful([],-5,'地址保存失败'));
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
        else return json(Restful([],-3,"用户id不存在！"));

    }
    public function ChangePwd($id,$oldpwd,$newpwd)
    {
        $user=model\User::find($id);
        if($user){
            if($user->Password!=$oldpwd)
                return json(Restful([],-1,"原密码错误！"));
            else{
                $user->Password=$newpwd;
                $user->save();
                return json(Restful());
            }
        }
        else return json(Restful([],-3,"用户id不存在!"));
    }
    public function ResetPwd($username,$email)
    {
        #设置重置后的默认密码
        $resetpwd='123456';

        $user=model\User::where('Username','=',$username)->find();
        if($user){
            if($user->Email==$email){
                $user->Password=$resetpwd;
                $user->save();
                return json(Restful(['Password'=>$resetpwd]));
            }
            else return json(Restful([],-1,"邮箱不正确!"));
        }
        else return json(Restful([],-3,"用户名不存在!"));
    }
    public function Add($username,$password,$email)
    {
        $user=new model\User();
        if($user->existUsername($username)){
            return json(Restful([],-4,"用户名已存在"));
        }
        $user->Username=$username;
        $user->Password=$password;
        $user->Email=$email;
        if($user->save()){
            return json(Restful());
        }
        else return json(Restful([],-5,"添加失败！"));
    }
    public function Edit($id,$sex=2,$birthday=null,$brief="")
    {
        $user=model\User::find($id);
        if($user){
            $user->Sex=$sex;
            $user->Brief=$brief;
            if($birthday)
                $user->Birthday=date("Y-m-d",strtotime($birthday));
            $user->save();
            return json(Restful());
        }
        else return json(Restful([],-3,"用户id不存在!"));
    }
    public function Delete($id)
    {
        $user=model\User::find($id);
        if($user){
            $user->delete();
            return json(Restful());
        }
        else return json(Restful([],-3,"用户id不存在!"));
    }
}
