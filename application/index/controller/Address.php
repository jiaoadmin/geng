<?php
namespace app\index\controller;
use think\Controller;
class Address extends Common{
    /**收货地址 */
    public function address(){
        //先调用查的省的信息的方法
        $provinceInfo=$this->getAreaInfo(0);
        $addressInfo=$this->getAddressInfo();

        //将查到的省的信息传导前台
        $this->assign('provinceInfo',$provinceInfo);
        $this->assign('addressInfo',$addressInfo);

        return view();
    }

    /**获取地区信息 */
    public function getAreaInfo($pid){
        $where=[
            'pid'=>$pid
        ];
        $area_model=model('Area');
        $data=$area_model->where($where)->select();
        if(!empty($data)){
            return $data;
        }else{
            return false;
        }
    }

    /**获取下一级区域信息 */
    public function getArea(){
        $id=input('post.id');
        // dump($id);exit;
        if(empty($id)){
            fail('请必须选择一项');
        }
        $areaInfo=$this->getAreaInfo($id);
        echo json_encode(['areaInfo'=>$areaInfo,'code'=>1]);
    } 

    /**添加收货地址 */
    public function addressDo(){
        $data=input('post.');
        // dump($data);
        $address_model=model('Address');
        if($data['is_default']==1){
            $where=[
                'user_id'=>session('userInfo.user_id')
            ];
            $address_model->startTrans();//开启事务操作
            $result=$address_model->save(['is_default'=>2],$where);
            $res=$address_model->isUpdate(false)->save($data);
            if($result!==false&&$res){
                $address_model->commit();//事务提交
                successly('添加成功');
                // echo "添加成功";
            }else{
                $address_model->rollback();//回滚
                fail('添加失败');
                // echo "添加失败";
            }
        }else{
            $res=$address_model->save($data);
            if($res){
                successly('添加成功');
                // echo "添加成功";
            }else{
                fail('添加失败');
                // echo "添加失败";
            }
        }

    }

    /**点击编辑 */
    public function addressUpdate(){
		$address_model = model('Address');
		if(checkRequest()){
			$data = input('post.');
			// dump($data);die;			
			$where = [
				'address_id'=>$data['address_id']
			];
			//验证

			//执行修改
			if($data['is_default']==1){
				$address_model->startTrans();
				$addressWhere = [
					'user_id'=>session('userInfo.user_id'),
				];
				$res1 = $address_model->save(['is_default'=>2],$addressWhere);
				$res2 = $address_model->save($data,$where);
				if($res1!==false&&$res2!==false){
					$address_model->commit();
					successly('修改成功');
				}else{
					$address_model->rollback();
					fail('修改失败');
				}
			}else{
				$res = $address_model->save($data,$where);
				if($res){
					successly('修改成功');
				}else{
					fail('修改失败');
				}
			}
		}else{
			$address_id = input('get.address_id',0,'intval');
			// dump($address_id);exit;
			if(empty($address_id)){
				echo "请选择收货地址";exit;
			}		
			$addressWhere = [
				'address_id'=>$address_id,
				'address_status'=>1
            ];
            // dump($addressWhere);exit;
            $addressInfo = $address_model->where($addressWhere)->find();
            // echo $address_model->getLastSql();exit;
			// dump($addressInfo);exit;
			//查看所有的省份作为下拉菜单的值
            $provinceInfo = $this->getAreaInfo(0);
            // dump($provinceInfo);
            $cityInfo = $this->getAreaInfo($addressInfo['province']);
            // dump($cityInfo);
            $areaInfo = $this->getAreaInfo($addressInfo['city']);
            // dump($areaInfo);

			$this->assign('addressInfo',$addressInfo);
			$this->assign('provinceInfo',$provinceInfo);
			$this->assign('cityInfo',$cityInfo);
			$this->assign('areaInfo',$areaInfo);
			return view();
		}		
    }

    /**点击设置为默认 */
    public function setdefault(){
        $address_id=input('post.address_id');
        // dump($address_id);exit;
       
        // dump($where);
        $address_model=model('Address');
        if(empty($address_id)){
            fail('请至少选择一个收获地址');
        }

        $where=[
            'user_id'=>session('userInfo.user_id'),
        ];
        $addressWhere=[
            'address_id'=>$address_id,
            'user_id'=>session('userInfo.user_id')
        ];

        $address_model->startTrans();//开启事务操作
        $result=$address_model->save(['is_default'=>2],$where);
        $res=$address_model->save(['is_default'=>1],$addressWhere);
        if($result!==false&&$res){
            $address_model->commit();
            successly('设定成功');
        }else{
            $address_model->rollback();
            fail('设定失败');
        }

        
    }












}
?>