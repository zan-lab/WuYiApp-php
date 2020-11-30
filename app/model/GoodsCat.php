<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class GoodsCat extends Model
{
    protected $table='GoodsCat';
    protected $schema=[
        'Id'  =>'int',
        'Name'=>'string'
    ];
    public function GetOneByName($name){
        return  $this->where('Name','=',$name)->find();
    }
}
