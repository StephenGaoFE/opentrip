<?php
namespace Home\Model;
use Think\Model;
class ProductModel extends Model {

	/**
	 * 产品列表
	 * @param array|int $cids 分类ID数组
	 * @param int $p 当前页码
	 * @return array 查询结果
	 */
	public function getList($cids=0,$p=0,$keyword="",$continent="",$country="",$city="",$days="",$detail_property="",$visa_type="",$place_of_issue="",$sent_visa_place="",$ship_route="",$ship_company="",$ship_name="",$ship_depart_city="",$ship_depart_time=""){
		//准备查询条件
		$field = 'category_id,id,name,price,thumb,info_fee,discount';
		$where = array('recycle' => 'no','on_sale'=>'yes');
		//查找分类ID数组
		if($cids > 0){
			$where['category_id'] = array('in',$cids);
		}
		//关键词模糊搜索
		if($keyword != ""){
		    $keywords = '%'.$keyword.'%';  //获取搜索关键字
		    $where['name'] = array('like',$keywords);  //用like条件搜索name字段
		}
		//具体筛选条件
		//线路
		if($continent != ""){
		   $where['continent'] = $continent;
		}
		if($country != ""){
		    $where['country'] = $country;
		}
		if($city != ""){
		    $where['city'] = $city;
		}
		if($days != ""){
		    $where['days'] = $days;
		}
		if($detail_property != ""){
		    $where['detail_property'] = $detail_property;
		}
		//签证
		if($visa_type != ""){
		    $where['visa_type'] = $visa_type;
		}
		if($place_of_issue != ""){
		    $place_of_issue = '%'.$place_of_issue.'%';  //获取搜索关键字
		    $where['place_of_issue'] = array('like',$place_of_issue);  //用like条件模糊查询，签发地不止一个
		}
		if($sent_visa_place != ""){
		    $where['sent_visa_place'] = $sent_visa_place;
		}
	    //邮轮
		if($ship_route != ""){
		    $where['ship_route'] = $ship_route;
		}
		if($ship_company != ""){
		    $where['ship_company'] = $ship_company;
		}
		if($ship_name != ""){
		    $where['ship_name'] = $ship_name;
		}
		if($ship_depart_city != ""){
		    $where['ship_depart_city'] = $ship_depart_city;
		}
		if($ship_depart_time != ""){
		    $where['ship_depart_time'] = $ship_depart_time;
		}
		
		$price_max = $this->where($where)->max('price');  //获取最大价格
		$recommend = $this->getRecommend($where);         //获取推荐产品
		//--------处理排序条件
		$order = 'id desc';
		$allow_order = array(
			'price-desc' => 'price desc',
			'price-asc' => 'price asc',
		);
		$input_order = I('get.order');
		if(isset($allow_order[$input_order])){
			$order = $allow_order[$input_order];
		}
		//--------处理价格条件
		$price = explode('-',I('get.price'));
		if(count($price)==2){
			$where['price'] = array(
				array('EGT',(int)$price[0]), //大于等于
				array('ELT',(int)$price[1]), //小于等于
			);
		}
		//准备分页查询
		$pagesize = C('USER_CONFIG.pagesize');        //每页显示产品数
		$count = $this->where($where)->count();       //获取符合条件的产品总数
		$Page = new \Think\Page($count,$pagesize);    //实例化分页类
		$this->_customPage($Page);                    //定制分页类样式
		//查询产品数据
		$results = $this->field($field)->where($where)->order($order)->page($p,$pagesize)->select();
		//处理价格
		if($results){
		    foreach ($results as &$res){
    		    //处理价格,最终产品价格 = 供应商报价 × (1 + 产品信息费% - 产品优惠额%)
    		    $res['price'] = (int)($res['price'] * (1 + $res['info_fee']*0.01 - $res['discount']*0.01));
		    }
		}
		//返回结果
		return array(
			'data' => $results,              //产品列表数组
			'price' => $this->getPriceDist($price_max), //计算产品价格
			'recommend' => $recommend,    //被推荐的产品
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
	
	//根据$where条件查询产品数据,负责前台的数据读取功能
	public function getProduct($where){
		//定义需要的字段
		$field = 'id,category_id,sn,name,price,market_price,child_price,deposit,stock,promotion,confirm,on_sale,recommend,continent,country,city,start,path,end,detail_property,highlight1,highlight2,highlight3,days,start_time,language,people,thumb,pic1,pic2,pic3,pic4,pic5,desc,travel_ref,reserve_notice,restrict,cost_desc,info,place_of_issue,visa_type,sent_visa_place,ship_depart_city,ship_route,ship_company,ship_name,ship_depart_time,plan1_name,plan1_price,plan2_name,plan2_price,plan3_name,plan3_price,plan4_name,plan4_price,plan5_name,plan5_price,plan6_name,plan6_price,info_fee,discount';
		$res = $this->field($field)->where($where)->find();
		if($res){
    		//处理价格,最终产品价格 = 供应商报价 × (1 + 产品信息费% - 产品优惠额%)
    		$res['price'] = (int)($res['price'] * (1 + $res['info_fee']*0.01 - $res['discount']*0.01));
    		$res['child_price'] = (int)($res['child_price'] * (1 + $res['child_price']*0.01 - $res['child_price']*0.01));
    		for($i=1; $i<=6; $i++){
    		    $res['plan'.$i.'_price'] = (int)($res['plan'.$i.'_price'] * (1 + $res['info_fee']*0.01 - $res['discount']*0.01));
    		}
		}
		return $res;
	}
	
	//取出推荐产品
	public function getRecommend($where){
		//查询被推荐的产品
		$where['recommend'] = 'yes';
		$field = 'id,name,price,thumb,info_fee,discount';
		$res = $this->field($field)->where($where)->limit(4)->select();
		if($res){
    		//处理价格
    		for($i=0; $i<=3; $i++){
    		  $res[$i]['price'] = (int)($res[$i]['price'] * (1 + $res[$i]['info_fee']*0.01 - $res[$i]['discount']*0.01));
    		}
		}
		return $res;
	}
	
	//动态计算价格
	//（max最大价格，sum分配个数）
	private function getPriceDist($max, $sum = 5) {
		if($max<=0) return false;
		$end = $size = ceil($max / $sum);
		$start = 0;
		$rst = array();
		for ($i = 0; $i < $sum; $i++) {
			$rst[] = "$start-$end";
			$start = $end + 1;
			$end += $size;
		}
		return $rst;
	}
}