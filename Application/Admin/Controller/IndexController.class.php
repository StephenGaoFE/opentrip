<?php
namespace Admin\Controller;
//后台首页
class IndexController extends CommonController{

	//后台首页，显示服务器基本信息
	public function index(){
		$serverInfo = array(
			//获取服务器信息（操作系统、Apache版本、PHP版本）
			'server_version' => $_SERVER['SERVER_SOFTWARE'],
			//获取MySQL版本信息
			'mysql_version' => $this->getMySQLVer(),
			//获取服务器时间
			'server_time' => date('Y-m-d H:i:s', time()),
			//上传文件大小限制
			'max_upload' => ini_get('file_uploads') ? ini_get('upload_max_filesize') : '已禁用', 
			//脚本最大执行时间
            'max_ex_time' => ini_get('max_execution_time').'秒', 
		);
		//视图
		$data['priv'] = session('userinfo.priv');
		$this->assign($data);
		$this->assign('serverInfo',$serverInfo);
		$this->display();
	}
	
	//获取MySQL版本
	private function getMySQLVer(){
		$rst = M()->query('select version() as ver');
		return isset($rst[0]['ver']) ? $rst[0]['ver'] : '未知';
	}
}
