<?php
namespace app\index\controller;
use think\Controller;
class Cart extends Common{

    /**加入购物车 */
    public function cartAdd(){
        $goods_id=input('post.goods_id',0,'intval');
        $buy_number=input('post.buy_number',0,'intval');

        //验证商品状态是否为上架
        $goods_model=model('Goods');
        $goodsWhere=[
            'goods_id'=>$goods_id,
            'is_up'=>1
        ];
        $goodsInfo=$goods_model->where($goodsWhere)->find();
        if(empty($goods_id)){
            fail('请选择一个商品');
        }else if(empty($goodsInfo)){
            fail('商品已下架');
        }

        if(empty($buy_number)){
            fail('请选择你要购买的商品数量');
        }

        //判断是否登录，选择商品数据加入到cookie中或数据库中
        if($this->checkLogin()){
            //加入数据库
            $this->addCartDb($goods_id,$buy_number);
        }else{
            //添加到cookie中
            $this->addCartCookie($goods_id,$buy_number);
        }

    }

    /**把购物车数据加入到数据库中 */
    public function addCartDb($goods_id,$buy_number){
        $cart_model=model('Cart');
        //将登录的id取到
        $user_id=session('userInfo.user_id');
        //查询购物车中是否有当前用户的商品数据
        $cartWhere=[
            'user_id'=>$user_id,
            'goods_id'=>$goods_id
        ];
        $cartInfo=$cart_model->where($cartWhere)->find();
        //判断数据库中是否有
        if(!empty($cartInfo)){
            //有数据  判断是否超过库存量 修改库存
            $this->checkGoodsNum($goods_id,$cartInfo['buy_number'],$buy_number);
            //修改
            $updateInfo=[
                'buy_number'=>$cartInfo['buy_number']+$buy_number
            ];
            $res=$cart_model->save($updateInfo,$cartWhere);
            if($res){
                successly('加入购物车成功');
            }else{
                fail('加入购物车失败');
            }
        }else{
            //没有数据 添加数据
            $info=[
                'goods_id'=>$goods_id,
                'buy_number'=>$buy_number,
                'user_id'=>$user_id
            ];
            $res=$cart_model->save($info);
            if($res){
                successly('加入数据库成功');
            }else{
                fail('加入数据库失败');
            }
        }
    }

    /**把购物车数据添加到cookie中 */
    public function addCartCookie($goods_id,$buy_number){
        $cart_str=cookie('cartInfo');
       if(!empty($cart_str)){
            //反序列化cookie
            $cartInfo=unserialize(base64_decode($cart_str));
            $flag=0;//表示是否加入过购物车
            //第n次添加   累加
            foreach($cartInfo as $k=>$v){
                if($v['goods_id']==$goods_id){
                    //检测是否超过库存
                    $this->checkGoodsNum($goods_id,$v['buy_number'],$buy_number);
                    //将已存入的购买数量加上将要加入购物车的数量
                    $cartInfo[$k]['buy_number']=$v['buy_number']+$buy_number;//将cookie中的值改
                    $cartInfo[$k]['create_time']=time();
                    $str=base64_encode(serialize($cartInfo));
                    cookie('cartInfo',$str);
                    $flag=1;//已经加入过这个商品
                }
            }

            //在cookie中做追加数据
            if($flag==0){
                $this->checkGoodsNum($goods_id,0,$buy_number);
                $cartInfo[]=[
                    'goods_id'=>$goods_id,
                    'buy_number'=>$buy_number,
                    'create_time'=>time()
                ];
                $str=base64_encode(serialize($cartInfo));
                cookie('cartInfo',$str);
            }
       }else{
            //第一次添加
            $this->checkGoodsNum($goods_id,0,$buy_number);
            $cartInfo[]=[
                'goods_id'=>$goods_id,
                'buy_number'=>$buy_number,
                'create_time'=>time(),
            ];
            // dump($cartInfo);exit;
            $str=base64_encode(serialize($cartInfo));
            // dump($str);exit;
            cookie('cartInfo',$str);

       }
       successly('添加成功');
    }

    /**购物车列表  */
    public function cartList(){
        //获取左侧分类信息
        $this->getLeftCateInfo();

        //判断是否登录然后取个购物车中的数据
        if($this->checkLogin()){
            //从数据库中取值
            $cartInfo=$this->getCartDb();
            
        }else{
            //从cookie中取值
            $cartInfo=$this->getCartCookie();
        }

        $this->assign('cartInfo',$cartInfo);
        return view();
    }

    /**从数据库中取购物车列表的值 */
    public function getCartDb(){
        //取到session值
        // $user_id=session('userInfo.user_id');
        $user_id=$this->getUserId();
        //实例化model
        $cart_model=model('Cart');
        $where=[
            'user_id'=>$user_id,
            // 'cart_status'=>1
        ];
        //按照where条件查找cart表中的goods_id并按时间排序（大->小）
        $goods_id=$cart_model->where($where)->order('create_time','desc')->column('goods_id');
        //把取到的id转化成字符串
        $goods_id=implode(',',$goods_id);

        //获取商品信息
        $goodsInfo=$this->getGoodsInfo($goods_id,1);
        return $goodsInfo;
        
    }

    /**从cookie中取购物车列表的值 */
    public function getCartCookie(){
        //取到cookie中的值
        $cookie_str=cookie('cartInfo');
        //判断cookie值是否为空
        if(!empty($cookie_str)){
            //将cookie值方式反序列化
            $cartInfo=unserialize(base64_decode($cookie_str));
            // dump($cartInfo);
            //获取商品id
            $goods_id=[];
            for($i=count($cartInfo)-1;$i>=0;$i--){
                $goods_id[]=$cartInfo[$i]['goods_id'];
            }
            $g_id=implode(',',$goods_id);
            // dump($g_id);exit;
            //根据商品的id查询对应的商品信息
            $goodsInfo=$this->getGoodsInfo($g_id,$type=2);
            foreach($goodsInfo as $k=>$v){
                if(in_array($v['goods_id'],$goods_id)){
                   foreach($cartInfo as $key=>$val){
                       if($v['goods_id']==$val['goods_id']){
                           $goodsInfo[$k]['buy_number']=$val['buy_number'];
                       }
                   }
                }
            }
            return $goodsInfo;
        }
       
    }

    /**获取商品信息
     * 接goods_id的值
     * $type 判断在哪里(cookie或数据库)取值用的条件  type=1从数据库取值
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
        // exit;
    }

    /**改变购买数量 */
    public function changeNum(){
        $goods_id=input('post.goods_id');
        $buy_number=input('post.buy_number');
        // dump($goods_id);
        // dump($buy_number);exit;
        if($this->checkLogin()){
            //修改数据库中的值
            $this->checkGoodsNum($goods_id,0,$buy_number);//检测库存
            $cart_model=model('Cart');
            $cartWhere=[
                'user_id'=>session('userInfo.user_id'),
                'goods_id'=>$goods_id
            ];
            $cartDate=[
                'buy_number'=>$buy_number
            ];
            $res=$cart_model->save($cartDate,$cartWhere);
            if($res){
                successly('修改成功');
            }else{
                fail('修改失败');
            }
        }else{
            //修改cookie中的值
            //修获取cookie的值
            $cart_str=cookie('cartInfo');
            if(!empty($cart_str)){
                $cartInfo=unserialize(base64_decode($cart_str));
                // dump($cartInfo);
                foreach($cartInfo as $k=>$v){
                    if($v['goods_id']==$goods_id){
                        //检测库存
                        $this->checkGoodsNum($goods_id,0,$buy_number);
                        //改变cookie中购买的数量
                        $cartInfo[$k]['buy_number']=$buy_number;
                    }
                }
                // dump($cartInfo);exit;
                $str=base64_encode(serialize($cartInfo));
                cookie('cartInfo',$str);
                // dump($str);
            }
        }
    }

    /**获取总价 */
    public function getCountPrice(){
        $goods_id=input('post.goods_id');
        // dump($goods_id);
        if($this->checkLogin()){
            //从数据库获取
            $where=[
                'user_id'=>session('userInfo.user_id'),
                'shop_cart.goods_id'=>['in',$goods_id],
                'is_up'=>1
            ];
            $cart_model=model('Cart');
            $info=$cart_model
                ->field('buy_number,self_price')
                ->alias('c')
                ->join('shop_goods g','c.goods_id=g.goods_id')
                ->where($where)
                ->select();
            $countPrice=0;
            foreach($info as $k=>$v){
                $countPrice+=$v['self_price']*$v['buy_number'];
            }
            echo $countPrice;
        }else{
            //从cookie中获取购物车商品的总价
            $goods_id=explode(',',$goods_id);
            // dump($goods_id);die;

            $cart_str=cookie('cartInfo');
            // dump($cart_str);die;
            $info=[];
            if(!empty($cart_str)){
                //反序列化cookie
                $cartInfo=unserialize(base64_decode($cart_str));
                //dump($cartInfo);exit;
                foreach($cartInfo as $k=>$v){
                    if(in_array($v['goods_id'],$goods_id)){
                        $info[]=$v;
                    }
                }
                // dump($info);exit;
                $goods_model=model('Goods');
                $countPrice=0;
                foreach($info as $key=>$val){
                    $where=[
                        'goods_id'=>$val['goods_id'],
                        'is_up'=>1
                    ];
                    $self_price=$goods_model->where($where)->value('self_price');
                    $countPrice+=$val['buy_number']*$self_price;
                    
                }
                echo $countPrice;
            }
        }
    }

    /**单个删除 */
    public function cartDel(){
        $goods_id=input('post.goods_id',0,'intval');
        if(empty($goods_id)){
            fail("请选择商品");
        }

        if($this->checkLogin()){
            //删除数据库中的数据（改变状态）
            $where=[
                'goods_id'=>$goods_id,
                'user_id'=>session('userInfo.user_id')
            ];
            $cart_model=model('Cart');
            $res=$cart_model->where($where)->update(['cart_status'=>2]);
            if($res){
                successly('删除成功');
            }else{
                fail('删除失败');
            }
        }else{
            //删除cookie中的值
            $cart_str=cookie('cartInfo');//获取cookie
            if(!empty($cart_str)){
                $cartInfo=unserialize(base64_decode($cart_str));//反序列化cookie
                foreach($cartInfo as $k=>$v){
                    if($v['goods_id']==$goods_id){
                        unset($cartInfo[$k]);
                    }
                }
                if(empty($cartInfo)){//判断是否为空
                    $str=null;
                }else{
                    $cartInfo=array_values($cartInfo);
                    $str=base64_encode(serialize($cartInfo));
                }
                cookie('cartInfo',$str);
                successly('删除成功');
            }
        }
    }

    /**清空购物车 */
    public function cartDelAll(){
        if($this->checkLogin()){
            $where=[
                'user_id'=>session('userInfo.user_id'),
            ];
            $cartDate=[
                'cart_status'=>2
            ];
            $cart_model=model('Cart');
            $res=$cart_model->save($cartDate,$where);
            if($res){
                successly('删除成功');
            }else{
                fail('删除失败');
            }
        }else{
            cookie('cartInfo',null);
            successly('清除成功');
        }
    }

    /**测试 */
    /*
    public function test(){
        $cart_str=cookie('cartInfo');
        $cartInfo=unserialize(base64_decode($cart_str));
        dump($cartInfo);
    }
    */
    

}

?>