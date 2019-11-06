<?php 
   namespace app\admin\validate;
   use think\Validate;
   class Admin extends Validate{
   		//验证规则
	    protected $rule =   [
           'admin_name'  => 'require|checkName|unique:admin',
           'admin_pwd'=>'require|checkPwd',
           'admin_email'=>'require|email',
           'admin_tel'=>'require|checkTel'
	                
	    ];
        //错误信息
        protected $message  =   [
           'admin_name.require'=>'管理员姓名不能为空',
           'admin_name.unique'=>'管理员姓名已经存在',
           'admin_pwd.require'=>'密码不能为空',
           'admin_email.require'=>'邮箱不能为空',
           'admin_email.email'=>'邮箱格式错误',
           'admin_tel.require'=>'手机号必填',
             
        ];
        //验证场景
        protected $scene = [
            'editadmin_name'  =>  ['admin_name'],
            'editadmin_email'  =>  ['admin_email'],
            'editadmin_tel'  =>  ['admin_tel'],
            'add'=>['admin_name','admin_pwd','admin_email','admin_tel'],
            'edit'=>['admin_name','admin_email','admin_tel']
        ];
            
        //自定义验证账号
        public function checkName($value,$rule,$data)
        {
            //定义一个正则，和前面js验证保持一致
            $reg='/^[a-z_]\w{3,11}$/i';
            //判断正则是否正确preg_match
            if(!preg_match($reg,$value)){
                return '账号数字 字母 下划线组成 非数字开头 4-12位';
            }else{
              return true;
                 
            }
        }
        //自定义验证密码
        public function checkPwd($value,$rule,$data){
            $reg='/^.{6,16}$/';
            if(!preg_match($reg,$value)){
                return '密码允许6-16位';
            }else{
                return true;
            }
        }
        //自定义验证手机号
        public function checkTel($value,$rule,$data){
            $reg='/^1[3-9]\d{9}$/';
            if(!preg_match($reg,$value)){
                return '请填写正确的手机号';
            }else{
                return true;
            }
        }
            
        
        
   }