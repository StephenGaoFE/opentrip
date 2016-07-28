<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller{
	protected $userinfo = false;  //用户登录信息（未登录为false）
	//构造方法
	public function __construct() {
		parent::__construct();
		//登录检查
		$this->checkUser();
	}
	//检查登录
	private function checkUser(){
		if(session('?userinfo')){
			$this->userinfo = session('userinfo');
			$this->assign('userinfo',$this->userinfo);
		}
	}
	public function _empty($name){
		$this->error('无效的操作：'.$name);
    }
}
