<?php

/**
 * 产品列表过滤项的URL生成
 * @param $type 生成的URL类型（cid, price, order)
 * @parma $data 相应的数据当前的值（为空表示清除该参数）
 * @return string 生成好的携带正确参数的URL
 */
function mkFilterURL($type, $data='') {
	$params = I('get.');
	unset($params['p']);  //先清除分页
	if($type=='cid') unset($params['price']); //切换分类时清除价格
	if($data){   //添加到参数
		$params[$type] = $data;
	}else{       //$data为空时清除参数
		unset($params[$type]);
	}
	return U('Index/find',$params);
}

//通过开源组件 HTML Purifier 过滤富文本
//该函数用于在后台编辑产品详情时过滤富文本，过滤后保存到数据库中。
function htmlpurifier($html){
	static $Purifier;
	if(empty($Purifier)){
		//载入第三方类库
		if(!Vendor('htmlpurifier.HTMLPurifier','','.standalone.php')){
			die('载入 HTMLPurifier 类库失败！');
		}
		$Purifier = new HTMLPurifier($html);
	}
	return $Purifier->purify($html);  
}

//---------分类处理函数

//获取一维数组分类列表
function category_list($data,&$rst,$pid=0,$level=0){
	foreach($data as $v){
		if($v['pid'] == $pid){
			$v['level'] = $level; //保存分类级别
			$rst[] = $v;          //保存符合条件的元素
			category_list($data,$rst,$v['id'],$level+1); //递归
		}
	}
}

//根据任意分类ID查找子孙分类ID
function category_child($data,&$rst,$id=0){
	foreach($data as $v){
		if($v['pid'] == $id){
			$rst[] = (int)$v['id'];
			category_child($data,$rst,$v['id']);
		}
	}
}

//按父子关系转换分类为多维数组
function category_tree($data,$pid=0,$level=0){
	$temp = $rst = array();
	foreach($data as $v) $temp[$v['id']] = $v;
	foreach($temp as $v){
		if(isset($temp[$v['pid']])){
			$temp[$v['pid']]['child'][] = &$temp[$v['id']];
		}else{
			$rst[] = &$temp[$v['id']];
		}
	}
    return $rst;
}

//查找分类的家谱
function category_family($data,$id){
	$rst = category_parent($data,$id);
	foreach(array_reverse($rst['pids']) as $v){
		foreach($data as $vv){
			($vv['pid']==$v) && $rst['parent'][$v][] = $vv;
		}
	}
	return $rst;
}

//根据任意分类ID查找父分类（包括自己）
function category_parent($data,$id=0){
	$rst = array('pcat'=>array(),'pids'=>array($id));
	for($i=0;$id && $i<10;++$i){  //最多10层，防止意外死循环
		foreach($data as $v){
			if($v['id']==$id){
				$rst['pcat'][] = $v;  //父分类
				$rst['pids'][] = $id = $v['pid']; //父分类ID
			}
		}
	}
	return $rst;
}
//发送email
function sendemail($mailcontent,$mailtitle){
    //初始化
    vendor('email.smtp');
    
    $smtpserver = C('smtpserver');//SMTP服务器
    $smtpserverport =25;//SMTP服务器端口
    $smtpusermail = C('smtpusermail');//SMTP服务器的用户邮箱
    $smtpemailto = C('smtpemailto');//发送给谁
    $smtpuser = C('smtpuser');//SMTP服务器的用户帐号
    $smtppass = C('smtppass');//SMTP服务器的用户密码
    $mailtype = "HTML";//邮件格式（HTML/TXT）,TXT为文本邮件
    //echo $mailcontent;
    $smtp = new smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
    $smtp->debug = false;//是否显示发送的调试信息
    $state = $smtp->sendmail($smtpemailto, $smtpusermail, $mailtitle, $mailcontent, $mailtype);
    if($state){
        return true;
    }else{
        return false;
    }
}