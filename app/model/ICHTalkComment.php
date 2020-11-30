<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class ICHTalkComment extends Model
{
    protected $table='ICHTalkComment';
    protected $schema=[
        "Id"=>'int',
        "UserId"=>'int',
        'TalkId'=>'int',
        "Content"=>'string',
        'CreateDate'=>'timestamp'
    ];
}
