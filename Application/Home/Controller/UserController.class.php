<?php
namespace Home\Controller;
//用户控制器
class UserController extends CommonController {
	//构造方法
	public function __construct() {
		parent::__construct();
		$allow_action = array( //指定不需要检查登录的方法列表
			'login','getverify','getVerify','register'
		);
		if($this->userinfo === false && !in_array(ACTION_NAME,$allow_action)){
			$this->error('请先登录。',U('User/login'));
		}
		$this->assign('title','会员中心 - opentrip');
	}
	//会员中心首页
	public function index(){
		$this->display();
	}
	//查看收件地址
	public function addr(){
		$data['addr'] = D('User')->getAddr($this->userinfo['id']);
		$this->assign($data);
		$this->display();
	}
	//修改收件地址
	public function addrEdit(){
		if(IS_POST){
			$User = D('User');
			if(!$User->create(null,2)){
				$this->error('修改失败：'.$User->getError(),U('User/addrEdit'));
			}
			if(false===$User->where(array('id'=>$this->userinfo['id']))->save()){
				$this->error('修改失败',U('User/addrEdit'));
			}
			$this->redirect('User/addr');
		}
		$this->addr();
	}
	//修改密码
	public function passwordEdit(){
	    if(IS_POST){
	        $User = D('User');
	        if(!$User->create(null,2)){
	            $this->error('修改失败：'.$User->getError(),U('User/passwordEdit'));
	        }
	        if(false===$User->where(array('id'=>$this->userinfo['id']))->save()){
	            $this->error('修改失败',U('User/passwordEdit'));
	        }
	        $this->redirect('User/passwordEdit');
	    }
	    $this->addr();
	}
	
	//用户登录
	public function login(){
		if(IS_POST){
			//检查验证码
			if(false===$this->checkVerify(I('post.verify'))){
				$this->error('验证码错误',U('User/login')); //指定跳转地址，防止验证码不刷新
			}
			//实例化模型
			$User = D('User');
			if(!$User->create()){
				$this->error('登录失败：'.$User->getError(),U('User/login'));
			}
			//检查用户名密码
			if($userinfo = $User->checkLogin()){
				//登录成功
				session('userinfo',$userinfo); //将登录信息保存到Session
				$this->redirect('Index/index');
			}
			$this->error('登录失败：用户名或密码错误。',U('User/login'));
		}
		$this->assign('title','会员登录 - opentrip');
		$this->display();
	}
	//用户注册
	public function register(){
		if(IS_POST){
			//检查验证码
			if(false===$this->checkVerify(I('post.verify'))){
				$this->error('验证码错误',U('User/register')); //指定跳转地址，防止验证码不刷新
			}
			//实例化模型
			$User = D('User');
			//判断用户名是否已经存在
			if($User->where(array('username'=>I('post.username')))->getField('id')){
				$this->error('注册失败：用户名已经存在。');
			}
			if(!$User->create(null,1)){
				$this->error('注册失败：'.$User->getError(),U('User/register'));
			}
			$username = $User->username; //取出用户名
			if(!$id = $User->add()){     //添加数据并取出新用户ID
				$this->error('注册失败：保存到数据库失败。',U('User/register'));
			}
			//注册成功后自动登录
			session('userinfo',array('id'=>$id,'name'=>$username));
			$this->redirect('Index/index');
		}
		$this->assign('title','会员注册 - opentrip');
		$this->display();
	}
	
	//生成验证码
    public function getVerify() {
        $Verify = new \Think\Verify();
		$Verify->entry();
    }
	//检查验证码
    private function checkVerify($code, $id = '') {
        $Verify = new \Think\Verify();
        return $Verify->check($code, $id);
    }
	//退出系统
	public function logout(){
		session(null); //清空前台所有会话
		$this->redirect('Index/index');
	}
}