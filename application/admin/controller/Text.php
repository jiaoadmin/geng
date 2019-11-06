<?php
namespace app\admin\controller;
use think\Controller;

class Text extends Controller
{
	public function textAdd()
	{
		if(checkRequest()){
			$data = input('post.');

			$text_model = model('text');
			$dtime = $data['text_dtime'];					

			if($dtime == 1){
				
				$arra = $text_model->value('create_time');				
				$data['text_dtime']=$arra;		
			}else{
				
			}
			
			$res = $text_model->save($data);
			if($res){
				successly('发布成功');
			}else{
				fail('发布失败');
			}
		}else{
			$model = model('textcate');
			$info = $model->select();
			// dump($info);
			$this->assign('info',$info);
			return view();
		}
	}
}