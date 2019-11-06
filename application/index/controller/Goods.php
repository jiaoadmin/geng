<?php
namespace app\index\controller;
use think\Controller;
	class Goods extends Common{
		/**商品列表 */
		public function goodsList(){
			//左侧导航
			$this->getLeftCateInfo();
			$brand_model=model('Brand');
			$cate_model=model('Category');
			$goods_model=model('Goods');

			//获取分类id
			$cate_id=input('get.cate_id');
			if(empty($cate_id)){
				session('cate_id',null);
				$brandWhere=1;
				$priceWhere=1;
			}else{
				session('cate_id',$cate_id);

				//在分类表中获取当前分类下的所有子类
				$cateInfo=$cate_model->select();
				$c_id=getSonCateId($cateInfo,$cate_id);//所有子类
				$priceWhere=[
					'cate_id'=>['in',$c_id],//
				];
				// dump($c_id);exit;

				//在商品表中根据查询到的子类id 获取品牌id
				$goodsWhere=[
					'cate_id'=>['in',$c_id]
				];
				$brand_id=$goods_model->where($goodsWhere)->column('brand_id');
				$brand_id=array_unique($brand_id);
				// dump($brand_id);
				$brandWhere=[
					'brand_id'=>['in',$brand_id]
				];				
			}
			// dump($where);exit;

			//在品牌表中根据品牌id获取品牌信息
			$brandInfo=$brand_model->where($brandWhere)->select();
			// $brandInfo=collection($brandInfo)->toArray();
			// dump($brandInfo);exit;

			//获取商品的最高价格
			$max_price=$goods_model->where($priceWhere)->value('max(self_price)');//所有价格里的最高价格一个
			// dump($max_price); exit;
			$priceInfo=$this->priceCut($max_price);//价格区间

			//获取第一页商品数据（获取库存量高的4个商品信息）
			$p=1;
			$page_num=4;
			$goodsInfo=$goods_model
				->where($priceWhere)
				->order("goods_num",'desc')
				->page($p,$page_num)
				->select();
			//总条数
			$count=$goods_model->where($priceWhere)->count();
			// dump($count);exit;

			//获取页码
			$page_obj=new \page\AjaxPage();
			$str=$page_obj::ajaxpager($p,$count,$page_num,url('Goods/goodsPage'));

			//同步浏览数据
			if($this->checkLogin()){
				$historyInfo=$this->getHistoryDb();
			}else{
				$historyInfo=$this->getHistoryCookie();
			}

			$this->assign('brandInfo',$brandInfo);
			$this->assign('historyInfo',$historyInfo);
			$this->assign('priceInfo',$priceInfo);
			$this->assign('goodsInfo',$goodsInfo);
			$this->assign('str',$str);
			return view();
		}

		/**商品详情 */
		public function goodsDetail(){
			//接受id
			$goods_id=input('get.goods_id',0,'intval');
			// dump($goods_id);exit;
			if(empty($goods_id)){
				$this->error('请选择一件商品','Index/index');exit;
			}
			$where=[
				'goods_id'=>$goods_id
			];
			$goods_model=model('Goods');
			$goodsInfo=$goods_model->where($where)->find();
			if(empty($goodsInfo)){
				$this->error('没有商品数据');exit;
			}
			//将商品轮播图路径中的'|'去除
			$goodsInfo['goods_imgs']=explode('|',rtrim($goodsInfo['goods_imgs'],'|'));

			//浏览记录
			if($this->checkLogin()){
				//把浏览记录存入数据库
				$this->saveHistoryDb($goods_id);
			}else{
				//把浏览记录存入cookie中
				$this->saveHistoryCookie($goods_id);
			}
			
			
			$this->getLeftCateInfo();
			$this->assign('goodsInfo',$goodsInfo);
			return view();
	
		}	

		/**重新获取价格 */
		public function getPriceInfo(){
			//接受品牌id
			$brand_id=input('post.brand_id');
			$cate_id=session('cate_id');
			// dump(session('cate_id'));exit;
			// dump($brand_id);exit;

			$cate_model=model('Category');
			$goods_model=model('Goods');
			//判断分类id是否为空
			if(empty($cate_id)){
				$goodsWhere=[
					'brand_id'=>$brand_id,
				];
			}else{
				//在分类表中获取当前分类下的所有子类
				$cateInfo=$cate_model->select();
				$c_id=getSonCateId($cateInfo,$cate_id);//所有子类
				// dump($c_id);exit;
				$goodsWhere=[
					'brand_id'=>$brand_id,
					'cate_id'=>['in',$c_id]
				];
				// dump($goodsWhere);exit;
			}

			//获取商品的最高价格
			$max_price=$goods_model->where($goodsWhere)->value('max(self_price)');//所有价格里的最高价格一个
			// dump($max_price); exit;
			$priceInfo=$this->priceCut($max_price);
			// dump($priceInfo);exit;
			echo json_encode($priceInfo);


		}

		/**将价格分割成七份 */
		public function priceCut($max_price){
			$price=[];
			$avg_price=$max_price/7;
			for($i=0;$i<6;$i++){
				$start=$avg_price*$i;
				$end=$avg_price*($i+1)-0.01;
				$price[]=number_format($start,2,'.',',').'-'.number_format($end,2,'.',',');'<br>';
			}
			$price[]=number_format($start,2,'.',',').'以上';
			return $price;
			// dump($max_price/7);  exit;
			
		}
	
		/**重新获取商品+分页 */
		public function getGoodsInfo(){
			$p=input('post.p');
			$brand_id=input('post.brand_id');
			$price=input('post.price');
			$field=input('post.field');
			$order=input('post.order');
			$cate_id=session('cate_id');
			$cate_model=model('Category');
			// dump($p);
			// dump($brand_id);
			// dump($price);
			// dump($field);
			// dump($order);
			// exit;

			//处理条件
			$where=[];
			if(!empty($brand_id)){
				$where['brand_id']=$brand_id;
			}

			if(!empty($price)){
				$price=explode('-',$price);
				// dump($price);exit;
				$min=str_replace(',','',$price[0]);
				$max=str_replace(',','',$price[1]);
				// echo $min;	echo '<br>';	echo $max;
				$where['self_price']=['between',[$min,$max]];
				// dump($where);exit;
			}
			
			if(!empty($field)&&!empty($order)){
				$ord=1;
			}

			if(!empty($field)&&empty($order)){
				$where[$field]=1;
			}
			
			if(!empty($cate_id)){
				$cateInfo=$cate_model->select();
				$c_id=getSonCateId($cateInfo,$cate_id);//所有子类
				$where['cate_id']=['in',$c_id];
				// dump($where);exit;
			}

			$goods_model=model('Goods');
			$page_num=4;
			if(!empty($field)&&!empty($order)){
				$goodsInfo=$goods_model->where($where)->order($field,$order)->page($p,$page_num)->select();
			}else{
				$goodsInfo=$goods_model->where($where)->page($p,$page_num)->select();
			}
			// echo $goods_model->getLastSql();exit;
			//dump($goodsInfo);

			//获取页码
			$count=$goods_model->where($where)->count();
			$page_obj=new \page\AjaxPage();
			$str=$page_obj::ajaxpager($p,$count,$page_num,('Goods/goodsPage'));
			
			//同步浏览记录

			$this->view->engine->layout(false);
			$this->assign('goodsInfo',$goodsInfo);
			$this->assign('str',$str);
			echo $this->fetch('div');

		}

		/**把浏览记录存入cookie中 */
		public function saveHistoryCookie($goods_id){
			// dump($goods_id);
			$cookie_str=cookie('arr');
			$now=time();

			if(!empty($cookie_str)){
				//反序列化
				$arr = unserialize(base64_decode($cookie_str));
			}
			$arr[]=[
				'goods_id'=>$goods_id,
				'create_time'=>$now
			];
			$str=base64_encode(serialize($arr));//base64_encode序列化
			cookie('arr',$str);
			

		}

		/**将浏览记录存入库 */
		public function saveHistoryDb($goods_id){
			// dump($user_id);
			//将值存入数组中
			$arr=[
				'user_id'=>$this->getUserId(),
				'goods_id'=>$goods_id
			];
			// dump($arr);
			//实例化model
			$his_model=model('History');
			//入库
			$res=$his_model->save($arr);

		}

		/**从数据库中取到浏览记录 */
		public function getHistoryDb(){
			//实力化model
			$his_model=model('History');
			//写where条件
			$where=[
				'user_id'=>$this->getUserId()
			];
			//进行查询最新的4条记录,只查询商品id
			$goods_id=$his_model->where($where)->order('create_time','desc')->column('goods_id');
			// echo $his_model->getLastSql();exit;		
			//将查到的浏览数据去重 并取出前四条
			$goods_id=array_slice(array_unique($goods_id),0,4);
			// dump($goods_id);exit;
			//实例化goods表
			$goods_model=model('Goods');

			$whereId=implode(',',$goods_id);
			// dump($whereId);exit;
			$goodsWhere=[
				'goods_id'=>['in',$goods_id],
			];
			// dump($goodsWhere);exit;
			$goodsInfo=$goods_model->where($goodsWhere)->order("field(goods_id,".$whereId.")")->select();
			// echo $goods_model->getLastSql();exit;

			return $goodsInfo;
		}

		/**从cookie中将浏览记录取出 */
		public function getHistoryCookie(){
			//先取cookie值
			$cookie_str=cookie('arr');
			//反序列化
			$arr=unserialize(base64_decode($cookie_str));
			$goods_id=[];//给goods_id一个空数组
			//循环 倒叙取出数据
			for($i=count($arr)-1;$i>=0;$i--){
				$goods_id[]=$arr[$i]['goods_id'];
			}
			$goods_id=array_slice(array_unique($goods_id),0,4);
			//array_unique数组去重      array_alice
			$goods_model=model('Goods');
			$goodsInfo=[];
			for($i=0;$i<count($goods_id);$i++){
				$goodsWhere=[
					'goods_id'=>$goods_id[$i],
				];
				$goodsInfo[]=$goods_model->where($goodsWhere)->find();
			}
			return $goodsInfo;
		}	

		/**测试 */
		// public function test(){
		// 	$user_id=$this->getUserId();
		// 	dump($user_id);
		// }

		
	}