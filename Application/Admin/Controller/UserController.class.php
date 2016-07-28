<?php
namespace Admin\Controller;
//前台会员控制器
class UserController extends CommonController{
	public function index(){
		//取出会员信息
		$data = D('User')->getList();
		$data['priv'] = session('userinfo.priv');
		$this->assign($data);
		$this->display();
	}
	//会员修改
	public function edit(){
	    //获取参数
	    $id = I('get.id/d',0);         //待修改ID
	    //实例化模型
	    $User = D('User');
	    
	    if(IS_POST){
	       //创建数据对象
			if(!$User->create(null,2)){
				$this->error('修改失败：'.$User->getError());
			}
			//保存到数据库
			if(false === $User->where(array('id'=>$id))->save()){
				$this->error('修改失败：保存到数据库失败。');
			}
			//保存成功
			$this->redirect('User/index');
	    }
	    //准备where条件
	    $where = array('id'=>$id);
	    //查询产品数据
	    $data['user'] = $User->getUser($where);
	    if(!$data['user']){
	        $this->error('用户不存在！');
	    }
	    
	    $data['priv'] = session('userinfo.priv');
	    $this->assign($data);
	    $this->display();
	}
	//删除
	public function del(){
	    //阻止直接访问
	    if(!IS_POST) $this->error('删除失败！');
	    //获取参数
	    $id = I('post.id/d',0);  //ID
	    //生成跳转地址
	    $jump = U('User/index');
	    //实例化模型
	    $User = D('User');
	    //检查表单令牌
	    if(!$User->autoCheckToken($_POST)){
	        $this->error('表单已过期，请重新提交',$jump);
	    }
	    //准备where条件
	    $where = array('id'=>$id);
	    //删除admin
	    $User->where($where)->delete();
	    //删除成功，跳转
	    redirect($jump);
	}
}