<?php 
   namespace app\admin\validate;
   use think\Validate;
   class Category extends Validate{
   		//验证规则
	    protected $rule =   [
           'cate_name'  => 'require|checkName',        
	                
	    ];
        //错误信息
        protected $message  =   [
           'cate_name.require'=>'分类名称不能为空',                    
             
        ];
        //验证场景
        protected $scene = [  
            'add'=>['cate_name'],
            'edit'=>['cate_name']
        ];

        public function checkName($value,$rule,$data){
          $model = model('Category');
          if (empty($data['cate_id'])) {
              $where = [
                  'cate_name'=>$value
              ];
          }else {
              $where = [
                  'cate_id'=>['neq', $data['cate_id']],
                  'cate_name'=>$value
              ];
          }
          $arr = $model->where($where)->find();
          if ($arr) {
              return '分类已存在';
          }else {
              return true;
          }
        }
           
        
   }