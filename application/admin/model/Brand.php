<?php
namespace app\admin\model;
use think\Model;
class Brand extends Model
{
	public function getBrandShowAttr($value)
    {
        $status = [1=>'√',2=>'×'];
        return $status[$value];
    }

}