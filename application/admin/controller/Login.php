<?php

namespace app\admin\controller;

use think\Controller;

class Login extends Controller
{
	/**登录 */
	public function login(){
		if(checkRequest()){
			$admin_name = input('post.admin_name');
			$admin_pwd = input('post.admin_pwd');
			$code = input('post.code');
			if(empty($admin_name)){
				fail('管理员名称不能为空');
			}
			if(empty($admin_pwd)){
				fail('密码不能为空');
			}
			if(empty($code)){
				fail('验证码不能为空');
			}else if(!captcha_check($code)){
				fail('验证码错误');
			}

			$admin_model = model('Admin');
			$where = [
				'admin_name'=>$admin_name
			];
			$arr = $admin_model->where($where)->find();
			if(!empty($arr)){
				$salt = $arr['salt'];
				$pwd = createPwd($admin_pwd,$salt);
				if($pwd == $arr['admin_pwd']){
					$adminInfo = [
						'admin_id'=>$arr['admin_id'],
						'admin_name'=>$admin_name
					];
					session('adminInfo',$adminInfo);
					successly('登录成功');
				}else{
					fail('登录失败');
				}
			}else{
				fail('账号或密码有误');
			}
		}else{
			$this->view->engine->layout(false);
			return view();
		}			
				
	}

	/**退出登录 */
	public function quit(){
		session('adminInfo',null);
		$this->redirect('Login/login');
	}
}