<?php 
    namespace app\index\validate;
    use think\Validate;
    class User extends Validate{
        //验证规则
        protected $rule = [
            'user_email'=>'require|email|unique:User|checkEmail',
            'user_code'=>'require|checkCode',
            'user_pwd'=>'require|checkPwd',
            'user_pwd1'=>'require|confirm:user_pwd'
        ];

        protected $message = [
            'user_email.require'  =>  '邮箱必填',
            'user_email.email' =>  '请填写正确邮箱格式',
            'user_email.unique' =>  '邮箱已存在',
            'user_code.require'=>'验证码必填',
            'user_pwd.require'=>'密码必填',
            'user_pwd1.require'=>'确认密码必填',
            'user_pwd1.confirm:user_pwd'=>'确认密码与密码一致',

        ];

        protected $scene = [
            'addEmail'   =>  ['user_email'=>'require|email|unique:user'],
            'register'  =>  ['user_email','user_code','user_pwd','user_pwd1'],
        ];

        //自定义验证邮箱是否和存在session中的值一样
        public function checkEmail($value,$rule,$data){
            if($value!=session('sendInfo.sendEmail')){
                return '注册用户与接收验证码用户不一致';
            }else{
                return true;
            }
        }

        //自定义验证码是否和session中是否一样  是否超时
        public function checkCode($value,$rule,$data){
            if($value!=session('sendInfo.sendCode')){
                return '请写正确的验证码';
            }else if(time()-session('sendInfo.senTime')>60){
                return '验证码已失效，一分钟内输入';
            }else{
                return true;
            }
        }

        //自定义密码格式
        public function checkPwd($value,$rule,$data){
            $reg='/^.{5,}$/';//密码位5位以上任意字符
            if(!preg_match($reg,$value)){
                return '密码位5位以上任意字符';
            }else{
                return true;
            }
        }



   }
?>