<?php
namespace app\index\controller;
use think\Controller;
class Exam extends Common{
    /**找回密码页面 */
    public function exam(){
        return view();
    }

    /**重置密码模板 */
    public function regit(){
        return view();

    }
    /**修改密码 */
    public function updo(){
        //获取密码
        $user_pwd=input('post.pwd');
        // dump($user_pwd);exit;
        $user_pwd=md5($user_pwd);
        $user_model=model('User');
        $upd=[
            'user_pwd'=>$user_pwd
        ];
        $where=[
            'user_email'=>'2401538133@qq.com'
        ];
        $res=$user_model->update($upd,$where);
        // dump($res);exit;
        // echo $user_model->getLastSql();exit;
        if($res){
            // $this->success('修改成功',url('Login/login'));
            successly('修改成功');
        }else{
            // $this->error('修改失败');
            fail('修改失败');
        }
    }
}
?>