<?php
namespace app\index\model;
use think\Model;
class Address extends Model{
    protected $updateTime=false;
    protected $insert = ['user_id'];
    public function setUserIdAttr(){
        return session('userInfo.user_id');
    }
}
?>