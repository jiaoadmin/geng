<?php
namespace app\index\controller;
use think\Controller;
class User extends Common{

    /**个人中心首页 */
    public function index(){
        if($this->checkLogin()){
            return view();
        }else{
            
            $this->error('请先登录',url('Login/login'));exit;
        }

    }



}
?>