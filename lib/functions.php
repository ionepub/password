<?php
/**
 * =====================
 * 公共函数
 * =====================
 */

/**
 * 检查是否登录
 */
function checkLogin(){
	if(!isset($_SESSION['userid']) || $_SESSION['userid']==""){
		return false;
	}
	return true;
}

/**
 * 跳转
 */
function redirect($url=""){
	if($url == ""){
		return false;
	}else{
		header("Location: ".$url);exit;
		return true;
	}
}

/**
 * 是否ajax请求
 */
function isAjax(){
	return 'XMLHttpRequest' ==  $_SERVER['HTTP_X_REQUESTED_WITH'];
}

/**
 * 格式化输出
 */
function output($code=0, $message=""){
	$out = array();
	if(is_array($code)){
		//成功
		$out['code'] = 0;
		$out['message'] = "success";
		$out['data'] = $code;
	}else{
		//failed
		$out['code'] = $code;
		$out['message'] = $message;
	}
	echo json_encode($out);
	exit;
}

/**
 * 错误信息转换
 */
function errorDesc($str=""){
	if($str == ""){
		return "";
	}
	if(strpos($str, "[101]") !== false){
		return "账号或密码错误";
	}
	if(strpos($str, "[108]") !== false || strpos($str, "[109]") !== false){
		return "账号和密码不能为空";
	}
	if(strpos($str, "[301]") !== false){
		return "邮箱格式错误";
	}
	if(strpos($str, "[202]") !== false){
		return "账号已被注册";
	}
	if(strpos($str, "[203]") !== false){
		return "邮箱已被注册";
	}
	if(strpos($str, "[210]") !== false){
		return "原密码错误";
	}
	$str = str_replace("BmobException: ", "", $str);
	$str = str_replace("bmob.cn", "", $str);
	return $str;
}

/**
 * 将数据数组转换成列表
 */
function transtolist($list=array()){
	if(empty($list) || !is_array($list)){
		return "";
	}
	$re = "";
	foreach ($list as $item) {
		$updatedAt = $item->updatedAt;
		if(time() - strtotime($updatedAt) > 86400){
			$updateTime = date("Y-m-d", strtotime($updatedAt));
		}else{
			//今天更新
			$updateTime = date("H:i", strtotime($updatedAt));
		}
		$re .= '<li class="mui-table-view-cell" data-id="'.$item->objectId.'">
		        		<div class="mui-slider-right mui-disabled">
					<a class="mui-btn mui-btn-red" data-id="'.$item->objectId.'">删除</a>
				</div>
		           	<div class="mui-table mui-slider-handle">
		           		<div class="mui-table-cell mui-col-xs-10">
		           			<h4 class="mui-ellipsis">'.$item->title.'</h4>
		           			<p class="mui-h6 mui-ellipsis-2">'.$item->description.'</p>
		           		</div>
		           		<div class="mui-table-cell mui-col-xs-2 mui-text-right">
		           			<span class="mui-h5">'.$updateTime.'</span>
		           		</div>
		           	</div>
		        	</li>';
	}
	return $re;
}