<?php
namespace app\admin\model;
use think\Model;
class Goods extends Model
{
	public function getIsUpAttr($value)
    {
        $status = [1=>'√',2=>'×'];
        return $status[$value];
    }
    public function getIsNewAttr($value)
    {
        $status = [1=>'√',2=>'×'];
        return $status[$value];
    }
    public function getIsBestAttr($value)
    {
        $status = [1=>'√',2=>'×'];
        return $status[$value];
    }
    public function getIsHotAttr($value)
    {
        $status = [1=>'√',2=>'×'];
        return $status[$value];
    }	
}