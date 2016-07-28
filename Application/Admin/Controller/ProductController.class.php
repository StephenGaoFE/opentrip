<?php
namespace Admin\Controller;
//产品控制器
class ProductController extends CommonController {
	
	//产品列表
	public function index() {
		//获取参数
		$p = I('get.p/d',0);       //当前页码
		$cid = I('get.cid/d',-1);  //分类ID（0表示未分类，-1表示全部分类）
		//实例化模型
		$Product = D('Product');
		$Category = D('Category');
		//如果分类ID大于0，则取出所有子分类ID
		$cids = ($cid>0) ? $Category->getSubIds($cid) : $cid;
		//获取产品列表
		$data['product'] = $Product->getList('Product',$cids,$p,session('userinfo.priv'));
		//防止空页被访问
		if(empty($data['product']['data']) && $p > 0){
			$this->redirect('Product/index',array('cid'=>$cid));
		}
		//查询分类列表
		$data['category'] = $Category->getList();
		$data['cid'] = $cid;
		$data['p'] = $p;
		$data['priv'] = session('userinfo.priv');
		$this->assign($data);
		$this->display();
	}
	
	//产品添加
	public function add(){
		//获取参数
		$cid = I('get.cid/d',0);  //分类ID
		if($cid < 0) $cid = 0;    //防止分类ID为负数
		
		
		//实例化模型
		$Category = D('Category');
		$Product = D('Product');
		if(IS_POST){
			//创建数据对象
			if(!$Product->create(null,1)){
				$this->error('添加产品失败：'.$Product->getError());
			}
			//写入管理员id
			$Product->admin_id = session('userinfo.id');
			//处理特殊字段
			$Product->category_id = $cid; //产品分类
			$Product->thumb = '';         //产品预览图
			$Product->pic1 = '';
			$Product->pic2 = '';
			$Product->pic3 = '';
			$Product->pic4 = '';
			$Product->pic5 = '';
			$Product->desc = I('post.desc','','htmlpurifier'); //产品描述（富文本过滤）
			$Product->travel_ref = I('post.travel_ref','','htmlpurifier');
			$Product->reserve_notice = I('post.reserve_notice','','htmlpurifier');
			$Product->restrict = I('post.restrict','','htmlpurifier');
			$Product->cost_desc = I('post.cost_desc','','htmlpurifier');
			$Product->info = I('post.info','','htmlpurifier');
			//如果有图片上传，则上传并生成预览图
			if(isset($_FILES['thumb']) && $_FILES['thumb']['error']===0) {
				$rst = $Product->uploadThumb('thumb');  //上传并生成预览图
				if(!$rst['flag']){					  //判断是否上传成功
					$this->error('上传图片失败：'.$rst['error']);
				}
				$Product->thumb = $rst['path'];  //上传成功，保存文件路径
			}
			if(isset($_FILES['pic1']) && $_FILES['pic1']['error']===0) {
			    $rst = $Product->uploadPic('pic1');  //上传并生成预览图
			    if(!$rst['flag']){					  //判断是否上传成功
			        $this->error('上传图片失败：'.$rst['error']);
			    }
			    $Product->pic1 = $rst['path'];  //上传成功，保存文件路径
			}
			if(isset($_FILES['pic2']) && $_FILES['pic2']['error']===0) {
			    $rst = $Product->uploadPic('pic2');  //上传并生成预览图
			    if(!$rst['flag']){					  //判断是否上传成功
			        $this->error('上传图片失败：'.$rst['error']);
			    }
			    $Product->pic2 = $rst['path'];  //上传成功，保存文件路径
			}
			if(isset($_FILES['pic3']) && $_FILES['pic3']['error']===0) {
			    $rst = $Product->uploadPic('pic3');  //上传并生成预览图
			    if(!$rst['flag']){					  //判断是否上传成功
			        $this->error('上传图片失败：'.$rst['error']);
			    }
			    $Product->pic3 = $rst['path'];  //上传成功，保存文件路径
			}
			if(isset($_FILES['pic4']) && $_FILES['pic4']['error']===0) {
			    $rst = $Product->uploadPic('pic4');  //上传并生成预览图
			    if(!$rst['flag']){					  //判断是否上传成功
			        $this->error('上传图片失败：'.$rst['error']);
			    }
			    $Product->pic4 = $rst['path'];  //上传成功，保存文件路径
			}
			if(isset($_FILES['pic5']) && $_FILES['pic5']['error']===0) {
			    $rst = $Product->uploadPic('pic5');  //上传并生成预览图
			    if(!$rst['flag']){					  //判断是否上传成功
			        $this->error('上传图片失败：'.$rst['error']);
			    }
			    $Product->pic5 = $rst['path'];  //上传成功，保存文件路径
			}
			//添加到数据库
			if(!$Product->add()){
				$this->error('添加产品失败！');
			}
			//添加产品成功
			if(isset($_POST['return'])) $this->redirect('Product/index');
			$this->assign('success',true);
		}
		//查询分类列表
		$data['category'] = $Category->getList();
		$data['cid'] = $cid;
		$data['priv'] = session('userinfo.priv');
		
		//读取下拉菜单数据
		$data['days'] = $Product->getData("days");
		$data['detail_property'] = $Product->getData("detail_property");
		$data['visa_type'] = $Product->getData("visa_type");
		$data['continent'] = $Product->getData("continent");
		//邮轮
		$data['ship_route'] = $Product->getData("ship_route");
		$data['ship_company'] = $Product->getData("ship_company");
		$data['ship_name'] = $Product->getData("ship_name");
		$data['ship_depart_city'] = $Product->getData("ship_depart_city");
		$data['ship_depart_time'] = $Product->getData("ship_depart_time");
		
		
		
		$this->assign($data);
		$this->display();
	}
	
	//产品修改
	public function edit(){
		//获取参数
		$id = I('get.id/d',0);         //待修改产品ID
		$p = I('get.p/d',0);           //当前页码
		$cid = I('get.cid/d',0,'abs'); //待修改产品的分类ID
		//实例化模型
		$Category = D('Category');
		$Product = D('Product');
		 //准备where条件
		$where = array('id'=>$id,'recycle'=>'no');
		if(IS_POST){
			//创建数据对象
			if(!$Product->create(null,2)){
				$this->error('修改产品失败：'.$Product->getError());
			}
			//处理特殊字段
			$Product->category_id = $cid;    //保存产品分类
			$Product->desc = I('post.desc','','htmlpurifier'); //产品描述（富文本过滤）
			$Product->travel_ref = I('post.travel_ref','','htmlpurifier');
			$Product->reserve_notice = I('post.reserve_notice','','htmlpurifier');
			$Product->restrict = I('post.restrict','','htmlpurifier');
			$Product->cost_desc = I('post.cost_desc','','htmlpurifier');
			$Product->info = I('post.info','','htmlpurifier');
			//如果有预览图文件上传，则更新预览图
			if(isset($_FILES['thumb']) && $_FILES['thumb']['error']===0) {
				$rst = $Product->uploadThumb('thumb');  //上传并生成预览图
				if(!$rst['flag']){					  //判断是否上传成功
					$this->error('上传图片失败：'.$rst['error']);
				}
				$Product->thumb = $rst['path'];  //上传成功，保存文件路径
				$Product->delThumbFile($where);  //删除产品图片
			}
			//如果有焦点图文件上传，则更新焦点图
			if(isset($_FILES['pic1']) && $_FILES['pic1']['error']===0) {
			    $rst = $Product->uploadPic('pic1');  //上传并生成预览图
			    if(!$rst['flag']){					  //判断是否上传成功
			        $this->error('上传图片失败：'.$rst['error']);
			    }
			    $Product->pic1 = $rst['path'];  //上传成功，保存文件路径
			    $Product->delPicFile($where,'pic1');  //删除产品图片
			}
			//如果有焦点图文件上传，则更新焦点图
			if(isset($_FILES['pic2']) && $_FILES['pic2']['error']===0) {
			    $rst = $Product->uploadPic('pic2');  //上传并生成预览图
			    if(!$rst['flag']){					  //判断是否上传成功
			        $this->error('上传图片失败：'.$rst['error']);
			    }
			    $Product->pic2 = $rst['path'];  //上传成功，保存文件路径
			    $Product->delPicFile($where,'pic2');  //删除产品图片
			}//如果有焦点图文件上传，则更新焦点图
			if(isset($_FILES['pic3']) && $_FILES['pic3']['error']===0) {
			    $rst = $Product->uploadPic('pic3');  //上传并生成预览图
			    if(!$rst['flag']){					  //判断是否上传成功
			        $this->error('上传图片失败：'.$rst['error']);
			    }
			    $Product->pic3 = $rst['path'];  //上传成功，保存文件路径
			    $Product->delPicFile($where,'pic3');  //删除产品图片
			}//如果有焦点图文件上传，则更新焦点图
			if(isset($_FILES['pic4']) && $_FILES['pic4']['error']===0) {
			    $rst = $Product->uploadPic('pic4');  //上传并生成预览图
			    if(!$rst['flag']){					  //判断是否上传成功
			        $this->error('上传图片失败：'.$rst['error']);
			    }
			    $Product->pic4 = $rst['path'];  //上传成功，保存文件路径
			    $Product->delPicFile($where,'pic4');  //删除产品图片
			}//如果有焦点图文件上传，则更新焦点图
			if(isset($_FILES['pic5']) && $_FILES['pic5']['error']===0) {
			    $rst = $Product->uploadPic('pic5');  //上传并生成预览图
			    if(!$rst['flag']){					  //判断是否上传成功
			        $this->error('上传图片失败：'.$rst['error']);
			    }
			    $Product->pic5 = $rst['path'];  //上传成功，保存文件路径
			    $Product->delPicFile($where,'pic5');  //删除产品图片
			}
			
			
			//保存到数据库
			if(false === $Product->where($where)->save()){
				$this->error('修改产品失败！');
			}
			//修改产品成功
			if(isset($_POST['return'])){
			    $this->redirect('Product/index',array('cid'=>$cid,'p'=>$p));
			    alert("修改成功！");
			}
			$this->assign('success',true);
		}
		//查询产品数据
		$data['product'] = $Product->getProduct($where);
		if(!$data['product']){
			$this->error('修改失败：产品不存在。');
		}
		//查询分类列表
		$data['category'] = $Category->getList();
		$data['cid'] = $cid;
		$data['id'] = $id;
		$data['p'] = $p;
		$data['priv'] = session('userinfo.priv');
		//读取下拉菜单数据
		$data['days'] = $Product->getData("days");
		$data['detail_property'] = $Product->getData("detail_property");
		$data['visa_type'] = $Product->getData("visa_type");
		$data['continent'] = $Product->getData("continent");
		//邮轮
		$data['ship_route'] = $Product->getData("ship_route");
		$data['ship_company'] = $Product->getData("ship_company");
		$data['ship_name'] = $Product->getData("ship_name");
		$data['ship_depart_city'] = $Product->getData("ship_depart_city");
		$data['ship_depart_time'] = $Product->getData("ship_depart_time");
		
		$this->assign($data);
		$this->display();
	}
	
	//删除产品（放入回收站）
	public function del() {
		//阻止直接访问
		if(!IS_POST) $this->error('删除失败：未选择产品');
		//获取参数
		$cid = I('get.cid/d',0); //分类ID
		$p = I('get.p/d',0);     //当前页码
		$id = I('post.id/d',0);  //待处理的产品ID
		//生成跳转地址
		$jump = U('Product/index',array('cid'=>$cid,'p'=>$p));
		//实例化模型
		$Product = M('Product');
		//检查表单令牌
		if(!$Product->autoCheckToken($_POST)){
			$this->error('表单已过期，请重新提交',$jump);
		}
		//将产品放入回收站
		if(false === $Product->where(array('id'=>$id))->save(array('recycle'=>'yes'))){
			$this->error('删除产品失败',$jump);
		}
		redirect($jump); //删除成功，跳转
	}
	
	//产品列表快捷修改
	public function change(){
		//阻止直接访问
		if(!IS_POST) $this->error('操作失败：未选择产品');
		//获取参数
		$cid = I('get.cid/d',0);    //分类ID
		$p = I('get.p/d',0);        //当前页码
		$id = I('post.id/d',0);     //待处理产品ID
		$field = I('post.field');   //待处理字段
		$status = I('post.status');	//待处理字段值
		//生成跳转地址
		$jump = U('Product/index',array('cid'=>$cid,'p'=>$p));
		//实例化模型
		$Product = M('Product');
		//检测输入变量
		if($field!='on_sale' && $field!='recommend'){
			$this->error('操作失败：非法字段');
		}
		if($status!='yes' && $status!='no'){
			$this->error('操作失败：非法状态值');
		}
		//检查表单令牌
		if(!$Product->autoCheckToken($_POST)){
			$this->error('表单已过期，请重新提交',$jump);
		}
		//执行操作
		if(false === $Product->where(array('id'=>$id,'recycle'=>'no'))->save(array($field=>$status))){
			$this->error('操作失败：数据库保存失败',$jump);
		}
		redirect($jump); //操作成功，跳转
	}
	
	//产品详情 在线编辑器 图片上传
	public function uploadImage(){
		//上传目录
		$savePath = './Public/Uploads/desc';
		//上传配置
		$config = array(
			'savePath' => $savePath,     //存储文件夹
			'subPath' => date('Y-m/d'),  //子目录
			'allowFiles' => array('.gif','.png','.jpg','.jpeg','.bmp')  //允许的文件格式
		);
		//实例化UMEditor配套的文件上传类
		$Upload = new \Components\Uploader('upfile',$config);
		
		//返回JSON数据给UMEditor
		$type = $_REQUEST['type'];
		$callback=$_GET['callback'];
		$info = $Upload->getFileInfo();
		$info = $callback ? "<script>$callback(".json_encode($info).')</script>' : json_encode($info);
		$this->ajaxReturn($info,'EVAL');
	}
}