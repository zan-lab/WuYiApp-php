<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
/**
 * @mixin \think\Model
 */
class ICHCat extends Model
{
    protected $table='ICHCat';
    protected $schema=[
        'Id'=>'string',
        'Name'=>'string'
    ];
    public function GetIdByName($name)
    {
       $res=self::where('Name','=',$name)->find();
        if($res==null)return null;
        else return $res->Id;
    }
}
