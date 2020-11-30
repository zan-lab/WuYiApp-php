<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class ICH extends Model
{
    protected $table='ICH';
    protected $schema=[
        'Id'=>'int',
        'Name'=>'string',
        'PicUrl'=>'string',
        'Address'=>'string',
        'CatId'=>'int',
        'IsTop'=>'int',

    ];
}
