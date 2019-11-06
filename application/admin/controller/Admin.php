<?php
namespace app\admin\controller;

class Admin extends Common {
    
	/** 管理员用户名添加 */
  public function adminAdd(){
    //判断是否ajax提交
    if (checkRequest()){
      $data = input('post.');
    //接收表单提交过来的数据
    //验证器验证
    $validate=validate('Admin');
    //调用验证类的check方法完成验证
    $result=$validate->scene('add')->check($data);
    //if判断
    if(!$result){
        fail($validate->getError());
    }
    //数据入库
    $res=model('admin')->save($data);
      if($res){
          successly('添加成功');
      }else{
          fail("添加失败");
      }
    }else{
      //如果不是ajax请求，直接展示添加页面
      return view();
    }
    
  }

  //验证唯一性
  public function checkName(){
    //接收name值
    $admin_name=input('post.admin_name');
    //接收type 判断是什么操作      
    $type = input('post.type');      
    if($type == 1){
      $where=[
        'admin_name'=>$admin_name,
      ];
    }else{
      $admin_id = input('post.admin_id');
      $where = [
        'admin_id'=>['neq',$admin_id],
        'admin_name'=>$admin_name
      ];
    }     
    
    //数据库查数据（查1条数据）
    $arr=model('admin')->where($where)->find();        
    if($arr){
        echo 'no';
    }else{
        echo 'ok';
    }
  }

  //管理员展示
  public function adminList(){
    return view();
  }

  //获取管理员数据
  public function adminInfo(){
    $page = input('get.page');
    $limit = input('get.limit');
    $admin_name = input('get.admin_name');

    $where = [];
    if(!empty($admin_name)){
      $where['admin_name']=['like',"%$admin_name%"];
    }
    
    $data = model('admin')->where($where)->page($page,$limit)->select();
    $count = model('admin')->where($where)->count();
    $info=[
      'code'=>0,
      'msg'=>'',
      'count'=>$count,
      'data'=>$data,
    ];
    echo json_encode($info);
  }

  //单元格编辑
  public function adminEditField(){
    $value = input('post.value');
    $admin_id = input('post.admin_id');
    $field = input('post.field');
    
    $where = [
      'admin_id'=>$admin_id
    ];

    $data = [
      $field => $value
    ];
    //验证
    $scene = 'edit'.$field;
    $validate=validate('Admin');
    //调用验证类的check方法完成验证
    $result=$validate->scene($scene)->check($data);
    //if判断
    if(!$result){
      fail($validate->getError());
    } 
    $admin_model = model('Admin');
    $res = $admin_model->where($where)->update($data);
    if($res){
      successly('操作成功');
    }else{
      fail("操作失败");
    }
  }


  //管理员删除
  public function adminDel(){
    $admin_id = input('post.admin_id');
    $where = [
      'admin_id'=>$admin_id
    ];
    $admin_model = model('Admin');
    $res = $admin_model->where($where)->delete();
    if($res){
      successly('删除成功');
    }else{
      fail('删除失败');
    }
  }

  //管理员修改
  public function adminEdit(){
    $admin_id = input('get.admin_id','','intval');
    if(empty($admin_id)){
      $this->error('请正确操作');exit;
    }
    $where = [
        'admin_id'=>$admin_id
    ];
    $admin_model = model('Admin');
    $adminInfo = $admin_model->where($where)->find();
    // dump($adminInfo);exit;
    if(empty($adminInfo)){
      $this->error('请正确操作');exit;
    }
    $this->assign('adminInfo',$adminInfo);
    return view();
  }


  //管理员修改执行
  public function adminEditDo(){
    $data = input('post.');

    //验证
    $validate=validate('Admin');
    //调用验证类的check方法完成验证
    $result=$validate->scene('edit')->check($data);
    //if判断
    if(!$result){
      fail($validate->getError());
    } 
      
    //修改
    $where = [
      'admin_id'=>$data['admin_id']
    ];
    $admin_model = model('Admin');
    $res = $admin_model->where($where)->update($data);
    //echo $admin_model->getLastSql();die;
    if($res === 'false'){
      fail("操作失败");
    }else{
      successly('操作成功');
    }
  }



}

   