<?php
namespace Admin\Controller;
use Think\Controller;
//后台公共控制器
class CommonController extends Controller{
	//构造方法
    public function __construct() {
        parent::__construct(); //先执行父类构造方法
		$this->checkUser();    //登录检查
		//已经登录，为模板分配用户名变量
		$this->assign('admin_name',session('userinfo.name'));
		$this->assign('admin_priv',session('userinfo.priv'));
    }
	//检查用户是否已经登录
	private function checkUser(){
		if(!session('?userinfo')){
			//未登录，请先登录
			$this->redirect('Login/index');
		}
	}
	public function _empty($name){
		$this->error('无效的操作：'.$name);
    }
}
