<?php
namespace app\index\controller;
class Order extends Common{
    /**订单详情页 */
    public function confirmCount(){
        if(!$this->checkLogin()){
            $this->error('请先登录',url('Login/login'));exit;
        }else{
            //获取左侧分类信息
            $this->getLeftCateInfo();
            //接受id值
            $goods_id=input('get.goods_id');
            // dump($goods_id);exit;

            //检测是否选择商品
            if(empty($goods_id)){
                $this->error('请至少选择一个商品');exit;
            }

            //获取商品总价格
            $countPrice=0;
            $goodsInfo=$this->getGoodsInfo($goods_id);
            //dump($goodsInfo);exit;

            //获取当前用户的所有的收货地址
            $addressInfo=$this->getAddressInfo();
            
            foreach($goodsInfo as $k=>$v){
                $countPrice+=$v['self_price']*$v['buy_number'];
            }
            //dump($countPrice);exit;

            $this->assign('goodsInfo',$goodsInfo);
            $this->assign('countPrice',$countPrice);
            $this->assign('addressInfo',$addressInfo);
            return view();
        }
    }

    /**判断是否登录 */
    public function isLogin(){
        $res=$this->checkLogin();
        echo json_encode(['login_status'=>$res]);exit;
    }


    /**获取商品信息
     * 接goods_id的值
     * $type 判断在哪里(cookie或数据库)取值用的条件
     */
    public function getGoodsInfo($g_id,$type=1){
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
        //写SQL语句  判断是取cookie中的值还是数据库中的值
        if($type==1){
            //写SQL语句 从数据库中取值
            $goodsInfo=$goods_model
                ->field('g.goods_id,goods_name,self_price,goods_num,goods_img,buy_number,market_price')
                ->alias('g')
                ->join('shop_cart c','g.goods_id=c.goods_id')
                ->where($goodsWhere)
                ->where($where)
                ->order("field(c.goods_id,$g_id)",'asc')
                ->select();
        }else{
            //从cookie中取值
            $goodsInfo=$goods_model
                ->field('goods_id,goods_name,self_price,goods_num,goods_img,market_price')
                ->where($goodsWhere)
                ->order("field(goods_id,$g_id)",'asc')
                ->select();
        }
        //判断是否取值成功 是：返回数据  否：返回false
        if(!empty($goodsInfo)){
            return $goodsInfo;
        }else{
            return false;
        }
    }

    /**确认订单 */
    public function confirmOrder(){
        //先检测是否登录
        if(!$this->checkLogin()){
            fail('请先登录');
        }
        //检测是否有商品
        $goods_id=input('post.goods_id');
        // $goods_id=explode(",",$goods_id);
        // dump($goods_id);
        if(empty($goods_id)){
            fail('请先选择一个要购买的商品');
        }

        //检测收货地址
        $address_id=input('post.address_id',0,'intval');
        // dump($address_id);
        if(empty($address_id)){
            fail('请选择一个收货地址');
        }

        //检测支付方式
        $pay=[1,2,3];
        $pay_type=input('post.pay_type',0,'intval');
        // dump($pay_type);
        if(!in_array($pay_type,$pay)){
            fail('请选择一个正确的支付方式');
        }

        //开启事务
        $order_model=model('Order');
        $order_model->startTrans();
        $order_text=input('post.order_text');
        //dump($order_text);exit;

        try{
            //先取到用户id
            $user_id=session('userInfo.user_id');
            // dump($user_id);exit;
            //把订单信息存入订单表
            //获取订单号
            $orderInfo['order_no']=$this->getOrderNo();
            //dump($orderInfo['order_no']);exit;
            $goodsInfo=$this->getGoodsInfos($goods_id);
            // dump($goodsInfo);exit;
            $order_amount=0;//商品总价
            foreach($goodsInfo as $k=>$v){
                $order_amount+=$v['self_price']*$v['buy_number'];
            }
            // dump($order_amount);exit;
            $orderInfo['order_amount']=$order_amount;
            $orderInfo['order_text']=$order_text;
            $orderInfo['pay_type']=$pay_type;
            $orderInfo['user_id']=$user_id;
            //  dump($orderInfo);die;
            $res=$order_model->save($orderInfo);
            // dump($res);exit;
            if(!$res){
                throw new Exception('订单信息写入错误');
            }

            $order_id=$order_model->getLastInsId();
            // dump($order_id);exit;

            //订单商品信息 存入订单详情表
            foreach($goodsInfo as $k=>$v){
                $res1=$this->checkGoodsNum($goods_id,0,$v['buy_number'],2);
                //dump($res1);exit;
                if(!$res1){
                    throw new Exception($v['goods_name'].'超过库存');
                }
                $goodsInfo[$k]['user_id']=$user_id;
                $goodsInfo[$k]['order_id']=$order_id;
                // dump($goodsInfo);exit;
            }
            // dump($goodsInfo);die;
            $goodsInfo=collection($goodsInfo)->toArray();
            // dump($goodsInfo);die;
            $orderdetail_model=model('OrderDetail');
            $res2=$orderdetail_model->allowField(true)->saveAll($goodsInfo);
            // dump($res2);exit;
            if(!$res2){
                throw new Exception('订单信息写入失败');
            }

            //订单收货地址 存入订单收货地址表
            $address_model=model('Address');
            $addressWhere=[
                'address_id'=>$address_id
            ];
            $addressInfo=$address_model->where($addressWhere)->find();
            if(empty($addressInfo)){
                throw new Exception("收货地址不存在");
            }
            $addressInfo=$addressInfo->toArray();
            $addressInfo['order_id']=$order_id;
            $addressInfo['user_id']=$user_id;
            $addressInfo['create_time']=time();
            $addressInfo['update_time']=time();
            // dump($addressInfo);exit;
            $orderaddress_model=model('OrderAddress');
            $res3=$orderaddress_model->allowField(true)->save($addressInfo);
            // dump($res3);die;
            // dump($res3);exit;
            if(empty($res3)){
                throw new Exception("订单收货地址写入信息失败");
            }

            //购物车数据状态发生改变
            $cart_model=model('Cart');
            $cartWhere=[
                'user_id'=>$user_id,
                'goods_id'=>['in',$goods_id]
            ];

            $res4=$cart_model->save(['cart_status'=>2],$cartWhere);
            
            if(empty($res4)){
                throw new Exception("购物车清空失败");                
            }

            //更改商品表库存
            $goods_model=model('Goods');
            foreach($goodsInfo as $k=>$v){
                $goodsWhere=[
                    'goods_id'=>$v['goods_id']
                ];
              
                $updateInfo=[
                    'goods_num'=>$v['goods_num']-$v['buy_number']
                ];//库存
                //dump($updateInfo);exit;
                $res5=$goods_model->save($updateInfo,$goodsWhere);
               // dump($res5);exit;
                if(empty($res5)){
                    throw new Exception("商品库存存入失败");
                }
            }
            $order_model->commit();
            successly('下单成功');

        }catch(\Exception $e){
            $order_model->rollback();
            fail($e->getMessage());
        }


    }

    /**订单号 */
    public function getOrderNo(){
        return time().rand('1111','9999');
    }



}

?>