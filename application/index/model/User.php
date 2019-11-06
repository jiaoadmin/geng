<?php
namespace app\index\model;
use think\Model;
class User extends Model{
    
    protected $updateTime=false;
    public function setUserPwdAttr($value){
        return md5($value);
    }
}


?>