<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class ShoppingOrder extends Model
{
    protected $table='ShoppingOrder';
    protected $schema=[
        "Id"=>'int',
        'UserId'=>'int',
        'GoodsId'=>'int',
        'Count'=>'int'
    ];
}
