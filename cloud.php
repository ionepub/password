<?php
session_start();

header("Content-type: text/html; charset=utf-8");

include_once 'lib/functions.php';

if(!isAjax()){
	exit('Access denied');
}

$act = isset($_REQUEST['act']) && $_REQUEST['act']!="" ? trim($_REQUEST['act']) : "";

$act_allow_list = array("checkLogin", "login", "logout", "reg", "editPwd", "checkLoginOnEmail", "editEmail", "add", "edit", "remove", "list", "checkLoginOnDetail", "getPwd");

if(!in_array($act, $act_allow_list)){
	exit('Access denied');
}

$base_pass_key = "gwNgoX4cUvbAppVso28C1afA5v1ZVI4m"; //平台密钥

/**
 * check login
 */
if($act == "checkLogin"){
	if(checkLogin()){
		//success
		output(1, "signed in");
	}else{
		// not login
		output(2, "not signed in");
	}
}

/**
 * check login and return email
 */
elseif($act == "checkLoginOnEmail"){
	if(checkLogin()){
		//success
		include_once 'lib/BmobUser.class.php';
		try{
			$bmobUser = new BmobUser();
			$res = $bmobUser->get($_SESSION['userid']);
			output(1, $res->email);
		}catch(Exception $e){
			//user not exist, logout
			unset($_SESSION['userid']);
			unset($_SESSION['sessionToken']);
			unset($_SESSION['username']);
			unset($_SESSION['email']);

			$e = (string)$e;
			output(2, "not signed in");
		}
	}else{
		// not login
		output(2, "not signed in");
	}
}

/**
 * check login and return detail
 */
elseif ($act == 'checkLoginOnDetail') {
	if(checkLogin()){
		//success
		include_once 'lib/BmobObject.class.php';

		$id = isset($_GET['id']) && trim($_GET['id'])!="" ? trim($_GET['id']) : "";
		if(!$id || $id == "#"){
			output(100, "未找到记录");
		}
		$id = explode("#", $id);
		$id = $id[1];

		try{
			$bmobObj = new BmobObject('pwd');
			$res = $bmobObj->get($id);
			if($_SESSION['userid'] != $res->uid){
				output(100, "未找到记录");
			}
			$result = array(
				'id' => $res->objectId,
				'title' => $res->title,
				'note' => $res->description,
				'password' => $res->password,
			);
			output($result);
		}catch(Exception $e){
			//obj not exist
			$e = (string)$e;
			output(100, "未找到记录");
		}
	}else{
		// not login
		output(2, "not signed in");
	}
}

/**
 * do login
 */
elseif ($act == 'login') {
	include_once 'lib/BmobUser.class.php';

	$username = isset($_POST['username']) ? trim($_POST['username']) : "";
	$password = isset($_POST['password']) ? trim($_POST['password']) : "";

	if($username == "" || $password == ""){
		output(100, "账号密码不能为空");
	}

	try{
		$bmobUser = new BmobUser();
		$res = $bmobUser->login($username, $password);
		
		$_SESSION['userid'] = $res->objectId;
		$_SESSION['sessionToken'] = $res->sessionToken;

		output(array());
	}catch(Exception $e){
		$e = (string)$e;
		output(100, errorDesc($e));
	}
}

/**
 * 注册
 */
elseif ($act == 'reg') {
	include_once 'lib/BmobUser.class.php';

	$username = isset($_POST['username']) ? trim($_POST['username']) : "";
	$password = isset($_POST['password']) ? trim($_POST['password']) : "";
	$repass = isset($_POST['repass']) ? trim($_POST['repass']) : "";
	$email = isset($_POST['email']) ? trim($_POST['email']) : "";

	if($username == "" || $password == "" || $repass == ""){
		output(100, "账号密码不能为空");
	}

	if($password !== $repass){
		output(100, "两次输入的密码不一致");
	}

	try{
		$bmobUser = new BmobUser();
		$data = array("username"=>$username, "password"=>$password);
		if($email != ""){
			$data['email'] = $email;
		}
		$res = $bmobUser->register($data); 
		
		$_SESSION['userid'] = $res->objectId;
		$_SESSION['sessionToken'] = $res->sessionToken;

		output(array());
	}catch(Exception $e){
		$e = (string)$e;
		output(100, errorDesc($e));
	}
}

/**
 * logout
 */
elseif ($act == 'logout') {
	unset($_SESSION['userid']);
	unset($_SESSION['sessionToken']);
	unset($_SESSION['username']);
	unset($_SESSION['email']);
	output(array());
}

/**
 * 修改密码
 */
elseif ($act == 'editPwd') {
	if(!checkLogin()){
		output(100, "请先登录");
	}

	include_once 'lib/BmobUser.class.php';

	$password = isset($_POST['password']) ? trim($_POST['password']) : "";
	$newpass = isset($_POST['newpass']) ? trim($_POST['newpass']) : "";
	$repass = isset($_POST['repass']) ? trim($_POST['repass']) : "";

	if($newpass == "" || $password == "" || $repass == ""){
		output(100, "账号密码不能为空");
	}

	if($newpass !== $repass){
		output(100, "两次输入的密码不一致");
	}

	try{
		$bmobUser = new BmobUser();

		$res = $bmobUser->updateUserPassword($_SESSION['userid'], $_SESSION['sessionToken'] , $password, $newpass); //用户输入一次旧密码做一次校验，旧密码正确才可以修改为新密码
		
		output(array());
		
	}catch(Exception $e){
		$e = (string)$e;
		output(100, errorDesc($e));
	}
}

/**
 * 修改邮箱
 */
elseif ($act == 'editEmail') {
	if(!checkLogin()){
		output(100, "请先登录");
	}

	include_once 'lib/BmobUser.class.php';

	$password = isset($_POST['password']) ? trim($_POST['password']) : "";
	$newemail = isset($_POST['newemail']) ? trim($_POST['newemail']) : "";

	if($newemail == "" || $password == ""){
		output(100, "新邮箱和密码不能为空");
	}

	try{
		$bmobUser = new BmobUser();

		$res = $bmobUser->get($_SESSION['userid']);

		$username = $res->username;

		$res = $bmobUser->login($username, $password); //验证密码

		$data = array(
			"email" => $newemail
		);
		$res = $bmobUser->update($_SESSION['userid'], $_SESSION['sessionToken'] , $data);
		
		output(array());
		
	}catch(Exception $e){
		$e = (string)$e;
		output(100, errorDesc($e));
	}
}

/**
 * 添加密码记录
 */
elseif ($act == 'add') {
	if(!checkLogin()){
		output(100, "请先登录");
	}
	include_once 'lib/BmobObject.class.php';
	include_once 'lib/Crypt.class.php';

	$title = isset($_POST['title']) ? trim($_POST['title']) : "";
	$password = isset($_POST['password']) ? trim($_POST['password']) : "";
	$pwdkey = isset($_POST['pwdkey']) ? trim($_POST['pwdkey']) : "";
	$note = isset($_POST['note']) ? trim($_POST['note']) : "";

	if($title == "" || $password == "" || $pwdkey == ""){
		output(100, "数据填写不完整");
	}

	if(mb_strlen($password, "utf-8") < 4){
		output(100, "密码太短了");
	}

	if(mb_strlen($pwdkey, "utf-8") < 6){
		output(100, "密钥太简单了");
	}

	$new_pwdkey = md5(md5($pwdkey) . $base_pass_key);

	$new_password = Crypt::encrypt($password, $new_pwdkey);

	try {
		$bmobObj = new BmobObject("pwd");

		$data = array(
			'uid' => $_SESSION['userid'],
			'password' => $new_password,
			'title' => $title,
			'description' => $note,
		);

		$res=$bmobObj->create($data); //添加对象

		output(array());
		
	} catch (Exception $e) {
		$e = (string)$e;
		output(100, errorDesc($e));
	}
}

/**
 * 用密钥获取密码明文
 */
elseif ($act == 'getPwd') {
	if(!checkLogin()){
		output(100, "请先登录");
	}
	include_once 'lib/BmobObject.class.php';
	include_once 'lib/Crypt.class.php';

	$id = isset($_POST['id']) ? trim($_POST['id']) : "";
	$pwdkey = isset($_POST['pwdkey']) ? trim($_POST['pwdkey']) : "";

	if($id == "" || $pwdkey == ""){
		output(100, "密钥错误或未找到记录");
	}

	if(mb_strlen($pwdkey, "utf-8") < 6){
		output(100, "密钥错误");
	}

	$new_pwdkey = md5(md5($pwdkey) . $base_pass_key);

	try {
		$bmobObj = new BmobObject("pwd");

		$res = $bmobObj->get($id);

		if($res->uid != $_SESSION['userid']){
			output(100, "记录不存在"); //不是当前用户记录
		}

		$password = $res->password; //密文

		$new_password = Crypt::decrypt($password, $new_pwdkey);

		if($new_password == ""){
			output(100, "密钥错误");
		}

		output(array('password'=>$new_password));

	} catch (Exception $e) {
		$e = (string)$e;
		output(100, errorDesc($e));
	}
}

/**
 * edit
 */
elseif ($act == 'edit') {
	if(!checkLogin()){
		output(100, "请先登录");
	}
	include_once 'lib/BmobObject.class.php';
	include_once 'lib/Crypt.class.php';

	$title = isset($_POST['title']) ? trim($_POST['title']) : "";
	$password = isset($_POST['password']) ? trim($_POST['password']) : "";
	$pwdkey = isset($_POST['pwdkey']) ? trim($_POST['pwdkey']) : "";
	$note = isset($_POST['note']) ? trim($_POST['note']) : "";
	$id = isset($_POST['id']) ? trim($_POST['id']) : "";

	if($title == "" || $password == "" || $pwdkey == "" || $id == ""){
		output(100, "数据填写不完整");
	}

	if(mb_strlen($password, "utf-8") < 4){
		output(100, "密码太短了");
	}

	if(mb_strlen($pwdkey, "utf-8") < 6){
		output(100, "密钥太简单了");
	}

	$new_pwdkey = md5(md5($pwdkey) . $base_pass_key);

	$new_password = Crypt::encrypt($password, $new_pwdkey);

	try {
		$bmobObj = new BmobObject("pwd");

		$res = $bmobObj->get($id);

		if($_SESSION['userid'] != $res->uid){
			output(100, "记录不存在");
		}

		$data = array(
			'password' => $new_password,
			'title' => $title,
			'description' => $note,
		);

		$res=$bmobObj->update($id, $data);

		output(array());
		
	} catch (Exception $e) {
		$e = (string)$e;
		output(100, errorDesc($e));
	}
}

/**
 * 密码记录列表
 */
elseif ($act == 'list') {
	if(!checkLogin()){
		output(100, "请先登录");
	}
	include_once 'lib/BmobObject.class.php';

	$page = isset($_GET['page']) && intval($_GET['page']) > 0 ? intval($_GET['page']) : 1;
	$pagesize = 100;
	$start = ($page - 1) * $pagesize;

	try {
		$bmobObj = new BmobObject("pwd");

		$res=$bmobObj->get("",array('where={"uid":"'.$_SESSION['userid'].'"}','limit='.$pagesize, 'skip='.$start, 'order=-createdAt'));

		$result = $res->results;
		
		if(!empty($result)){
			$list = transtolist($result);
			output(array('list'=>$list));
		}else{
			output(array('list'=>'')); //没有更多数据
		}

	} catch (Exception $e) {
		$e = (string)$e;
		output(100, errorDesc($e));
	}
}

/**
 * remove
 */
elseif ($act == 'remove') {
	if(!checkLogin()){
		output(100, "请先登录");
	}
	include_once 'lib/BmobObject.class.php';

	$id = isset($_POST['id']) && trim($_POST['id'])!="" ? trim($_POST['id']) : "";
	if(!$id){
		output(100, "未找到记录");
	}

	try {
		$bmobObj = new BmobObject("pwd");

		$res = $bmobObj->get($id);

		$userid = $res->uid;
		if($userid != $_SESSION['userid']){
			output(100, "未找到记录"); //非本人账号
		}

		$res = $bmobObj->delete($id);
		
		output(array());

	} catch (Exception $e) {
		$e = (string)$e;
		$errorDesc = errorDesc($e);
		if($errorDesc == "账号或密码错误"){
			$errorDesc = "删除失败"; //未找到记录
		}
		output(100, $errorDesc);
	}
}

?>