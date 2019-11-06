<?php
namespace app\admin\controller;

class Category extends Common 
{
	/** 分类添加展示页面 */
	public function cateAdd(){
		if(checkRequest()){
			 //接收表单提交过来的数据
	        $data = input('post.');
	        //验证器验证
	        $validate=validate('Category');
	        //调用验证类的check方法完成验证
	        $result=$validate->scene('add')->check($data);
	        //if判断
	          if(!$result){
	             fail($validate->getError());
	          }
	          //数据入库
	        $res=model('category')->save($data);
	        	if($res){
	           		successly('添加成功');
	        	}else{
	            	fail("添加失败");
	        	}
		}else{
			$info =$this->getCateInfo();			
			$this->assign('info',$info);
			return view();
		}
		
	}
	

	/** 分类列表展示页面 */
	public function cateList(){		
		$arr = $this->getCateInfo();
		$this->assign('arr',$arr);
		return view();
	}

	/**验证唯一性 */
	public function checkName(){
	  	$cate_name = input('post.cate_name');
		$cate_model = model('category');
		$type = input('post.type');
		if($type == 1){
			$where = [
				'cate_name'=>$cate_name
			];
		}else{
			$cate_id = input('post.cate_id');
			$where = [
				'cate_id'=>['neq',$cate_id],
				'cate_name'=>$cate_name
			];
		}
		
		$arr = $cate_model->where($where)->find();
		if($arr){
			echo 'no';
		}else{
			echo 'ok';
		}
    }
	
	/** 分类删除 */
	public function cateDel(){
		$cate_id = input('get.cate_id');
		// dump($cate_id);die;
		// 当前分类下是否有分类
		$cate_model = model('category');
		$cateWhere = [
			'pid'=>$cate_id
		];
		$count1 = $cate_model->where($cateWhere)->count();
		if($count1>0){
			$this->error('此分类下有分类或者商品');
		}
		
		$where = [
			'cate_id'=>$cate_id
		];
		$res = $cate_model->where($where)->delete();
		if($res){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}

	/** 分类修改页面 */
	public function cateEdit(){
		$cate_id = input('get.cate_id','','intval');

		if(empty($cate_id)){
			$this->error('请正确操作');exit;
		}
		$where = [
			'cate_id'=>$cate_id
		];
		$model = model('Category');
		$data = $model->where($where)->find();
		if(empty($data)){
			$this->error('请正确操作');exit;
		}
		$cateInfo = $this->getCateInfo();

		$this->assign('cateInfo',$cateInfo);
		$this->assign('data',$data);
		return view();
	}


	/** 分类修改执行 */
	public function cateEditDo(){
		$data = input('post.');
		$validate = validate('Category');
		$result = $validate->scene('edit')->check($data);
		if(!$result){
			fail($validate->getError());
		}
		$where = [
			'cate_id'=>$data['cate_id']
		];
		$model = model('Category');
		$arr = $model->allowField(true)->save($data,$where);
		if($arr === false){
			fail('修改失败');
		}else{
			successly('修改成功');
		}
	}
	/** 即点即改 */
	public function cateChange(){
		$cate_id = input('post.cate_id');//id
		$value = input('post.value');//值
		$column = input('post.column');//字段
		$model = model('Category');
		$where = [
			'cate_id'=>$cate_id
		];
		$info=[
			$column=>$value
		];
		$res = $model->save($info,$where);
		if($res){
			successly('操作成功');
		}else{
			fail('操作失败');
		}
	}

	

	
}