<?php
declare (strict_types = 1);

namespace app\controller;

use think\Request;
use app\model;

class System
{
   public function usernum()
   {
       $user=new model\User();
       $count=$user->count();
       return json(Restful(['count'=>$count]));
   }
}
