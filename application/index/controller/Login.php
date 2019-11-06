<?php
namespace app\index\controller;
use think\Controller;

class Login extends Common{
    //注册
    public function register(){
        if(checkRequest()){
            $data=input('post.');
            // dump($data);exit;  // validate验证
            $validate=validate('User');
            //调用验证类的check方法完成验证
            $result=$validate->scene('register')->check($data);
            // dump($result);exit;
            //if判断
            if(!$result){
                fail($validate->getError());
            }
            //入库
            $user_model=model('User');
            $res=$user_model->allowField(true)->save($data);
            $user_id=$user_model->getLastInsId();
            if($res){
                $userInfo=[
                    'user_id'=>$user_id,
                    'user_name'=>$data['user_email']
                ];
                session('userInfo',$userInfo);
                successly('添加成功');


            }else{
                fail("添加失败");
                // echo '添加失败';
            }

        }else{
            $this->view->engine->layout(false);
            return view();
        }
    }

    //登录
    public function login(){
        if(checkRequest()){
            //接受数据
            $account=input('post.account');//接收手机号或者邮箱
            $user_pwd=input('post.user_pwd');
            $remember_me=input('post.remember_me');
            // dump($account);  dump($user_pwd);  dump($remember_me);exit;
            if(empty($account)){
                fail('邮箱或手机号不能为空');
            }else if(empty($user_pwd)){
                fail('密码不能为空');
            }
            //where条件
            $where=[
                'user_email'=>$account,
                'user_tel'=>$account
            ];
            //实例化model 
            $user_model=model('User');
            //入库查询
            $data=$user_model->whereOr($where)->find();
            // echo $user_model->getLastSql();exit;   

            //密码错误3次，密码锁定一小时
            $now=time();
            $error_num=$data['error_num'];
            $last_error_time=$data['last_error_time'];
            // dump($error_num); dump($last_error_time);exit;
           $updWhere=[
               'user_id'=>$data['user_id'],
           ];
           if(!empty($data)){
               $pwd=md5($user_pwd);
            //    dump($pwd);exit;
                if($pwd==$data['user_pwd']){
                    //密码正确
                    // 错误次数>=3次&&当前时间-最后一次错误时间<3600
                    if($error_num>=3&&$now-$last_error_time<3600){
                        //距离解锁还有$mins分钟
                        $mins=60-ceil(($now-$last_error_time)/60);
                        fail('账号锁定中，请'.$mins.'分钟后重新登录！');
                    }
                    //错误次数清零
                    $updateInfo=[
                        'error_num'=>0,
                        'last_error_time'=>null
                    ];
                    //修改错误次数 最后一次错误时间
                    $res=$user_model->where($updWhere)->update($updateInfo);
                    //判断是否记录账号10天存入cookie中
                    if($remember_me=='true'){
                        $sec=60*60*24*10;//存储时间 
                        cookie('account',$account,$sec);
                        cookie('user_pwd',$user_pwd,$sec);
                    }
                    //把用户ID 用户名存入到session中
                    $userInfo=[
                        'user_id'=>$data['user_id'],
                        'user_name'=>$account
                    ];
                    session('userInfo',$userInfo);
                    //同步浏览数据
                    $this->syncHistory();
                    //同步购物车数据
                    $this->syncCart();

                    
                    successly('登录成功');
                }else{
                    //密码错误
                    if($now-$last_error_time>3600){
                        $updateInfo=[
                            'error_num'=>1,
                            'last_error_time'=>$now
                        ];
                        $res=$user_model->where($updWhere)->update($updateInfo);
                        fail('账号或密码有误，您还有2次机会');
                    }else{
                        if($error_num>=3){
                            $mins=60-ceil(($now-$last_error_time)/60);
                            fail('账号锁定中，请'.$mins.'分钟后重新登录');
                        }else{
                            $updateInfo=[
                                'error_num'=>$error_num+1,
                                'last_error_time'=>$now
                            ];
                            $res=$user_model->where($updWhere)->update($updateInfo);
                            $num=3-($error_num+1);//错误次数加一
                            fail('账号或密码有误，您还有'.$num.'次机会');
                        }
                    }

                }
              
           }else{
               fail('账号错误');
           }


        }else{
            $this->view->engine->layout(false);
            return view();
        }

    }

    //验证邮箱唯一性
    public function checkEmail(){
        $user_email=input('post.user_email');
        // dump($user_email);exit;
        $user_model=model('User');
        $where=[
            'user_email'=>$user_email,
        ];
        $res=$user_model->where($where)->find();
        if($res){
            fail('此邮箱已注册过');
        }else{
            successly('');
        }
    }

    //发送邮箱
    public function sendEmail(){
        //接受type值判断发送什么邮件
        $type=input('post.type');
        // dump($type);exit;
        if($type==1){
            $user_email=input('post.user_email');
            //随机生成验证码
            $code=rand(100000,999999);
            //发送邮件
            $res=sendEmail($user_email,$code);
            if($res){
                $sendInfo=[
                    'senTime'=>time(),
                    'sendCode'=>$code,
                    'sendEmail'=>$user_email
                ];
                session('sendInfo',$sendInfo);
                successly('发送成功');
            }else{
                fail('发送失败');
            }
        }else if($type==2){
            //获取要接受的邮箱账号
            $email=input('post.email');
            $user_model=model('User');
            $where=[
                'user_email'=>$email
            ];
            $res=$user_model->where($where)->select();
            // dump($res);exit;
            // dump($res[0]['user_id']);exit;
            //要发送的内容
            $code=md5($res[0]['user_id'].$res[0]['user_pwd']);
            $code="确认找回密码".$code;
            // dump($code);exit;
                
            //发送邮件
            $res=sendEmail($email,$code);
            if($res){
                $sendInfo=[
                    'sendCode'=>$code,
                    'sendEmail'=>$email
                ];
                session('sendInfo',$sendInfo);
                successly('发送成功');
                // $this->success('发送成功',url('Exam/index'));
                // echo "发送成功";
            }else{
                fail('发送失败');
                // echo "发送失败";
            }
            
            

            
        }
        
    }


    /**退出 */
    public function quit(){
		session('userInfo',null);
		$this->redirect('Login/login');
    }


    /**考试发送邮件 */

    public function sendemails(){
      
    }
    
 

}
?>