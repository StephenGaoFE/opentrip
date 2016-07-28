<?php
namespace Admin\Controller;
use Think\Controller;
//后台用户登录
class LoginController extends Controller {
    // 登录页
    public function index(){
        if (IS_POST) {
            // 检查验证码
            if (false === $this->checkVerify(I('post.verify'))) {
                $this->error('验证码错误', U('Login/index')); // 指定跳转地址，防止验证码不刷新
            }
            // 实例化模型
            $Admin = D('Admin');
            if (! $Admin->create()) {
                $this->error('登录失败：' . $Admin->getError(), U('Login/index'));
            }
            // //检查用户名密码
            $username = $Admin->username; //获取用户名
           
            $userinfo = $Admin->checkLogin();
            if ($userinfo) {
                // 登录成功
                session('userinfo', $userinfo); // 将登录信息保存到Session
                $this->redirect('Index/index');
            }
            $this->error('登录失败：用户名或密码错误。', U('Login/index'));
        }
        
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
		session(null); //清空后台所有会话
		$this->redirect('Login/index');
	}
	public function _empty($name){
		$this->error('无效的操作：'.$name);
    }
}