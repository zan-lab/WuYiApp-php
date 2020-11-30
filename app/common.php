<?php
// 应用公共文件
function commentest(){
    return  "commentest";
}
function Restful($data=null,$code=0,$msg=""){
    if($code==0&&!is_null($data)){
        #如果是传进来数据了并且是code0才去检查数据长度
        if(gettype($data)!='object')
        {
            if(count($data)==0) return ['code'=>'-3','msg'=>'未找到数据！','data'=>[]];
        }
    }
    #如果正常返回的话就把data赋回[]
    if(is_null($data))$data=[];

    return ['code'=>$code,'msg'=>$msg,'data'=>$data];
}


