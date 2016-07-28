<?php
namespace Admin\Model;
use Think\Model;
//后台用户登录
class adminModel extends Model {
	//自动验证
	protected $_validate = array(
		array('username','/^\w{4,10}$/','用户名不合法（4~10位，英文、数字、下划线）',self::VALUE_VALIDATE), //self::MUST_VALIDATE的值为1
		array('password','/^\w{6,12}$/','密码不合法（6~12位，英文、数字、下划线）',self::MUST_VALIDATE),
	);
	protected $insertFields = 'username,password';
	protected $updateFields = 'username,password';
	
	//判断管理员用户名和密码
	public function checkLogin(){
		$username = $this->data['username']; //表单提交的用户名
		$password = $this->data['password']; //表单提交的密码
		//根据用户名查询密码
		$data = $this->field('id,password,salt,priv')->where(array('username' => $username))->find();
		//判断密码
		if($data && $data['password'] == $this->password($password,$data['salt'])){
		    return array('id'=>$data['id'],'priv'=>$data['priv'],'name'=>$username);
		}
		return false;
	}
	//密码加密函数
	private function password($password,$salt){
		return md5(md5($password).$salt);
	}
	//获得管理员列表
	public function getList(){
	    $field = 'id,username,priv';
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
	//定制分页类样式
	private function _customPage($Page){
	    $Page->lastSuffix = false;
	    $Page->setConfig('prev','上一页');
	    $Page->setConfig('next','下一页');
	    $Page->setConfig('first','首页');
	    $Page->setConfig('last','尾页');
	}
	//插入数据前的回调方法
	protected function _before_insert(&$data,$option) {
	    $data['salt'] = substr(uniqid(), -6);
	    $data['password'] = $this->password($data['password'],$data['salt']);
	}
	//插入数据前的回调方法
	protected function _before_update(&$data,$option) {
	    $data['salt'] = substr(uniqid(), -6);
	    $data['password'] = $this->password($data['password'],$data['salt']);
	}
}
