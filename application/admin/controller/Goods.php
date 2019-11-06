<?php
namespace app\admin\controller;

class Goods extends Common {
	/*商品添加*/
    public function goodsAdd(){
        if (checkRequest()){
            $data = input('post.');
            $model = model('Goods');
            //验证
            $validate = validate('Goods');
            if (!$validate->check($data)) {
                fail($validate->getError());
            }

            $res = $model->allowField(true)->save($data);
            if ($res){
                successly('添加成功');
            }else{
                fail('添加失败');
            }
        }else{
            //获取分类数据
            $info = $this->getCateInfo();
            $this->assign('info',$info);
            //获取品牌数据
            $model = model('Brand');
            $arr = $model->select();
            $this->assign('arr',$arr);
            return view();
        }
    }

    /** 富文本编辑器上传 */
    public function goodsEditImgs(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'goodseditimgs');
        if($info){
            // 成功上传后 获取上传信息
            $arr = [
                'code'=>0,
                'font'=>'上传成功',
                'data'=>[
                    'src'=>"/goodseditimgs/".$info->getSaveName(),
                    'title'=>'a'
                ]
            ];
            echo json_encode($arr);exit;
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }

    /** 获取分类数据 */
    public function getCateInfo(){
        $model = model('Category');
        $data = $model->select();
        //print_r($data);exit;
        $cateInfo = getCateInfo($data);
        return $cateInfo;
    }

    /**商品图片上传*/
    public function goodsUpload(){
        $type = input('get.type');
        $dir = $type==1?'goodsimg':'goodsimgs';
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . $dir);
        if($info){
            // 成功上传后 获取上传信息
            $arr = [
                'code'=>1,
                'font'=>'上传成功',
                'src'=>$info->getSaveName()
            ];
            echo json_encode($arr);exit;
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }

    /**商品展示 */
    public function goodsList(){
        //获取分类数据
        $info = $this->getCateInfo();
        $this->assign('info',$info);
        //获取品牌数据
        $model = model('Brand');
        $arr = $model->select();
        $this->assign('arr',$arr);
        return view();
    }

    /** 获取数据 */
    public function getGoodsInfo(){
    	$page = input('get.page');
		$limit = input('get.limit');

		$goods_name = input('get.goods_name');
		$cate_id = input('get.cate_id');
		$brand_name = input('get.brand_name');
		$is_up = input('get.is_up');
		
		$where = [];
		if(!empty($goods_name)){
			$where['goods_name'] = ['like',"%$goods_name%"];
		}
		if(!empty($cate_id)){
			$cate_model = model('category');
			$cateWhere = [
				'pid'=>$cate_id
			];
			$count = $cate_model->where($cateWhere)->count();
			if($count>0){
				$cateInfo = $cate_model->select();
				$c_id=getSonCateId($cateInfo,$cate_id);
				$where['g.cate_id']=['in',$c_id];
			}else{
				$where['g.cate_id']=$cate_id;
			}
		}
		if(!empty($brand_name)){
			$where['brand_name'] = $brand_name;
		}
		if(!empty($is_up)){
			$where['is_up'] = $is_up;
		}

		$model = model('Goods');
		$goodsInfo = $model
				->field("g.*,c.cate_name,b.brand_name")
				->alias('g')
				->join("shop_category c","g.cate_id=c.cate_id")
				->join("shop_brand b","g.brand_id=b.brand_id")
				->where($where)
				->page($page,$limit)
				->select();

		$count = $model->alias('g')
			  ->join("shop_category c","g.cate_id=c.cate_id")
			  ->join("shop_brand b","g.brand_id=b.brand_id")
			  ->where($where)
			  ->count();
		$info = [
			'code'=>0,
			'msg'=>'',
			'count'=>$count,
			'data'=>$goodsInfo
		];	
		echo json_encode($info);
    }

    /** 商品删除 */
	public function goodsDel(){
		$goods_id = input('post.goods_id');
		$where = [
			'goods_id'=>$goods_id
		];
		$model = model('Goods');
		$arra = $model->where($where)->value('goods_img');
		$imgs = $model->where($where)->value('goods_imgs');
		$image = rtrim($imgs,'|');
		$images = explode('|', $image);		
		$arr = $model->where($where)->delete();
		if($arr){
			foreach($images as $k => $v){
				unlink("goodsimgs/".$v);
			}
			unlink('goodsimg/'.$arra);
			successly('删除成功');
		}else{
			fail('删除失败');
		}
	}


    /** 即点即改 */
    public function goodsEditField(){
        //分别接受新值，id,修改的当前字段名
        $value = input('post.value');
        $goods_id = input('post.goods_id');
        $field = input('post.field');
        //条件
        $where = [
            'goods_id'=>$goods_id
        ];
        //字段=新值
        $data = [
            $field => $value
        ];
        //验证场景
        $scene = 'edit'.$field;
        $validate = validate('goods');
        $result = $validate->scene($scene)->check($data);
        if(!$result){
            fail($validate->getError());
        }
        $model = model('Goods');
        $arr = $model->where($where)->update($data);
        if($arr){
            successly('操作成功');
        }else{
            fail('操作失败');
        }
    }

    /** 修改 */
    public function goodsEdit(){
        if (checkRequest()){
            //修改执行
            $data = input('post.');
            //验证
            $validate = validate('Goods');
            if (!$validate->check($data)) {
                fail($validate->getError());
            }
            $where = [
                'goods_id'=>$data['goods_id']
            ];
            $model = model('Goods');
            $res = $model->allowField(true)->save($data,$where);
            if ($res){
                //修改成功删除图片
                successly('修改成功');
            }else{
                fail('修改失败');
            }
        }else{
            //修改展示
            $goods_id = input('get.goods_id');
            // 表单展示value值
            $model = model('Goods');
            $where = [
                'goods_id' => $goods_id
            ];
            $goodsInfo = $model->where($where)->find();
            $this->assign('goodsInfo',$goodsInfo);

            //下拉菜单
            $brandModel = model('Brand');
            $arr = $brandModel->select();           
            
            $info = $this->getCateInfo();
            $this->assign('info',$info);
            $this->assign('arr',$arr);
            
            return view();
        }
    }



}