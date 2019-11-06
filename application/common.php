<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/** 
 * @param 检测是否正常合法提交
 */
function checkRequest(){
	return request()->isAjax()&&request()->isPost();
}

/**  
 * @param  错误提示信息 
 */
function fail($font){
	echo json_encode(['font'=>$font,'code'=>2]);exit;
}

/** 
 * @param  正确提示信息
 */
function successly($font){
	echo json_encode(['font'=>$font,'code'=>1]);exit;
}

/**
 * 生成盐值
 * @return  string
 */
function createSalt(){
	$str = "qwertyuioplkjhgfdsazxcvbnm,./-*/!@#%&*";
	return substr(str_shuffle($str),rand(5,20),6);
}

/**
 * 生成密码
 * @param  $pwd 密码
 * @param  $salt 盐值
 * @return  string 生成后新密码
 */
function createPwd($pwd,$salt){
	//特有的加密方式
	return md5(md5($pwd).md5($salt).'1809');
}

/**
 * 递归方式查询分类信息
 * @param  $pid $level
 */
function getCateInfo($cateInfo,$pid=0,$level=0){
	static $arr = [];
	foreach($cateInfo as $k=>$v){
		if($v['pid']==$pid){
			$v['level'] = $level;
			$arr[] = $v;
			getCateInfo($cateInfo,$v['cate_id'],$v['level']+1);
		}
	}
	return $arr;
}

/**
 * 获取分类id
 * @param  $id
 */
function getSonCateId($cateInfo,$pid){
	static $id = [];
	foreach($cateInfo as $k =>$v){
		if($v['pid']==$pid){
			$id[]=$v['cate_id'];
			getSonCateId($cateInfo,$v['cate_id']);
		}
	}
	return $id;
}

/** 获取左侧分类信息 */
function getLeftCateInfo($cateInfo,$pid=0){
	$info=[];
	foreach ($cateInfo as $k => $v) {
		if($v['pid']==$pid){
			$son = getLeftCateInfo($cateInfo,$v['cate_id']);
			$v['son']=$son;
			$info[]=$v;
		}
	}
	return $info;
}

/**发送邮箱 */
function sendEmail($address,$code){
	//实例化PHPMailer核心类
	$mail = new \email\PHPMailer();

	
	//是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
	$mail->SMTPDebug =1;

	//使用smtp鉴权方式发送邮件
	$mail->isSMTP();

	//smtp需要鉴权 这个必须是true
	$mail->SMTPAuth=true;

	//链接qq域名邮箱的服务器地址
	$mail->Host = 'smtp.163.com';//163邮箱：smtp.163.com

	//设置使用ssl加密方式登录鉴权
	$mail->SMTPSecure = 'ssl';//163邮箱就注释

	//设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
	$mail->Port = 465;//163邮箱：25

	//设置smtp的helo消息头 这个可有可无 内容任意
	// $mail->Helo = 'Hello smtp.qq.com Server';

	//设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
	//$mail->Hostname = 'http://localhost/';

	//设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
	$mail->CharSet = 'UTF-8';

	//设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
	$mail->FromName = 'root';

	//smtp登录的账号 这里填入字符串格式的qq号即可
	$mail->Username ='jqq12345jqq@163.com';//自己

	//smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
	$mail->Password = 'jiao200419';//163邮箱也有授权码 进入163邮箱帐号获取

	//设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
	$mail->From = 'jqq12345jqq@163.com';//自己

	//邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
	$mail->isHTML(true);

	//设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
	$mail->addAddress($address);//活的，前边获取的注册的邮箱

	//添加多个收件人 则多次调用方法即可
	// $mail->addAddress('xxx@163.com','爱代码，爱生活世界');

	//添加该邮件的主题
	$mail->Subject = '注册成功';

	//添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
	// $mail->Body = '验证码为'.$code."一分钟内验证码有效";
	$mail->Body =$code."http://localhost/myshop/public/exam/regit";

	//为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
	// $mail->addAttachment('./d.jpg','mm.jpg');
	//同样该方法可以多次调用 上传多个附件
	// $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');

	$status = $mail->send();

	//简单的判断与提示信息
	if($status) {
		return true;
	}else{
		return false;
	}
}