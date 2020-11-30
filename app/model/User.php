<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class User extends Model
{
    protected $table='User';
    protected $schema=[
        'Id'=>'int',
        'Username'=>'string',
        'Password'=>'string',
        'ProfilePicUrl'=>'string',
        'Email'=>'string',
        'Sex'=>'int',
        'Birthday'=>'timestamp',
        'Brief'=>'string'
    ];
    public function existUsername($username){
        $res=$this->where('Username','=',$username)->find();
        if(!$res)return false;
        else return true;
    }
    public function checkPassword($username,$password){
        $res=$this->where('Username','=',$username)->find();
        if($res['Password']==$password)return true;
        else return false;
    }
}
