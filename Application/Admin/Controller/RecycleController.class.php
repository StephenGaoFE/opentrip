<?php
namespace Admin\Controller;
//回收站控制器
class RecycleController extends CommonController{
	
	//显示回收站产品列表
	public function index(){
		//获取参数
		$p = I('get.p/d',0);  //当前页码
		//实例化模型
		$Product = D('Product');
		//查询产品列表
		$data['product'] = $Product->getList('recycle',-1,$p);
		//防止查询到空页
		if(empty($data['product']['data']) && $p > 0){
			$this->redirect('Recycle/index');
		}
		$data['p'] = $p;
		$data['priv'] = session('userinfo.priv');
		$data['id'] = session('userinfo.id');
		$this->assign($data);
		$this->display();
	}
	
	//恢复产品
	public function rec(){
		//阻止直接访问
		if(!IS_POST) $this->error('恢复失败：未选择产品');
		//获取参数
		$p = I('get.p/d',0);   //当前页码
		$id = I('post.id/d',0); //产品ID
		//生成跳转地址
		$jump = U('Recycle/index',array('p'=>$p));
		//实例化模型
		$Product = M('Product');
		//检查表单令牌
		if(!$Product->autoCheckToken($_POST)){
			$this->error('表单已过期，请重新提交',$jump);
		}
		//将产品取消删除
		if(false === $Product->where(array('id'=>$id))->save(array('recycle'=>'no'))){
			$this->error('恢复产品失败',$jump);
		}
		redirect($jump); //恢复成功，跳转
	}
	
	//彻底删除产品
	public function del(){
		//阻止直接访问
		if(!IS_POST) $this->error('删除失败：未选择产品');
		//获取参数
		$p = I('get.p/d',0);     //当前页码
		$id = I('post.id/d',0);  //产品ID
		//生成跳转地址
		$jump = U('Recycle/index',array('p'=>$p));
		//实例化模型
		$Product = D('Product');
		//检查表单令牌
		if(!$Product->autoCheckToken($_POST)){
			$this->error('表单已过期，请重新提交',$jump);
		}
		//准备where条件
		$where = array('id'=>$id,'recycle'=>'yes');
		//删除产品图片
		$Product->delThumbFile($where);
		$Product->delPicFile($where,'pic1');
		$Product->delPicFile($where,'pic2');
		$Product->delPicFile($where,'pic3');
		$Product->delPicFile($where,'pic4');
		$Product->delPicFile($where,'pic5');
		//删除产品数据
		$Product->where($where)->delete();
		//删除成功，跳转
		redirect($jump);
	}
}