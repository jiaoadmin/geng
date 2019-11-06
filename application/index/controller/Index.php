<?php
namespace app\index\controller;
use think\Controller;
	class Index extends Common{

		public function index(){
			//获取左侧分类信息
			$this->getLeftCateInfo();
			
			//处理楼层
			$cate_id=1;
			$floorInfo = $this->getFloorInfo($cate_id);
			$this->assign('floorInfo',$floorInfo);
			return view();
		}

		public function getFloorInfo($cate_id){
			$cate_model = model('Category');
			//获取顶级分类信息
			
			$where=[
				'cate_id'=>$cate_id
			];
			$data['topCate'] = $cate_model->field('cate_id,cate_name')->where($where)->find();
			//获取二级分类数据
			$cateWhere=[
				'pid'=>$cate_id
			];
			$data['sonCate']=$cate_model->where($cateWhere)->select();
			//获取商品数据
			$cateInfo = $cate_model->select();
			$c_id = getSonCateId($cateInfo,$cate_id);
			$goodsWhere = [
				'cate_id'=>['in',$c_id]
			];
			$goods_model = model('Goods');

			$data['goodsInfo']=collection($goods_model->where($goodsWhere)->select())->toArray();
			// dump($data['goodsInfo']);die;
			return $data;
		}

		public function getMoreFloorInfo(){
			$cate_id = input('post.cate_id');
			$floor_num = input('post.floor_num');
			// echo $cate_id;
			// echo $floor_num;exit;
			//得到下一楼层数据
			$cate_model=model('Category');
			$where=[
				'pid'=>0,
				'cate_id'=>['>',$cate_id]
			];
			$c_id = $cate_model->where($where)->order('cate_id','asc')->limit(1)->value('cate_id');
			if(empty($c_id)){
				echo "no";exit;
			}
			$info = $this->getFloorInfo($c_id);
			$floor_num = $floor_num+1;
			$this->assign('floorInfo',$info);
			$this->assign('floorNum',$floor_num);
			$this->view->engine->layout(false);
			echo $this->fetch('div');
		}	

		

	}
	