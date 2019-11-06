<?php
namespace app\admin\model;
use think\Model;
class Admin extends Model
{
	//做数据库完成
	protected $insert = ['salt'];
	protected $salt;
	//修改器 密码加密
	public function setAdminPwdAttr($value){
		//生成盐值
		$this->salt=$salt=createSalt();
		//用特有的加密方式 生成密码
		$pwd = createPwd($value,$salt);
		return $pwd;
	}
		//数据补全
	public function setSaltAttr(){
		return $this->salt;
	}
	


}