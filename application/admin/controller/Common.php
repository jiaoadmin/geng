<?php
namespace app\admin\controller;
use think\Controller;

class Common extends Controller
{
	function _initialize()
	{
		//判断是否有session信息
		if(!session("?adminInfo")){
			$this->error('请先登录',url('Login/login'));
		}	
	}

	function getCateInfo()
	{
		$model = model('category');
		$cateInfo = $model->select();
		$cateInfo = collection($cateInfo)->toArray();
		$arr = getCateInfo($cateInfo);
		return $arr;
	}

}
?>