<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:75:"D:\phpstudy\WWW\myshop\public/../application/index\view\login\register.html";i:1552012545;}*/ ?>
<!DOCTYPE html>
<html>

<head lang="en">
	<meta charset="UTF-8">
	<title>注册</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<meta name="format-detection" content="telephone=no">
	<meta name="renderer" content="webkit">
	<meta http-equiv="Cache-Control" content="no-siteapp" />

	<link rel="stylesheet" href="__STATIC__/index/login/amazeui.css" />
	<link href="__STATIC__/index/login/dlstyle.css" rel="stylesheet" type="text/css">
	<link href="__STATIC__/layui/css/layui.css" rel="stylesheet" type="text/css">

	<script src="__STATIC__/layui/layui.js"></script>
	<script src="__STATIC__/index/login/jquery-3.2.1.min.js"></script>
	<script src="__STATIC__/index/login/amazeui.min.js"></script>

</head>

<body>
	<div class="login-boxtitle">
		<a href="home/demo.html"><img alt="" src="__STATIC__/index/login/logobig.png" /></a>
	</div>

	<div class="res-banner">
		<div class="res-main">
			<div class="login-banner-bg"><span></span><img src="__STATIC__/index/login/big.jpg" /></div>
			<div class="login-box">

			<div class="am-tabs" id="doc-my-tabs">
				<ul class="am-tabs-nav am-nav am-nav-tabs am-nav-justify">
					<li class="am-active"><a href="">邮箱注册</a></li>
					<li><a href="">手机号注册</a></li>
				</ul>

				<div class="am-tabs-bd">
					<!--邮箱注册-->
					<div class="am-tab-panel am-active">
						<form method="post" class="layui-form">
							<div class="user-email">
								<label for="email"><i class="am-icon-envelope-o"></i></label>
								<input type="email" name="user_email"  id="user_email" lay-verify="required|email"  placeholder="请输入邮箱账号">
							</div><!-- lay-verify="required|email" -->

							<div class="verification">
								<label for="email_code"><i class="am-icon-code-fork"></i></label>
								<input type="text" name="user_code" id="email_code" lay-verify="required" placeholder="请输入验证码">
								<a class="btn" href="javascript:void(0);"  id="sendEmailCode">
									<span class="dyButton" id="span_email">获取</span><!--  lay-verify="required" -->
								</a>
							</div>

							<div class="user-pass">
								<label for="email_pwd"><i class="am-icon-lock"></i></label>
								<input type="password" name="user_pwd" id="email_pwd" lay-verify="required|checkPwd"  placeholder="设置密码">
							</div><!-- lay-verify="required|checkPwd" -->

							<div class="user-pass">
								<label for="email_pwd1"><i class="am-icon-lock"></i></label>
								<input type="password" name="user_pwd1" id="email_pwd1" lay-verify="required|checkPwd1"  placeholder="确认密码">
							</div><!-- lay-verify="required|checkPwd1" -->
							<div class="am-cf">
								<input type="button" lay-submit lay-filter="emailDemo" value="注册" class="am-btn am-btn-primary am-btn-sm am-fl">
							</div>
						</form>
					</div>


						<!--手机号注册-->
						<div class="am-tab-panel">
							<form method="post" class="layui-form">
								<div class="user-phone">
									<label for="tel"><i class="am-icon-mobile-phone am-icon-md"></i></label>
									<input type="tel" name="user_tel" lay-verify="required|phone"  id="tel" placeholder="请输入手机号">
								</div>
								<div class="verification">
									<label for="tel_code"><i class="am-icon-code-fork"></i></label>
									<input type="tel" name="user_code" lay-verify="required" id="tel_code" placeholder="请输入验证码">
									<a class="btn" href="javascript:void(0);" id="sendTelCode">
										<span class="dyButton" id="span_tel">获取</span>
									</a>
								</div>
								<div class="user-pass">
									<label for="tel_pwd"><i class="am-icon-lock"></i></label>
									<input type="password" name="user_pwd" lay-verify="checkpwd1" id="tel_pwd" placeholder="设置密码">
								</div>
								<div class="user-pass">
									<label for="tel_pwd1"><i class="am-icon-lock"></i></label>
									<input type="password" name="user_pwd1" lay-verify="checkpwd2" id="tel_pwd1" placeholder="确认密码">
								</div>
								<div class="am-cf">
									<input type="button" lay-submit lay-filter="telDemo" value="注册" class="am-btn am-btn-primary am-btn-sm am-fl">
								</div>
							</form>

						</div>
					<script>
						$(function() {
							$('#doc-my-tabs').tabs();
							})
					</script>
				</div>
			</div>

		</div>
	</div>
	
	<div class="footer ">
		<div class="footer-hd ">
			<p>
				<a href="# ">恒望科技</a>
				<b>|</b>
				<a href="# ">商城首页</a>
				<b>|</b>
				<a href="# ">支付宝</a>
				<b>|</b>
				<a href="# ">物流</a>
			</p>
		</div>
		<div class="footer-bd ">
			<p>
				<a href="# ">关于恒望</a>
				<a href="# ">合作伙伴</a>
				<a href="# ">联系我们</a>
				<a href="# ">网站地图</a>
				<em>© 2015-2025 Hengwang.com 版权所有. 更多模板 <a href="http://www.cssmoban.com/" target="_blank" title="模板之家">模板之家</a> - Collect from <a href="http://www.cssmoban.com/" title="网页模板" target="_blank">网页模板</a></em>
			</p>
		</div>
	</div>
</body>
</html>

<script>
	$(function(){
		//引用form和layer模块
		layui.use(['form','layer'],function(){
			var form = layui.form;
			var layer= layui.layer;
			
			var emailFlag=false;
			//点击获取
			$('#sendEmailCode').click(function(){
				var user_email = $('#user_email').val();
				// console.log(user_email);
				//验证非空 
				var reg=/^\w+@\w+\.com$/;
				if(user_email==''){
					layer.msg('邮箱不能为空',{icon:2});
					return false;
				}else if(!reg.test(user_email)){
					layer.msg('请输入正确的邮箱格式',{icon:2});
					return false;
				}else{
					//验证唯一性
					$.ajax({
						type:'post',
						url:"<?php echo url('Login/checkEmail'); ?>",
						data:{user_email:user_email,type:1},
						async:false,
						success:function(res){
							// console.log(res);
							if(res.code==2){
								layer.msg(res.font,{icon:res.code});
								emailFlag=false;
							}else{
								emailFlag=true;
							}
						}
						,dataType:'json'						
					})
					if(emailFlag==false){
						return emailFlag;
					}
				}
				
				//计时器
				//点击‘获取’获得值
				$('#span_email').text(60+'s');
				//定义一个全局变量  
				_time=setInterval(secondLess,1000);

				//发送邮件
				$.post(
					"<?php echo url('Login/sendEmail'); ?>",
					{user_email:user_email},
					function(res){
						layer.msg(res.font,{icon:res.code});
					}
					,'json'
				)
			})//点击获取的结尾
			
			// //验证密码
			form.verify({
				checkPwd:function(value, item){ //value：表单的值、item：表单的DOM对象
					var reg = /^.{5,}$/;//密码位5位以上任意字符
					if (!reg.test(value)) {
						return "密码位6位任意字符";
					}
				},
				checkPwd1: function(value, item){ //value：表单的值、item：表单的DOM对象
					var checkPwd=$('#email_pwd').val() ;//密码位5位任意字符
					if (value!=checkPwd) {
						return "确认密码与密码保持一致";
					}
				}	
			});  

			//监听提交
			form.on('submit(emailDemo)', function(data){
				$.post(
					"<?php echo url('Login/register'); ?>",
					data.field,
					function(res){
						layer.msg(res.font,{icon:res.code,time:2000})
						location.href= "<?php echo url('Index/index'); ?>";	
						// console.log(res); 
					}
					,'json'
				);
          		return false;
        	});   

			//倒计时 封装的方法
			function secondLess(){
				//获得当前秒数（span里的值）
				var second=parseInt($('#span_email').text());
				if(second==0){
					$('#span_email').text('获取');
					//关闭计时器
					clearInterval(_time);
				}else{
					second=second-1;
					$('#span_email').text(second+'s');
				}
			}


		})//layui.use结尾
	})
</script>
