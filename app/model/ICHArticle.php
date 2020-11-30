<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class ICHArticle extends Model
{
    protected $table='ICHArticle';
    protected $schema=[
        'Id'=>'int',
        'ICHId'=>'int',
        'Title'=>'string',
        'Content'=>'string',
        'Comment'=>'string',
        'IsTop'=>'int'
    ];
}
