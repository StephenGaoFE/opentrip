<?php
namespace Home\Controller;
class OrderController extends CommonController {
	//构造方法
	public function __construct() {
		parent::__construct();
		if($this->userinfo === false){
			$this->error('请先登录。',U('User/login'));
		}
		$this->assign('title','我的订单 - opentrip');
	}
	
	//查看订单
	public function index(){
		//取出订单列表
		$data = M('Order')->where(array('user_id'=>$this->userinfo['id'],'cancel'=>'no'))->order('id desc')->select();
		foreach($data as $k=>$v){
			$data[$k]['product'] = unserialize($data[$k]['product']);
			$data[$k]['address'] = unserialize($data[$k]['address']);
		}
		$this->assign('order',$data);
		$this->display();
	}
	
	//购买产品（单件或多件产品）
	public function buy(){
		$buy = $this->_input();
		//实例化模型
		$Product = M('Product');
		$Order = M('Order');
		$this->checkToken($Order);    //检查表单令牌
		$uid = $this->userinfo['id']; //当前用户ID
		//准备待写入数据库的数据
		$data = array(
			'price' => 0,       //订单总价格
			'payment' => 'no',  //订单未支付
			'cancel' => 'no',   //订单未取消
			'user_id' => $uid,  //购买者的用户ID
		);
		//获取收件人信息（收件人，收件地址，手机）
		$data['address'] = M('User')->field('consignee,address,phone,email')->where(array('id'=>$uid))->find();
		//如果没有收件人，则不允许购买
// 		foreach(array('consignee','address','phone','email') as $v){
// 			if(empty($data['address'][$v])){
// 				$this->error('请先完善收货地址。',U('User/addr'));
// 			}
// 		}
		$Order->startTrans(); //开启事务
		//处理每件产品
		foreach($buy as $id=>$num){
			//查询出产品的名称、价格			
			$product = $Product->field('name,price')->where(array('id'=>$id))->find();
			if(empty($product)){
				$Order->db()->rollBack();  //回滚
				$this->error("您购买的产品不存在，错误的产品ID：{$id}。");
			}
			//组合产品信息
			$data['product'][] = array(
				'id' => $id,       //产品ID
				'num' => $num,     //购买数量
				'name' => $product['name'],   //产品名
				'price' => $product['price'], //价格
			);
			//准备库存操作的where条件
			$where = array(
				'id' => $id,                  //产品ID
				'stock' => array('EGT',$num), //库存不低于购买数量
				'recycle' => 'no',            //产品未在回收站中
				'on_sale' => 'yes',           //产品已经上架
			);
			//更新库存
			if(!$Product->where($where)->setDec('stock', $num)){
				$Order->rollback();  //回滚
				$this->error('执行失败，产品“'.$product['name'].'”库存不足。');
			}
			//价格自增
			$data['price'] += $product['price'] * $num;
		}
		//数组序列化
		$data['address'] = serialize($data['address']);
		$data['product'] = serialize($data['product']);
		//保存订单
		if(!$Order->data($data)->add()){
			$Order->rollBack();  //回滚
			$this->error('执行失败，生成订单失败。');
		}
		$Order->commit();    //提交事务
		
		
		$this->success('订单生成成功',U('Order/index'));
	}
	
	//取消订单
	public function cancel(){
		$id = I('post.id/d',0);
		$Product = M('Product');
		$Order = M('Order');
		$this->checkToken($Order); //检查表单令牌
		
		//将订单中的产品返库存
		$data_order = $Order->where(array('id'=>$id,'user_id'=>$this->userinfo['id']))->find();
		$data_product = unserialize($data_order['product']);
		foreach($data_product as $v){
			$Product->where(array('id' => $v['id']))->setInc('stock', $v['num']);
		}
		//取消订单（如果有订单回收站功能，则执行此步骤）
		if(false === $Order->where(array('id'=>$id,'user_id'=>$this->userinfo['id']))->save(array('cancel'=>'yes'))){
			$this->error('取消失败');
		}
		//删除订单（如果没有订单回收站功能，则执行此步骤）
		$Order->where(array('id'=>$id,'user_id'=>$this->userinfo['id']))->delete();

		$this->redirect('Order/index');
	}
	
	private function checkToken($Model){
		//检查表单令牌
		if(!$Model->autoCheckToken($_POST)){
			$this->error('表单已过期，请重新提交');
		}
	}
	
	//获取购买信息，并过滤数据
	private function _input(){
		$buy = I('post.buy/a'); //获取参数
		$data = array();  //保存关联数组结果 array(id=>num)
		//限制提交的订单中最多只能包含100种产品
		count($buy) > 100 && $this->error('一个订单中最多只能包含100种产品。');
		//从参数中取出每件产品的ID和购买数量
		foreach($buy as $v){
			if(isset($v['id']) && isset($v['num'])){				
				$v['id'] = max((int)$v['id'],0);   //产品ID不能为负数
				$v['num'] = max((int)$v['num'],1); //购买数量最少为1
				$data[$v['id']] = $v['num'];
			}else{
				continue;
			}
		}
		return $data;
	}
}