<?php
require_once 'BaseController.php';

/**
 * 
 *员工管理控制器
 *该控制器专门用于响应登陆和退出请求
 */
class EmployeeController extends BaseController
{
    /**
     * 员工信息查询
     */
    public function employeeAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
	    	$db=$userModel->getAdapter();
	    	$plandata=$userModel->userplanmsg($_SESSION['loginuser']['crm_EmployID']);
	        //今日拜访计划数
	        $this->view->todyplan=$plandata;
			$statime=$this->getRequest()->getParam("statime","");//开始时间
			$endtime=$this->getRequest()->getParam("endtime","");//结束时间
			$name=$this->getRequest()->getParam("name","");//客户姓名
			$employeedata=parent::parameteraudit("",array($statime,$endtime,$name),3);
			$statime=$employeedata[0];
			$endtime=$employeedata[1];
			$name=$employeedata[2];
			if (!empty($name)) {
				$urlpara="&name=".$name;
			}		
			//获取员工信息总数
			$sql="SELECT COUNT(crm_EmployID) sum FROM crm_employee WHERE 1=1";
	   		if($name != ""){
				$sql.=" AND crm_Name LIKE '%".$name."%'";
			}	
			$sum=$db->query($sql)->fetchAll();		
			//定义每页显示几个
			$num=10;
			$cpage=1;
			//当前是第几页
			$cpage=$this->getRequest()->getParam("cpage","");
			parent::parameteraudit(array($cpage),"",2);
			if ($cpage==""){$cpage="1";}
			$page=ceil($sum[0]['sum']/$num);
			$start=($cpage-1)*$num;
			$this->view->lbcount=$sum[0]['sum'];		
			$this->view->cpage=$cpage;
			$this->view->spage=$page;
			//查出所有员工信息列表
			$employsql="SELECT * FROM crm_employee WHERE 1=1";
	   		if($name != "") {
				$employsql.=" AND crm_Name LIKE '%".$name."%'";
			}
			$employsql.=" ORDER BY crm_EmployID DESC LIMIT $start,$num";
			$employlst=$db->query($employsql)->fetchAll();
			$this->view->employlst=$employlst;
			$this->view->name=$name;	
			$this->view->urlpara=$urlpara;
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();	
	    }
    }
    /**
     * 员工信息删除
     */
    public function employdelAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
	    	$db=$userModel->getAdapter();
	    	$uid=$this->getRequest()->getParam("uid","");//员工id
	    	parent::parameteraudit(array($uid),"",1);
	    	$where=" crm_EmployID=".$uid;
	    	$userModel->delete($where);
	    	echo json_encode(0);
			exit();
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		session_destroy();
    		echo json_encode(1);
			exit();	
	    }
    }
    

    /**
     * 员工个人信息页面
     */
    public function persinfoAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s==0){
	        $db=$userModel->getAdapter();
	        $plandata=$userModel->userplanmsg($_SESSION['loginuser']['crm_EmployID']);
	        //今日拜访计划数
	        $this->view->todyplan=$plandata;
	        $upd=$this->getRequest()->getParam("upd","");//修改 参数
	        $ck=$this->getRequest()->getParam("ck","");//查看 参数
	        parent::parameteraudit(array($ck),"",2);
	        if (!empty($upd))
	        {
	            $where="crm_EmployID=".$upd;
	            $userinfo=$userModel->fetchAll($where)->toArray();
	            $this->view->userinfo=$userinfo[0];
	        }
	        $this->view->upd=$upd;
	        $this->view->ck=$ck;
    	}
    	else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();	
	    }
    }
    /**
     * 个人资料修改
     */
    public function employeesaveAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
	        $employid=$_SESSION['loginuser']['crm_EmployID'];
	        $userModel=new Application_Model_Employee();
	        $db=$userModel->getAdapter();
	        $username=$this->getRequest()->getParam("name","");//员工姓名
	        $loginname=$this->getRequest()->getParam("loginname","");//登陆账号
	        $loginpwd=$this->getRequest()->getParam("loginpwd","");//登录密码
	        $sex=$this->getRequest()->getParam("sex","");//性别
	        $phone=$this->getRequest()->getParam("phone","");//手机号码
	        $address=$this->getRequest()->getParam("address","");//地址
	        $email=$this->getRequest()->getParam("email","");//邮箱
	        $qq=$this->getRequest()->getParam("qq","");//QQ
	        $textfield=$this->getRequest()->getParam("usimg","");//用户头像
	        $userid=$this->getRequest()->getParam("uid","");//用户id	        
	        $tempdata=parent::parameteraudit(array($sex,$phone,$qq,$userid),array($username,$loginname,$loginpwd,$address,$email,$textfield),10);
			$username=$tempdata[0];
			$loginname=$tempdata[1];
	        $loginpwd=$tempdata[2];
	        $address=$tempdata[3];
	        $email=$tempdata[4];
	        $textfield=$tempdata[5];
	        
	        //保存头像
	    	if(is_uploaded_file($_FILES["file"]["tmp_name"])) {
				
				//将信息存放在变量中
				$upfile=$_FILES["file"];//用一个数组类型的字符串存放上传文件的信息
				$type=$upfile["type"];//上传文件的类型
				$size=$upfile["size"];//上传文件的大小
				$tmp_name=$upfile["tmp_name"];//用户上传文件的临时名称
				$error=$upfile["error"];//上传过程中的错误信息
				if($error=='0'){
		    		$type=$_FILES['file']['type'];
		    		$fp = fopen($_FILES['file']['tmp_name'], 'rb');
		    		if (!$fp) {
		    			echo "<p style='color:red'>读取图片失败！</p>";
		    			exit();
		    		} 
		    		else {
		    			$size=$upfile["size"];//上传文件的大小	    			
		    			switch($type){
		    			     case "image/gif" : $ok=1;
		    			     	break;
		    			     case "image/jpg" : $ok=1;
		    			     	break;
		    			     case "image/jpeg": $ok=1;
		    			     	break;
		    			     case "image/png": $ok=1;
		    			     	break;
		    			     default:$ok=0;
		    			     	break;
		    			}
		    			if($ok==0){
		    				echo "<script>window.top.window.stopupload(1); </script>";
		    				exit();
		    			}
		    			else if ($size>2*1024*1024){
		    				echo "<script>window.top.window.stopupload(2); </script>";
		    				exit();
		    			}
		    			else {
		    				//生成患者guid
			           		$exten_name=pathinfo($upfile['name'],PATHINFO_EXTENSION);
				    		$uuid=$this->setguid();
				    		$name=$uuid.'.'.$exten_name;
		    				//调用move_uploaded_file（）函数，进行文件转移
							$path="/uploadfiles/userimage/".date('Ymd')."/".$name;	
							//查询该用户是否已存在头像，并删除本地文件
							if (!empty($userid))
							{										
				    			$where="SELECT crm_UserImg FROM crm_employee WHERE crm_EmployID=".$userid;
				    			$pathImage=$db->query($where)->fetchAll();		
				    			$UserImage=	$pathImage[0]['crm_UserImg'];	
								unlink($_SERVER['DOCUMENT_ROOT'].$UserImage);//删除本地文件
							}
			    			if (!file_exists(dirname($_SERVER['DOCUMENT_ROOT'].$path))){
		    					mkdir(dirname($_SERVER['DOCUMENT_ROOT'].$path), 0777);//检查文件或目录是否存在，不存在则创建新目录
		    				}
							move_uploaded_file($tmp_name,$_SERVER['DOCUMENT_ROOT'].$path);//移动文件至根目录下
			    		}
		    		}
	    		}
	    		else{
	    			echo "<script>window.top.window.stopupload(3); </script>";
	    			exit();
	    		}
			}
	    	if($uuid != "") {//此处判断用户是否更换了头像，如果没有系统默认保存之前的头像
				$pathimg=$path;
			} else {
				$pathimg=$textfield;
			}		
	        $data=array(
	            "crm_LoginName"=>$loginname,
	            "crm_Name"=>$username,
	            "crm_LoginPwd"=>$loginpwd,
	            "crm_Gender"=>$sex,
	            "crm_UserPhone"=>$phone,
	            "crm_UserAddress"=>$address,
	            "crm_UserQQ"=>$qq,
	            "crm_UserEmail"=>$email,
	            "crm_UserImg"=>$pathimg
	        );
	        if ($employid==$userid) {
	            //修改session
	            $_SESSION['loginuser']["crm_Name"]=$username;
	            $_SESSION['loginuser']["crm_Gender"]=$sex;
	            $_SESSION['loginuser']["crm_UserPhone"]=$phone;
	            $_SESSION['loginuser']["crm_UserAddress"]=$address;
	            $_SESSION['loginuser']["crm_UserQQ"]=$qq;
	            $_SESSION['loginuser']["crm_UserEmail"]=$email;
	            $_SESSION['loginuser']["crm_UserImg"]=$pathimg;
	        }
	        if (empty($userid)) {
	            $userModel->insert($data);
	            echo "<script>window.top.window.stopupload(5); </script>";
				exit();
	            
	        } else {
	            $where1=" crm_EmployID=".$userid;
	            $userModel->update($data,$where1);
	        }        
	        echo "<script>window.top.window.stopupload(0); </script>";
			exit();
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		session_destroy();
    		echo "<script>window.top.window.stopupload(4); </script>";
			exit();
	    }
    }

	/**
	 * 生成guid方法
	 */
	public function setguid()
    {
    	$charid = strtoupper(md5(uniqid(mt_rand(), true)));
    	$hyphen = chr(45);// "-"
    	$uuid = substr($charid, 0, 8).$hyphen
		    	.substr($charid, 8, 4).$hyphen
		    	.substr($charid,12, 4).$hyphen
		    	.substr($charid,16, 4).$hyphen
		    	.substr($charid,20,12);
    	return $uuid;
    }  
}