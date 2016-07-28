<?php
namespace Admin\Model;
use Think\Model;
class ProductModel extends Model {
	//表单字段过滤，这里控制能够提交的字段
	protected $insertFields = 'sn,name,price,market_price,child_price,deposit,stock,promotion,confirm,on_sale,recommend,continent,country,city,start,path,end,detail_property,highlight1,highlight2,highlight3,days,start_time,language,people,place_of_issue,visa_type,sent_visa_place,ship_depart_city,ship_route,ship_company,ship_name,ship_depart_time,plan1_name,plan1_price,plan2_name,plan2_price,plan3_name,plan3_price,plan4_name,plan4_price,plan5_name,plan5_price,plan6_name,plan6_price,info_fee,discount';
	protected $updateFields = 'sn,name,price,market_price,child_price,deposit,stock,promotion,confirm,on_sale,recommend,continent,country,city,start,path,end,detail_property,highlight1,highlight2,highlight3,days,start_time,language,people,place_of_issue,visa_type,sent_visa_place,ship_depart_city,ship_route,ship_company,ship_name,ship_depart_time,plan1_name,plan1_price,plan2_name,plan2_price,plan3_name,plan3_price,plan4_name,plan4_price,plan5_name,plan5_price,plan6_name,plan6_price,info_fee,discount';
	//自动验证
	protected $_validate = array(
		array('name','1,40','产品名称不合法（1-40个字符）',self::MUST_VALIDATE,'length'),
// 		array('on_sale',array('yes','no'),'on_sale字段填写错误',self::MUST_VALIDATE,'in'),
// 		array('recommend',array('yes','no'),'recommend字段填写错误',self::MUST_VALIDATE,'in'),
		array('price','0,100000','产品价格输入不合法（0~100000）',self::MUST_VALIDATE,'between'),
		array('stock','0,900000','产品库存输入不合法',self::MUST_VALIDATE,'between'),
	);

	/**
	 * 产品列表
	 * @param string $type 数据用途（产品列表或回收站列表）
	 * @param array|int $cids 分类ID数组
	 * @param int $p 当前页码
	 * @param int $priv 当前页码
	 * @return array 查询结果
	 */
	public function getList($type='Product',$cids=0,$p=0,$priv=0){
		//准备查询条件
		$order = 'g.id desc';        //排序条件
		$field = 'c.name as category_name,g.category_id,g.id,g.name,g.on_sale,g.stock,g.recommend,g.admin_id';
		if($type=='Product'){          //产品列表页取数据时
			$where = array('g.recycle' => 'no');
		}elseif($type=='recycle'){   //产品回收站取数据时
			$where = array('g.recycle' => 'yes');
		}
		//cids=0查找未分类产品，cid>0查找分类ID数组产品，cid<0查找全部产品
		if($cids == 0){      //查找未分类的产品
			$where['g.category_id'] = 0;
		}elseif($cids > 0){  //查找分类ID数组
			$where['g.category_id'] = array('in',$cids);
		}
		//权限判断
		if($priv > 0){      //普通管理员作限制，超级管理员不限制
		    $where['g.admin_id'] = session('userinfo.id');
		}
		
		
		//准备分页查询,从config读取分页设置
		$pagesize = C('USER_CONFIG.pagesize');              //每页显示产品数
		//alias用于设置当前数据表的别名，便于使用其他的连贯操作例如join方法等。
		$count = $this->alias('g')->where($where)->count(); //获取符合条件的产品总数
		$Page = new \Think\Page($count,$pagesize);          //实例化分页类
		$this->_customPage($Page);                          //定制分页类样式
		//查询数据,左连接left join
		$data = $this->alias('g')->join('__CATEGORY__ AS c ON c.id=g.category_id','LEFT')->field($field)
				->where($where)->order($order)->page($p,$pagesize)->select();
		//返回结果
		return array(
			'data' => $data,              //产品列表数组
			'pagelist' => $Page->show(),  //分页链接HTML
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
	
	//根据$where条件查询产品数据，负责后台的数据读取功能
	public function getProduct($where){
		//定义需要的字段
		$field = 'id,category_id,sn,name,price,market_price,child_price,deposit,stock,promotion,confirm,on_sale,recommend,continent,country,city,start,path,end,detail_property,highlight1,highlight2,highlight3,days,start_time,language,people,thumb,pic1,pic2,pic3,pic4,pic5,desc,travel_ref,reserve_notice,restrict,cost_desc,info,place_of_issue,visa_type,sent_visa_place,ship_depart_city,ship_route,ship_company,ship_name,ship_depart_time,plan1_name,plan1_price,plan2_name,plan2_price,plan3_name,plan3_price,plan4_name,plan4_price,plan5_name,plan5_price,plan6_name,plan6_price,info_fee,discount';
		return $this->field($field)->where($where)->find();
	}
	
	//根据$where条件删除产品预览图文件
	public function delThumbFile($where){
		//取出原图文件名
		$thumb = $this->where($where)->getField('thumb');
		if(!$thumb) return ;  //产品图片不存在时直接返回
		$path = "./Public/Uploads/big/$thumb";    //准备大图路径
		if(is_file($path)) unlink($path);         //删除大图文件
		$path = "./Public/Uploads/small/$thumb";  //准备小图路径
		if(is_file($path)) unlink($path);         //删除小图文件
		//会残留空目录，可以通过其它方式定期清理
	}
	
	//根据$where条件删除名为$p的焦点图文件
	public function delPicFile($where,$p){
	    //取出原图文件名
	    $pic = $this->where($where)->getField($p);
	    if(!$pic) return ;  //产品图片不存在时直接返回
	    $path = "./Public/Uploads/focus/$pic";    //准备路径
	    if(is_file($path)) unlink($path);         //删除文件
	}
	
	//上传预览图文件并生成缩略图
	//返回数组（flag=是否执行成功，error=失败时的错误信息，path=成功时的保存路径）
	public function uploadThumb($upfile){
		//准备上传目录
		$file['temp'] = './Public/Uploads/temp/';                       //准备临时目录
		file_exists($file['temp']) or mkdir($file['temp'],0777,true);   //自动创建临时目录
		//上传文件
		$Upload = new \Think\Upload(array(
			'exts' => array('jpg','jpeg','png','gif'), //允许的文件后缀
			'rootPath' => $file['temp'],               //文件保存路径
			'autoSub' => false,                        //不生成子目录
		));
		if(false===($rst = $Upload->uploadOne($_FILES[$upfile]))){
			//上传失败时，返回错误信息
			return array('flag'=>false,'error'=>$Upload->getError());
		}
		//准备生成缩略图
		$file['name'] = $rst['savename'];						  //文件名
		$file['save'] = date('Y-m/d/');                           //子目录
		$file['path1'] = './Public/Uploads/big/'.$file['save'];   //大图路径
		$file['path2'] = './Public/Uploads/small/'.$file['save']; //小图路径
		//创建保存目录
		file_exists($file['path1']) or mkdir($file['path1'],0777,true);
		file_exists($file['path2']) or mkdir($file['path2'],0777,true);
		//生成缩略图
		$Image = new \Think\Image();                //实例化图像处理类
		$Image->open($file['temp'].$file['name']);  //打开文件
		$Image->thumb(400,400,2)->save($file['path1'].$file['name']);//保存大图
		$Image->open($file['temp'].$file['name']);  //再次打开文件
		$Image->thumb(220,220,2)->save($file['path2'].$file['name']);//保存小图
		unlink($file['temp'].$file['name']);        //删除临时文件
		//返回文件路径
		return array('flag'=>true,'path'=>$file['save'].$file['name']);
	}
	//上传焦点图，压缩为690*400
	//返回数组（flag=是否执行成功，error=失败时的错误信息，path=成功时的保存路径）
	public function uploadPic($upfile){
	    //准备上传目录
	    $file['temp'] = './Public/Uploads/temp/';                       //准备临时目录
	    file_exists($file['temp']) or mkdir($file['temp'],0777,true);   //自动创建临时目录
	    //上传文件
	    $Upload = new \Think\Upload(array(
	        'exts' => array('jpg','jpeg','png','gif'), //允许的文件后缀
	        'rootPath' => $file['temp'],               //文件保存路径
	        'autoSub' => false,                        //不生成子目录
	    ));
	    if(false===($rst = $Upload->uploadOne($_FILES[$upfile]))){
	        //上传失败时，返回错误信息
	        return array('flag'=>false,'error'=>$Upload->getError());
	    }
	    //准备生成缩略图
	    $file['name'] = $rst['savename'];						  //文件名
	    $file['save'] = date('Y-m/d/');                           //子目录
	    $file['path1'] = './Public/Uploads/focus/'.$file['save'];   //焦点图路径
	    //创建保存目录
	    file_exists($file['path1']) or mkdir($file['path1'],0777,true);
	    //生成缩略图
	    $Image = new \Think\Image();                //实例化图像处理类
	    $Image->open($file['temp'].$file['name']);  //打开文件
	    $Image->thumb(690,400,2)->save($file['path1'].$file['name']);//保存大图
	    unlink($file['temp'].$file['name']);        //删除临时文件
	    //返回文件路径
	    return array('flag'=>true,'path'=>$file['save'].$file['name']);
	}
	
	//插入数据前置操作
	protected function _before_insert(&$data, $option){
		$data['recycle'] = 'no';                 //新产品是未删除的
		$data['add_time'] = date('Y-m-d H:i:s'); //新产品的添加时间
		//$data['price'] = (float)$data['price'];  //产品价格为浮点型
	}
	//更新数据前置操作
	protected function _before_update(&$data, $option){
		//$data['price'] = (float)$data['price'];  //产品价格为浮点型
	}
	
	public function getData($data){
    	if($data == "days"){
    	    return array('半日','1日','2日','3日','4日','5日','6日','7日','8日','9日','10日及以上');
    	}elseif ($data == "detail_property"){
    	    return array('亲子','养生','文化','美食','探险','水上','蜜月','摄影','购物','体检','体育','商务','休闲');
    	}elseif ($data == "visa_type"){
    	    return array('入台证','旅游签证','单次旅游签证','两次旅游签证','多次旅游签证','三年多次旅游签证','五年多次旅游签证','15天多次往返','30天多次往返','90天多次往返证','一个月单次入境','一个月多次入境','三个月单次入境','三个月多次入境','团体旅游签证','过境签证','打工度假签证','学生签证','商务签证','交流访问学者签证','探亲访友签证','其他');
    	}elseif ($data == "continent"){
    	    return array('亚洲','大洋洲','欧洲','中北美洲','加勒比','南美洲','中东','非洲','南极北极','海岛');
    	}elseif ($data == "ship_company"){
    	    return array('皇家加勒比国际游轮','阿玛河轮','公主邮轮','诺唯真游轮','丽星邮轮','歌诗达邮轮','地中海邮轮','嘉年华邮轮','精致邮轮');
    	}elseif ($data == "ship_route"){
    	    return array('澳洲','南太平洋','日韩','东南亚','中东非','欧洲','北美','内河');
    	}elseif ($data == "ship_name"){
    	    return array('黄金公主号','海德堡号','双子星号','处女星号','海洋钻石号','蓝宝石公主号','大西洋号','海洋量子号','皇冠公主号');
    	}elseif ($data == "ship_depart_city"){
    	    return array('成都','北京','天津','上海','香港','新加坡','深圳','广州','武汉');
    	}elseif ($data == "ship_depart_time"){
    	    return array('6月','7月','8月','9月','10月','11月','12月');
    	}
	
	
	}
	
} 