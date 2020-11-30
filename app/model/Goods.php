<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Goods extends Model
{
    protected $table='Goods';
    protected $schema=[
        'Id'=>'int',
        'Name'=>'string',
        'Price'=>'float',
        'Brief'=>'string',
        'PicUrl'=>'string',
        'CatId'=>'int',
        'IsTop'=>'int'
    ];
}
