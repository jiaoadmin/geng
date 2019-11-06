<?php 
   namespace app\admin\validate;
   use think\Validate;
   class Brand extends Validate{
   		//验证规则
	    protected $rule =   [
           'brand_name'  => 'require|checkName|unique:brand',
           'brand_url'=>'require|url',
           'brand_logo'=>'require',
	                
	    ];
        //错误信息
        protected $message  =   [
           'brand_name.require'=>'品牌名称不能为空',
           'brand_name.unique'=>'品牌名称已经存在',
           'brand_url.require'=>'网址不能为空',           
           'brand_url.url'=>'网址格式错误',
           'brand_logo.require'=>'请上传品牌logo',
             
        ];
        //验证场景
        protected $scene = [
            'editbrand_name'  =>  ['brand_name'],
            'editbrand_url'  =>  ['brand_url'],
            'editbrand_logo'  =>  ['brand_logo'],
            'add'=>['brand_name','brand_url','brand_logo'],
            'edit'=>['brand_name','brand_url','brand_logo']
            
        ];
            
        //自定义验证账号
        public function checkName($value,$rule,$data)
        {
            //定义一个正则，和前面js验证保持一致
            $reg="/([\x{4e00}-\x{9fa5}]{2,4})/u";
            //判断正则是否正确preg_match
            if(!preg_match($reg,$value)){
                return '品牌名由2到4位汉字组成';
            }else{
                return true;                 
            }
        } 
   }