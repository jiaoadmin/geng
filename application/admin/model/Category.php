<?php
namespace app\admin\model;
use think\Model;
class Category extends Model
{
	public function getCateShowAttr($value)
    {
        $status = [1=>'√',2=>'×'];
        return $status[$value];
    }
    public function getCateNavShowAttr($value)
    {
        $status = [1=>'√',2=>'×'];
        return $status[$value];
    }
}