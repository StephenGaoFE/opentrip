<?php
namespace Home\Model;
use Think\Model;
class CategoryModel extends Model {
	
	//查询分类数据
	private function getData(){
		static $data = null;  //缓存查询结果
		if(!$data) $data = $this->field('id,name,pid')->select();
		return $data;
	}
	
	//获得分类列表
	public function getTree($level=3){
		return category_tree($this->getData(),0,$level);
	}
	
	//查找所有子孙分类ID
	public function getSubIds($id){
		$data = array($id); //将ID自身放入数组头部
		category_child($this->getData(),$data,$id);
		return $data;
	}
	
	//查找分类家谱
	public function getFamily($id){
		$id = max($id,0);
		return category_family($this->getData(),$id);
	}

	//查找分类面包屑导航
	public function getPath($id){
		$rst = category_parent($this->getData(),$id);
		return array_reverse($rst['pcat']);
	}
	
}