<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class ICHTalk extends Model
{
    protected $table='ICHTalk';
    protected $schema=[
        'Id'=>'int',
        'UserId'=>'int',
        'Content'=>'string',
        'LikeCount'=>'int',
        'CreateDate'=>'timestamp',
        'Photo1Url'=>'string',
        'Photo2Url'=>'string',
        'Photo3Url'=>'string',
        'IsTop'=>'int',
    ];
}
