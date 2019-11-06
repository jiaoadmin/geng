<?php
namespace app\admin\validate;
use think\Validate;

class Goods extends Validate
{
    protected $rule = [
        'goods_name'  =>  'require',
        'self_price' =>  'require|number|max:7',
        'market_price' =>  'require|number|max:7',
        'goods_num' =>  'require|number|max:7',
        'goods_score' =>  'require|number|max:7',
        'goods_img' =>  'require',
        'goods_imgs' =>  'require',
    ];

    protected $message = [
        'goods_name.require'  =>  '商品名称必填',
        'self_price.require'  =>  '本店售价必填',
        'market_price.require'  =>  '市场售价必填',
        'goods_num.require'  =>  '库存必填',
        'goods_score.require'  =>  '赠送积分必填',
        'self_price.number'  =>  '售价填写有效数字',
        'market_price.number'  =>  '售价填写有效数字',
        'goods_num.number'  =>  '库存填写有效数字',
        'goods_score.number'  =>  '赠送积分填写有效数字',
        'self_price.max'  =>  '请填写有效售价价格',
        'market_price.max'  =>  '请填写有效售价价格',
        'goods_img.require'  =>  '上传图片不能为空',
        'goods_imgs.require'  =>  '上传商品相册不能为空',
    ];

    //验证场景
    protected $scene = [
        'editgoods_name'  =>  ['goods_name'],  
        'editgoods_num'  =>  ['goods_num'],  
        'editself_price'  =>  ['self_price'],  
        'editmarket_price'  =>  ['market_price'],  
        'add'=>['cate_name'],
        'edit'=>['cate_name']
    ];

}