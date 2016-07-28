<?php
namespace Home\Controller;
//前台主页控制器
class IndexController extends CommonController {
    //首页
	public function index(){
		//获得分类列表
		$data['category'] = D('Category')->getTree();
		//准备查询条件（推荐产品、已上架、不在回收站中）
		$where = array('recommend'=>'yes','on_sale'=>'yes','recycle'=>'no');
		//取出产品id，产品名，产品价格，产品图片
		$data['best'] = M('Product')->field('id,name,price,thumb,info_fee,discount')->where($where)->limit(4)->select();
	    //处理价格
		for($i=0; $i<=3; $i++){
		  $data['best'][$i]['price'] = (int)($data['best'][$i]['price'] * (1 + $data['best'][$i]['info_fee']*0.01 - $data['best'][$i]['discount']*0.01));
		}
		$data['best2'] = M('Product')->field('id,name,price,thumb')->where($where)->limit(4,3)->select();
		$data['best3'] = M('Product')->field('id,name,price,thumb')->where($where)->limit(7,3)->select();
		session('temp_cid',0);
		$data['temp_cid'] = session('temp_cid');//读取session中的cid
		$this->assign('title','opentrip旅游 - 全景看世界');
		$this->assign($data);
		$this->display();
	}
	//按分类筛选产品，关键词搜索产品
	public function find(){
		//获取参数
		$p = I('get.p/d',0);     //当前页码
		$cid = I('get.cid/d',-1); //获取分类ID
		$keyword = I('get.keyword',""); //搜索关键词
		
		$continent = I('get.continent',"");
		$country = I('get.country',"");
		$city = I('get.city',"");
		$days = I('get.days',"");
		$detail_property = I('get.detail_property',"");
		
		$visa_type = I('get.visa_type',"");
		$place_of_issue = I('get.place_of_issue',"");
		$sent_visa_place = I('get.sent_visa_place',"");
		
		$ship_route = I('get.ship_route',"");
		$ship_company = I('get.ship_company',"");
		$ship_name = I('get.ship_name',"");
		$ship_depart_city = I('ship_depart_city',"");
		$ship_depart_time = I('get.ship_depart_time',"");
		
		//如果cid在1-7之间，把cid保存到Session
		if($cid >=1 && $cid <=7){
		    session('temp_cid',$cid); 
		}
        
		//实例化模型
		$Product = D('Product');
		$Category = D('Category');
		//如果分类ID大于0，则取出所有子分类ID
		$cids = ($cid>0) ? $Category->getSubIds($cid) : $cid;
		//获取产品列表
	    $data['product'] = $Product->getList($cids,$p,$keyword,$continent,$country,$city,$days,$detail_property,$visa_type,$place_of_issue,$sent_visa_place,$ship_route,$ship_company,$ship_name,$ship_depart_city,$ship_depart_time);
		
		
		//防止空页被访问
		if(empty($data['product']['data']) && $p > 0){
			$this->redirect('Index/find',array('cid'=>$cid));
		}
		//准备筛选数据
		//$data['visa_dest'] = array("亚洲","欧洲","大洋洲","中北美洲","中东","非洲","加勒比","南美洲");
		
		$visa_dest = array(
		    '亚洲' => array(
		        '不丹' => array(),
		        '孟加拉国' => array('旅游签证','商务签证','探亲访友签证','落地签','其他'),
		        '文莱' => array('旅游签证','商务签证','探亲访友签证','落地签','其他'),
		        '柬埔寨' => array('旅游签证','落地签'),
		        '中国' => array('旅游签证','团体旅游签证'),
		        '香港' => array(),
		        '印度尼西亚' => array ('旅游签证','商务签证','落地签'),
		        '印度' => array('旅游签证','商务签证'),
		        '日本' => array('单次旅游签证','三年多次旅游签证','五年多次旅游签证','商务签证','探访亲友签证','团体旅游签证'),
		        '哈萨克斯坦' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '老挝' => array('旅游签证','商务签证','探亲访友签证','落地签','其他'),
		        '澳门' => array(),
		        '蒙古' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '马来西亚' => array('旅游签证','商务签证','免签'),
		        '马尔代夫' => array('落地签'),
		        '缅甸' => array('旅游签证','商务签证','落地签'),
		        '尼泊尔' => array('15天多次往返','30天多次往返','90天多次往返','落地签'),
		        '巴基斯坦' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '菲律宾' => array('旅游签证','商务签证','旅行团签证'),
		        '斯里兰卡' => array('旅游签证','商务签证','探亲访友签证','落地签','其他'),
		        '韩国' => array('单次签证','两次签证','多次签证','团体旅游签证'),
		        '新加坡' => array('旅游签证','商务签证'),
		        '台湾' => array('入台证'),
		        '东帝汶' => array('落地签'),
		        '土库曼斯坦' => array('落地签'),
		        '泰国' => array('旅游签证','商务签证','落地签'),
		        '乌兹别克斯坦' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '越南' => array('一个月单次入境','一个月多次入境','三个月单次入境','三个月多次入境','落地签'),
		    ),
		
		    '大洋洲' => array(
		        '澳大利亚' => array('旅游签证','商务签证','过境签证','打工度假签证'),
		        '斐济' => array(),
		        '纽埃岛' => array(),
		        '新西兰' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '帕劳' => array('落地签'),
		        '萨摩亚' => array('免签'),
		        '汤加' => array('落地签'),
		        '大溪地' => array(),
		        '瓦努阿图' => array('落地签'),
		    ),
		
		    '欧洲' => array(
		        '奥地利' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '比利时' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '丹麦' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '英格兰' => array('旅游签证','商务签证','探亲访友签证','两年多次往返签证','其他'),
		        '法国' => array('旅游签证','商务签证','探亲访友签证'),
		        '芬兰' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '希腊' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '德国' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '匈牙利' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '冰岛' => array(),
		        '意大利' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '爱尔兰' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '列支敦士登' => array(),
		        '卢森堡' => array(),
		        '马耳他' => array(),
		        '北爱尔兰' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '挪威' => array('新奥勒松','卑尔根','斯塔万格','奥斯陆'),
		        '波兰' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '葡萄牙' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '俄罗斯' => array('旅游签证','商务签证'),
		        '罗马尼亚' => array(),
		        '瑞典' => array(),
		        '苏格兰' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '瑞士' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '斯洛伐克' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '圣马力诺' => array(),
		        '西班牙' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '荷兰' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '土耳其' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '乌克兰' => array('旅游签证','商务签证','探亲访友签证','落地签','其他'),
		        '威尔士' => array('旅游签证','商务签证','探亲访友签证','其他'),
		    ),
		
		    '中北美洲' => array(
		        '加拿大' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '哥斯达黎加' => array(),
		        '洪都拉斯' => array(),
		        '墨西哥' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '巴拿马' => array(),
		        '美国' => array('商务/旅行签证 （B1/B2）','学生签证 （F,M）','交流访问学者签证 （J）','十年签证'),
		    ),
		
		    '加勒比' => array(
		        '古巴' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '多米尼加共和国' => array(),
		        '多米尼加岛' => array(),
		        '海地' => array('免签'),
		        '牙买加' => array(),
		        '巴哈马' => array('免签'),
		        '特立尼达和多巴哥' => array(),
		    ),
		
		    '南美洲' => array(
		        '阿根廷' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '玻利维亚' => array(),
		        '巴西' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '哥伦比亚' => array(),
		        '智利' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '厄瓜多尔' => array('免签'),
		        '圭亚那' => array('落地签'),
		        '秘鲁' => array(),
		        '圣赫勒拿（英属）' => array(),
		    ),
		
		    '中东' => array(
		        '巴林' => array('旅游签证','商务签证','探亲访友签证','落地签','其他'),
		        '伊朗' => array('旅游签证','商务签证','探亲访友签证','落地签','其他'),
		        '以色列' => array('旅游签证','商务签证','探亲访友签证','十年多次往返','其他'),
		        '约旦' => array('落地签'),
		        '黎巴嫩' => array('落地签'),
		        '卡塔尔' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '沙特阿拉伯' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '阿联酋' => array('旅游签证','商务签证','探亲访友签证','落地签','其他'),
		    ),
		
		    '非洲' => array(
		        '佛得角' => array('落地签'),
		        '科摩罗' => array('落地签'),
		        '科特迪瓦' => array('落地签'),
		        '埃及' => array('旅游签证','商务签证','探亲访友签证','落地签','其他'),
		        '几内亚比绍' => array('落地签'),
		        '肯尼亚' => array('旅游签证','商务签证','探亲访友签证','落地签','其他'),
		        '毛里塔尼亚' => array('落地签'),
		        '毛里求斯' => array('免签'),
		        '摩洛哥' => array('免签'),
		        '马达加斯加' => array('旅游签证','商务签证','探亲访友签证','落地签','其他'),
		        '马拉维' => array('落地签'),
		        '尼日利亚' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '纳米比亚' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '留尼旺（法属）' => array(),
		        '塞舌尔' => array('免签'),
		        '萨拉利昂' => array('落地签'),
		        '南非' => array('旅游签证','商务签证','探亲访友签证','其他'),
		        '多哥' => array('落地签'),
		        '坦桑尼亚' => array('落地签'),
		        '乌干达' => array('落地签'),
		        '赞比亚' => array('旅游签证','商务签证','探亲访友签证','其他'),
		    ),
		
		    '南极北极' => array(
		    ),
		
		    '海岛' => array(
		        '巴厘岛' => array(),
		        '斐济' => array('落地签'),
		        '夏威夷' => array(),
		        '马尔代夫' => array('落地签'),
		        '毛里求斯' => array('免签'),
		        '帕劳' => array('落地签'),
		        '留尼旺（法属）' => array(),
		        '塞舌尔' => array('免签'),
		        '大溪地' => array(),
		        '瓦努阿图' => array('落地签'),
		    ),
		);
		
		$route_dest = array(
		    '亚洲' => array(
		        '不丹' => array(),
		        '孟加拉国' => array(),
		        '文莱' => array(),
		        '柬埔寨' => array('暹粒','金边','西哈努克市（西港）','贡布','白马','戈公','吴哥','高龙岛'),
		        '中国' => array('西藏','四川','云南','陕西','新疆','海南','北京','上海','香港','澳门'),
		        '台湾' => array('台北','高雄','垦丁','台中','花莲','桃园','阿里山','新竹','台南','日月潭','清境','苗栗','宜兰','台东'),
		        '印度尼西亚' => array ('巴厘岛'),
		        '印度' => array(),
		        '日本' => array('东京','大阪','京都','札幌','北海道','箱根','神户','富士山','名古屋','冲绳','小樽','横滨','千叶'),
		        '哈萨克斯坦' => array('阿拉木图','阿斯塔纳'),
		        '老挝' => array(),
		        '蒙古' => array(),
		        '马来西亚' => array('吉隆坡','新山','沙巴','兰卡威','槟城'),
		        '马尔代夫' => array(),
		        '缅甸' => array(),
		        '尼泊尔' => array('加德满都'),
		        '菲律宾' => array(),
		        '斯里兰卡' => array('科伦坡'),
		        '韩国' => array('首尔','济州','京畿道','釜山','庆州','江原道'),
		        '新加坡' => array('新加坡','民丹岛'),
		        '泰国' => array('普吉岛','清迈','曼谷','甲米','芭提雅','苏梅岛'),
		        '越南' => array(),
		    ),
		
		    '欧洲' => array(
		        '奥地利' => array(),
		        '比利时' => array(),
		        '捷克' => array(),
		        '克罗地亚' => array('萨格勒布'),
		        '塞浦路斯' => array(),
		        '丹麦' => array('哥本哈根'),
		        '英格兰' => array('伦敦','爱丁堡','温莎','巴斯','尼斯湖','剑桥','利物浦','牛津','贝尔法斯','特坎特伯雷','布莱顿湖区','卡迪	夫','格拉斯哥','因弗内斯','阿普克罗斯半岛','科茨沃尔德','沃里克'),
		        '法国' => array('巴黎','阿维尼翁','尼斯','戛纳','艾克斯','波尔多','里昂','格拉斯','圣－米歇尔山','马赛','特鲁瓦昂','布瓦斯','莱塞佩斯','里尔','鲁贝','卡昂'),
		        '芬兰' => array('赫尔辛基'),
		        '希腊' => array('雅典','扎金索斯','圣托里尼'),
		        '德国' => array('慕尼黑','柏林','法兰克福','科隆'),
		        '匈牙利' => array(),
		        '冰岛' => array(),
		        '意大利' => array('罗马','威尼斯','佛罗伦萨','米兰','锡耶纳','比萨','卢卡','索伦托','蒙特卡蒂尼'),
		        '爱尔兰' => array(),
		        '列支敦士登' => array(),
		        '卢森堡' => array(),
		        '马耳他' => array(),
		        '摩纳哥' => array('摩纳哥'),
		        '挪威' => array('新奥勒松','卑尔根','斯塔万格','奥斯陆'),
		        '波兰' => array(),
		        '葡萄牙' => array('里斯本'),
		        '俄罗斯' => array('莫斯科','圣彼得堡','喀山','索契','符拉迪沃斯托克'),
		        '罗马尼亚' => array(),
		        '瑞典' => array('斯德哥尔摩'),
		        '苏格兰' => array(),
		        '瑞士' => array('伯尔尼','因特拉肯','苏黎世'),
		        '圣马力诺' => array(),
		        '西班牙' => array('巴塞罗那','马德里','格拉纳达','塞维利亚','塞哥维亚','瓦伦西亚','托莱多','龙达','潘普洛纳','圣塞巴斯蒂安','萨拉曼卡','科尔多瓦','萨拉戈萨','毕尔巴鄂','赫雷斯','马拉加','加的斯','阿维拉'),
		        '荷兰' => array('阿姆斯特丹'),
		        '土耳其' => array('卡帕多奇亚','伊斯坦布尔','库萨达斯','阿拉尼亚','以弗所','伊兹密尔'),
		        '乌克兰' => array(),
		        '威尔士' => array(),
		    ),
		
		    '中东' => array(
		        '伊朗' => array(),
		        '以色列' => array('特拉维夫','伯利恒','杰里科','马萨达'),
		        '约旦' => array('安曼','亚喀巴','死海'),
		        '阿曼' => array(),
		        '阿联酋' => array('迪拜','阿布扎比'),
		    ),
		
		    '非洲' => array(
		        '埃塞俄比亚' => array(),
		        '埃及' => array(),
		        '肯尼亚' => array(),
		        '毛里求斯' => array(),
		        '摩洛哥' => array(),
		        '马达加斯加' => array(),
		        '纳米比亚' => array(),
		        '留尼旺（法属）' => array(),
		        '塞舌尔' => array(),
		        '突尼斯' => array(),
		        '坦桑尼亚' => array(),
		        '津巴布韦' => array(),
		        '赞比亚' => array(),
		    ),
		
		    '南极北极' => array(
		        '南极洲' => array(),
		        '北极' => array(),
		    ),
		
		    '大洋洲' => array(
		        '澳大利亚' => array('悉尼','墨尔本','黄金海岸','布里斯班','大堡礁','凯恩斯','圣灵群岛','拜伦湾','艾尔利海滩','阿德莱德','珀斯','达尔文','堪培拉','爱丽丝泉','艾尔斯岩','昆士兰洲其它地区','塔斯马尼亚'),
		        '斐济' => array(),
		        '新西兰' => array('皇后镇','奥克兰','基督城','怀托摩','惠灵顿','罗托鲁瓦','米尔福德峡湾','蒂阿瑙','西海岸冰川','瓦纳卡','陶波','凯库拉','玛塔玛塔','蒂卡波','但尼丁','皮克顿','岛屿湾','北岛其他地区','汉默温泉','南岛其他地区哈维诺'),
		        '帕劳' => array(),
		        '大溪地' => array(),
		        '瓦努阿图' => array(),
		    ),
		
		    '中北美洲' => array(
		        '加拿大' => array('多伦多','尼亚加拉瀑布','维多利亚','温哥华','班芙','怀特霍斯'),
		        '哥斯达黎加' => array(),
		        '墨西哥' => array('墨西哥城','梅里达'),
		        '巴拿马' => array(),
		        '美国' => array('洛杉矶','纽约','拉斯维加斯','圣地亚哥','旧金山','亚利桑那','奥兰多','夏威夷','芝加哥','波士顿','西雅图','加利福尼亚','佛罗里达','欧胡岛','檀香山','华盛顿特区','茂宜岛','迈阿密','新泽西','大岛','费城','巴尔的摩','匹兹堡','菲尼克斯','底特律','坦帕','阿拉斯加','丹佛','犹他'),
		    ),
		
		    '加勒比' => array(
		        '古巴' => array(),
		        '牙买加' => array(),
		        '巴哈马' => array(),
		    ),
		
		    '南美洲' => array(
		        '阿根廷' => array(),
		        '玻利维亚' => array(),
		        '巴西' => array('里约热内卢','伊瓜苏'),
		        '智利' => array(),
		        '厄瓜多尔' => array(),
		        '秘鲁' => array('利马','库斯科'),
		    ),
		
		    '海岛' => array(
		        '巴厘岛' => array(),
		        '斐济' => array(),
		        '夏威夷' => array(),
		        '马尔代夫' => array(),
		        '毛里求斯' => array(),
		        '帕劳' => array(),
		        '留尼旺（法属）' => array(),
		        '塞舌尔' => array(),
		        '大溪地' => array(),
		        '瓦努阿图' => array(),
		    ),
		);		
		$data['days'] = array('半日','1日','2日','3日','4日','5日','6日','7日','8日','9日','10日及以上');
		$data['detail_property'] = array('亲子','养生','文化','美食','探险','水上','蜜月','摄影','购物','体检','体育','商务','休闲');
		$data['visa_type'] = array('入台证','旅游签证','单次旅游签证','两次旅游签证','多次旅游签证','三年多次旅游签证','五年多次旅游签证','15天多次往返','30天多次往返','90天多次往返证','一个月单次入境','一个月多次入境','三个月单次入境','三个月多次入境','团体旅游签证','过境签证','打工度假签证','学生签证','商务签证','交流访问学者签证','探亲访友签证','其他');
		$data['place_of_issue'] = array('安徽','北京','福建','甘肃','广东', '广西','贵州','海南','河北','河南','黑龙江','湖北', '湖南','吉林','江苏','江西','辽宁','内蒙古','宁夏' ,'青海' ,'山东','山西' ,'陕西','上海', '四川','天津','西藏','新疆','云南' ,'浙江','重庆' ,'其他国家或地区');
		$data['sent_visa_place'] = array('上海','北京','广州','武汉','成都','沈阳','香港');
		//邮轮
		$data['ship_route'] = array('澳洲','南太平洋','日韩','东南亚','中东非','欧洲','北美','内河');
		$data['ship_company'] = array('皇家加勒比国际游轮','阿玛河轮','公主邮轮','诺唯真游轮','丽星邮轮','歌诗达邮轮','地中海邮轮','嘉年华邮轮','精致邮轮');
		$data['ship_name'] = array('黄金公主号','海德堡号','双子星号','处女星号','海洋钻石号','蓝宝石公主号','大西洋号','海洋量子号','皇冠公主号');
		$data['ship_depart_city'] = array('成都','北京','天津','上海','香港','新加坡','深圳','广州','武汉');
		$data['ship_depart_time'] = array('6月','7月','8月','9月','10月','11月','12月');
		
		$data['visa_dest'] = $visa_dest;
		$data['route_dest'] = $route_dest;
		
		//查询分类列表
		$data['category'] = $Category->getFamily($cid);
		$data['cid'] = $cid;
		$data['p'] = $p;
		$data['temp_cid'] = session('temp_cid');//读取session中的cid
		$this->assign('title','产品列表 - opentrip');
		$this->assign($data);
		$this->display();
	}
	
	//产品详情页
	public function product(){
		$id = I('get.id/d',0); //产品ID
		$Product = D('Product');
		$Category = D('Category');
		//查找当前产品
		$data['product'] = $Product->getProduct(array('recycle' => 'no','on_sale'=>'yes','id'=>$id));
		if(empty($data['product'])){
			$this->error('您访问的产品不存在，已下架或删除！');
		}
		
		//查找推荐产品
		$cids = $Category->getSubIds($data['product']['category_id']);
		$where = array('recycle' => 'no','on_sale'=>'yes');
		$where['category_id'] = array('in',$cids);
		$data['recommend'] = $Product->getRecommend($where);
		
		//读取session中的cid
		$data['temp_cid'] = session('temp_cid');
		
		//查找分类导航
		$data['path'] = $Category->getPath($data['product']['category_id']);
		$this->assign('title',$data['product']['name'].' - opentrip');
		$this->assign($data);
		$this->display();
	}
	//个人定制页面
	public function customize(){ 
	    $sn = I('get.sn',"");     //获取sn
	    if(IS_POST){
	        $array = $_POST;
	        
	        $localtime = date('y-m-d H:i:s',time());
	        
	        $mailtitle = "私人定制订单";//邮件主题
            $mailcontent = "<h1>私人定制</h1>";//邮件内容
            $mailcontent .= "<h3>产品编号：".$array['sn']."</h3>";
            $mailcontent .= "<h3>创建时间：".$localtime."</h3>";
            $mailcontent .= "<h3>用户名：".$array['username']."</h3>";
            $mailcontent .= "<h3>联系电话：".$array['phone']."</h3>";
            $mailcontent .= "<h3>邮箱：".$array['email']."</h3>";
            $mailcontent .= "<h3>想去目的地：".$array['where']."</h3>";
            $mailcontent .= "<h3>和谁去：".$array['relation']."</h3>";
            $mailcontent .= "<h3>单人预算：".$array['budget']."</h3>";
            $mailcontent .= "<h3>出行时间：".$array['departtime']."</h3>";
            $mailcontent .= "<h3>出行天数：".$array['departday']."</h3>";
            $mailcontent .= "<h3>出行人数：".$array['departpeople']."</h3>";
            $mailcontent .= "<h3>其他需求：".$array['rests']."</h3>";
            $mailcontent .= "<br><h3>opentrip版权所有</h3>";
	        	        	    
	        $flag = sendemail($mailcontent, $mailtitle);
	        
	        if(true === $flag){
	            $data['res'] = 1;
	            $this->ajaxReturn($data);
	        }else{
	            $data['res'] = 0;
	            $this->ajaxReturn($data);
	        }
	    }
	    $data['sn'] = $sn;
	    $this->assign($data);
	    $this->assign('title','私人定制 - opentrip');
	    $this->display();
	}
	//个人定制页面
	public function firm(){
	    if(IS_POST){
	        $array = $_POST;
	         
	        $localtime = date('y-m-d H:i:s',time());
	         
	        $mailtitle = "企业定制订单";//邮件主题
            $mailcontent = "<h1>企业定制</h1>";//邮件内容
            $mailcontent .= "<h3>创建时间：".$localtime."</h3>";
            $mailcontent .= "<h3>用户名：".$array['username']."</h3>";
            $mailcontent .= "<h3>联系电话：".$array['phone']."</h3>";
            $mailcontent .= "<h3>邮箱：".$array['email']."</h3>";
            $mailcontent .= "<h3>想去目的地：".$array['where']."</h3>";
            $mailcontent .= "<h3>出游类型：".$array['relation']."</h3>";
            $mailcontent .= "<h3>单人预算：".$array['budget']."</h3>";
            $mailcontent .= "<h3>出行时间：".$array['departtime']."</h3>";
            $mailcontent .= "<h3>出行天数：".$array['departday']."</h3>";
            $mailcontent .= "<h3>出行人数：".$array['departpeople']."</h3>";
            $mailcontent .= "<h3>公司名称：".$array['company']."</h3>";
            $mailcontent .= "<h3>其他需求：".$array['rests']."</h3>";
            $mailcontent .= "<br><h3>opentrip版权所有</h3>";
	         
	        $flag = sendemail($mailcontent, $mailtitle);
	        
	        if(true === $flag){
	            $data = 1;
	            $this->ajaxReturn($data);
	        }else{
	            $data['res'] = 0;
	            $this->ajaxReturn($data);
	        }
	    }
	    $this->assign('title','企业定制 - opentrip');
	    $this->display();
	}
	
    //by LiHang
	public function introduce() {
	    $tid = I('get.tid/d',-1);
	    session('temp_tid',$tid);
	    $data['temp_tid'] = session('temp_tid');
	    //echo 'temp_tid';
	    $this->assign('title','网站声明 - opentrip');
	    $this->assign($data);
	    $this->display();
	}
	
	//生成验证码
	public function getVerify() {
	    $Verify = new \Think\Verify();
	    $Verify->entry();
	}
	//检查验证码
	public function checkVerify() {
	    if(IS_AJAX && IS_POST){
	        $code = I('post.code','');
	        $id = '';
    	    $Verify = new \Think\Verify();
    	    $flag = $Verify->check($code, $id);
    	    if(true === $flag){
    	        $data['status'] = 1;
    	        $this->ajaxReturn($data);
    	    }else{
    	        $data['status'] = 0;
    	        $this->ajaxReturn($data);
    	    }
	    }else{
	        echo "error";
	    }
	}
	
	
}

