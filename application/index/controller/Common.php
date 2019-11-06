<?php
namespace app\index\controller;
use think\Controller;

class Common extends Controller{
	public function _initialize(){
		$controllerName = request()->controller();
		$this->assign('controllerName',$controllerName);
	}

	/**获取左侧分类 */
	public function getLeftCateInfo(){
		$cate_model = model('Category');
		//获取左侧分类导航
		$cateWhere = [
			'cate_show'=>1
		];
		$cateInfo=$cate_model->where($cateWhere)->select();
		$cateInfo = getLeftCateInfo($cateInfo);
		// var_dump($cateInfo);die;
		$this->assign('cateInfo',$cateInfo);
		//查询头部导航
		$cateNavInfo = $cate_model->where(['cate_navshow'=>1])->select();
		$this->assign('cateNavInfo',$cateNavInfo);
	}

	/**检测是否登录 */
	public function checkLogin(){
		return session('?userInfo');
	}

	/**获取库存
	 * $goods_id 商品id
	 * $num 以存入库的购买数量
	 * $buy_number 将要加入购物车的数量
	 */
	public function checkGoodsNum($goods_id,$num,$buy_number,$type=1){
		$goods_model=model('Goods');
		//根据id写条件
		$where=[
			'goods_id'=>$goods_id
		];
		//查询
		$goods_num=$goods_model->where($where)->value('goods_num');
		// dump($goods_num);exit;
		//判断库存量是否在加入购物车的范围内
		if(($num+$buy_number)>$goods_num){
			$n=$goods_num-$num;
			if($type==1){
				fail('您购买数量已超过库存，您还可以购买'.$n.'件');
			}else{
				return false;
			}
			
		}else{
			return true;
		}
	}

	/**获取登录用户的id */
	public function getUserId(){
		return session('userInfo.user_id');
	}

	/**同步浏览记录 */
	public function syncHistory(){
		//同步浏览数据
		$arr=cookie('arr');//将cookie值取出
		//判断$str是否为空
		if(!empty($arr)){
			$str=unserialize(base64_decode($arr));//将$str反序列化出来
			//取到登录用户的id
			$user_id=$this->getUserId();
			foreach($str as $k=>$v){
				$str[$k]['user_id']=$user_id;//将用户id存到数组cookie中
			}
			//实力化model
			$his_model=model('History');
			//将数组存入到数据库中（存多条数据）
			$res=$his_model->saveAll($str);
			if($str){
				//判断是否成功添加成功将cookie中的清除
				cookie('arr',null);
			}
		}
	}

	/**同步购物车 */
	public function syncCart(){
		$cart_model=model('Cart');
		//获取到cookie值
		$cart_str=cookie('cartInfo');
		//判断cookie值是否为空
		// $user_id=$this->getUserId();
		if(!empty($cart_str)){
			//将cookie的值反序列化
			$cartInfo=unserialize(base64_decode($cart_str));
			// dump($cartInfo);exit;
			foreach($cartInfo as $k=>$v){
				$cartWhere=[
					'user_id'=>session('userInfo.user_id'),
					'goods_id'=>$v['goods_id']
				];
				$cartArr=$cart_model->where($cartWhere)->find();
				// dump($cartArr);die;
				if(!empty($cartArr)){
					//检测库存
					$this->checkGoodsNum($v['goods_id'],$cartArr['buy_number'],$v['buy_number']);
					//改
					$cartUpdate=[
						'buy_number'=>$cartArr['buy_number']+$v['buy_number'],
						'update_time'=>time(),
					];
					$result=$cart_model->save($cartUpdate,$cartWhere);
				}else{
					//检测库存
					$this->checkGoodsNum($v['goods_id'],0,$v['buy_number']);
					$cartAdd=[
						'goods_id'=>$v['goods_id'],
						'user_id'=>session('userInfo.user_id'),
						'buy_number'=>$v['buy_number'],
						'create_time'=>$v['create_time']
					];
					$result=$cart_model->insert($cartAdd);
				}
			}
			if($result){
				cookie('cartInfo',null);
			}
		}
	}

	/**获取收货人信息 */
	public function getAddressInfo(){
		$where=[
			'user_id'=>session('userInfo.user_id'),
		];
		$address_model=model('Address');
		$area_model=model('Area');
		//根据用户id查询
		$addressInfo=$address_model->where($where)->select();
		if(!empty($addressInfo)){
			//处理收货地址的省市区
			foreach($addressInfo as $k=>$v){
				$addressInfo[$k]['province']=$area_model->where(['id'=>$v['province']])->value('name');
				$addressInfo[$k]['city']=$area_model->where(['id'=>$v['city']])->value('name');
				$addressInfo[$k]['area']=$area_model->where(['id'=>$v['area']])->value('name');
			}
			return $addressInfo;
		}else{
			return false;
		}
	}

	/**获取商品信息
     * 接goods_id的值
     * $type 判断在哪里(cookie或数据库)取值用的条件
     */
    public function getGoodsInfos($g_id,$type=1){
		//dump($g_id);die;
        //实例化goods表
        $goods_model=model('Goods');
        //写where条件
        $goodsWhere=[
            'shop_goods.goods_id'=>['in',$g_id],//goods表中的id等于上面取到对的id
            'is_up'=>1,
        ];
        $where=[
            'user_id'=>session('userInfo.user_id'),
            'cart_status'=>1
		];
		// $goods_id=implode(',',$g_id);
        //写SQL语句  判断是取cookie中的值还是数据库中的值
        if($type==1){
            //写SQL语句 从数据库中取值
            $goodsInfo=$goods_model
                ->field('g.goods_id,goods_name,self_price,goods_num,goods_img,buy_number,market_price')
                ->alias('g')
                ->join('shop_cart c','g.goods_id=c.goods_id')
                ->where($goodsWhere)
                ->where($where)
                ->order("field(c.goods_id,".$g_id.")")
                ->select();
        }else{
            //从cookie中取值
            $goodsInfo=$goods_model
                ->field('goods_id,goods_name,self_price,goods_num,goods_img,market_price')
                ->where($goodsWhere)
                ->order("field(goods_id,".$g_id.")")
                ->select();
        }
		 //判断是否取值成功 是：返回数据  否：返回false
		 
		if(!empty($goodsInfo)){
            return $goodsInfo;
        }else{
            return false;
        }
        // exit;
    }




}