<?php
namespace app\admin\controller;

class Brand extends Common 
{
	/** 品牌添加展示页面 */
	public function brandAdd()
	{
		if(checkRequest()){
			$data = input('post.');
			$validate = validate('Brand');
			$result = $validate->scene('add')->check($data);
			if(!$result){
				fail($validata->getError());
			}

			$model = model('Brand');
			$res = $model->allowField(true)->save($data);
			if($res){
				successly('添加成功');
			}else{
				fail('添加失败');
			}
		}else{
			return view();
		}
	}	
	/** 品牌列表展示页面 */
	public function brandlist()
	{
		return view();
	}

	/** 展示页面数据接收 */
	public function brandInfo(){
		$page = input('get.page');
		$limit = input('get.limit');

		$brand_name = input('get.brand_name');
		//dump($brand_name);
		$where = [];
		if(!empty($brand_name)){
			$where['brand_name'] = ['like',"%$brand_name%"];
		}
		$model = model('Brand');
		$data = $model->where($where)->page($page,$limit)->select();
		
		$count = $model->where($where)->count();
		$info = [
			'code'=>0,
			'msg'=>'',
			'count'=>$count,
			'data'=>$data
		];	
		echo json_encode($info);
	}

	/** 唯一性验证 */
	public function checkName(){
		$brand_name = input('post.brand_name');
		$brand_model = model('Brand');
		$type = input('post.type');
		if($type == 1){
			$where = [
				'brand_name'=>$brand_name
			];
		}else{
			$brand_id = input('post.brand_id');
			$where = [
				'brand_id'=>['neq',$brand_id],
				'brand_name'=>$brand_name
			];
		}
		
		$arr = $brand_model->where($where)->find();
		if($arr){
			echo 'no';
		}else{
			echo 'ok';
		}
	}

	/** 品牌logo上传 */
	public function brandLogo(){
		// 获取表单上传文件 例如上传了001.jpg
			
		$file = request()->file('file');
		// 移动到框架应用根目录/public/brandLogo/ 目录下
		$info = $file->move(ROOT_PATH . 'public' .  DS . 'brandLogo');
		if($info){
			//根据品牌id 查询品牌logo 删除
			
			$arr = [
				'code'=>1,
				'font'=>'上传成功',
				'src'=>$info->getSaveName()
			];
			echo json_encode($arr);			
		}else{
			// 上传失败获取错误信息
			echo $file->getError();
		}

	}

	/** 即点即改 */
	public function brandEditField(){
		//分别接受新值，id,修改的当前字段名
		$value = input('post.value');
		$brand_id = input('post.brand_id');
		$field = input('post.field');
		//条件
		$where = [
			'brand_id'=>$brand_id
		];
		//字段=新值
		$data = [
			$field => $value
		];
		//验证场景
		$scene = 'edit'.$field;
		$validate = validate('brand');
		$result = $validate->scene($scene)->check($data);
		if(!$result){
			fail($validate->getError());
		}
		$model = model('Brand');
		$arr = $model->where($where)->update($data);
		if($arr){
			successly('操作成功');
		}else{
			fail('操作失败');
		}
	}

	/** 品牌删除 */
	public function brandDel(){
		$brand_id = input('post.brand_id');
		$where = [
			'brand_id'=>$brand_id
		];
		$model = model('Brand');
		$arra = $model->where($where)->value('brand_logo');
		$arr = $model->where($where)->delete();
		if($arr){
			unlink('brandLogo/'.$arra);
			successly('删除成功');
		}else{
			fail('删除失败');
		}
	}
	/** 品牌修改页面 */
	public function brandEdit(){
		$brand_id = input('get.brand_id','','intval');
		if(empty($brand_id)){
			$this->error('请正确操作');exit;
		}
		$where = [
			'brand_id'=>$brand_id
		];
		$model = model('Brand');
		$brandInfo = $model->where($where)->find();
		if(empty($brandInfo)){
			$this->error('请正确操作');exit;
		}
		$this->assign('brandInfo',$brandInfo);
		return view();
	}

	/** 品牌修改执行 */
	public function brandEditDo(){
		$data = input('post.');
		$validate = validate('Brand');
		$result = $validate->scene('edit')->check($data);
		if(!$result){
			fail($validate->getError());
		}
		$where = [
			'brand_id'=>$data['brand_id']
		];
		$model = model('Brand');
		$arr = $model->allowField(true)->save($data,$where);
		if($arr === false){
			fail('修改失败');
		}else{
			$brand_id = $data['brand_id'];
			if(!empty($brand_id)){
				$model = model('Brand');
				$where = [
					'brand_id'=>$brand_id
				];
				$brand_logo = $model->where($where)->value('brand_logo');
				unlink("brandLogo/".$brand_logo);
			}
			successly('修改成功');
		}
	}

	

	
}