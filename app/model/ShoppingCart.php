<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class ShoppingCart extends Model
{
    protected $table='ShoppingCart';
    protected $schema=[
        'Id'=>'int',
        'UserId'=>'int',
        'GoodsId'=>'int',
        'Count'=>'int'
    ];
}
