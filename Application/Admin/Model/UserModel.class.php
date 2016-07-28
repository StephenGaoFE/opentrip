<?php
namespace Admin\Model;
use Think\Model;
class UserModel extends Model {
    
    protected $insertFields = 'username,phone,email';
    protected $updateFields = 'username,phone,email';
    
	//获得会员列表
	public function getList(){
		$field = 'id,username,phone,email';
		$where = array();
		$order = 'id asc';
		//查询数据
		$count = $this->where($where)->count();
		$Page = new \Think\Page($count,C('USER_CONFIG.pagesize'));
		$this->_customPage($Page); //定制分页类样式
		$limit = $Page->firstRow.','.$Page->listRows;
		//取得数据
		return array(
			'list' => $this->field($field)->where($where)->order($order)->limit($limit)->select(),
			'page' => $Page->show(),
		);
	}
	
	public function getUser($where){
	    //定义需要的字段
	    $field = 'username,phone,email';
	    return $this->field($field)->where($where)->find();
	}
	
	
	//定制分页类样式
	private function _customPage($Page){
		$Page->lastSuffix = false;
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('first','首页');
		$Page->setConfig('last','尾页');
	}
}
