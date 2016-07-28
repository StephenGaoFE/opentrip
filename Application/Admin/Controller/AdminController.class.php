<?php
namespace Admin\Controller;
//前台会员控制器
class AdminController extends CommonController{
	public function index(){
		//取出会员信息
		$data = D('Admin')->getList();
		$data['priv'] = session('userinfo.priv');
		$data['id'] = session('userinfo.id');
		$this->assign($data);
		$this->display();
	}
	//添加管理员
	public function add(){
	    //实例化模型
	    $Category = D('Admin');
	    if(IS_POST){
	        //创建数据对象
	        if(!$Category->create(null,1)){
	            $this->error('添加失败：'.$Category->getError());
	        }
	        //添加到数据库
	        if(!$Category->add()){
	            $this->error('添加失败：保存到数据库失败。');
	        }
	        //添加成功
	        if(isset($_POST['return'])) $this->redirect('Admin/index');
	        $this->assign('success',true);
	    }
	    
	    $this->display();
	}
	//修改管理员密码
	public function edit(){
	    //获取参数
		$id = I('get.id/d',0);  //待修改的ID，“/d”用于转换为整型
		//安全机制判定
		if($id != session('userinfo.id') && session('userinfo.id') != 1){
		    $this->error('非法操作,不能修改别人的密码！');
		}
		
		//实例化模型
		$Admin = D('Admin');
		if(IS_POST){
			
			//创建数据对象
			if(!$Admin->create(null,2)){
				$this->error('修改失败：'.$Admin->getError());
			}
			//保存到数据库
			if(false === $Admin->where(array('id'=>$id))->save()){
				$this->error('修改失败：保存到数据库失败。');
			}
			//保存成功
			$this->redirect('Admin/index');
		}
		$data['id'] = $id;
		$this->assign($data);
		$this->display();
	}
	//删除管理员
	public function del(){
	    //阻止直接访问
	    if(!IS_POST) $this->error('删除失败！');
	    //获取参数
	    $id = I('post.id/d',0);  //ID
	    //生成跳转地址
	    $jump = U('Admin/index');
	    //实例化模型
	    $Admin = D('Admin');
	    //检查表单令牌
	    if(!$Admin->autoCheckToken($_POST)){
	        $this->error('表单已过期，请重新提交',$jump);
	    }
	    //准备where条件
	    $where = array('id'=>$id);
	    //删除admin
	    $Admin->where($where)->delete();
	    //删除成功，跳转
	    redirect($jump);
	}
}