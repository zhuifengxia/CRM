<?php
require_once 'BaseController.php';
/**
 * 
 *系统设置页面控制器
 *
 */
class SystemsetController extends BaseController
{
    /**
     * 系统设置版块
     */
    public function sysemailAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
	        $plandata=$userModel->userplanmsg($_SESSION['loginuser']['crm_EmployID']);
	        //今日拜访计划数
	        $this->view->todyplan=$plandata;
	    	$sysemailModel=new Application_Model_SystemEmail();
			//获取所有设置的系统邮箱信息
			$emaildata=$sysemailModel->emaildatalst();
			$this->view->emaildata=$emaildata;
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();
	    }
    }
	
	/**
	 * 修改系统邮箱页面
	 */
	public function sysemaileditAction()
	{
		$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
		    $plandata=$userModel->userplanmsg($_SESSION['loginuser']['crm_EmployID']);
		    //今日拜访计划数
		    $this->view->todyplan=$plandata;
			$emailid=$this->getRequest()->getParam("emailid","");//邮箱ID
			parent::parameteraudit(array($emailid),"",1);
			//如果邮箱ID不为空说明是邮箱编辑页面；否则为邮箱添加
			if(!empty($emailid))
			{
				$sysemailModel=new Application_Model_SystemEmail();
				$emaildata=$sysemailModel->emaildetail($emailid,0);
				$this->view->emaildetail=$emaildata;
			}
			$this->render("emaildetail");
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();
	    }
	}
	
	/**
	 * 系统邮箱的编辑/添加保存
	 */
	public function sysemailsaveAction()
	{
		$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
		    $emailid=$this->getRequest()->getParam("emailid","");//邮箱ID
		    $ownname=$this->getRequest()->getParam("ownname","");//邮箱所属人
		    $emailaccount=$this->getRequest()->getParam("emailaccount","");//邮箱账号
		    $emailpwd=$this->getRequest()->getParam("emailpwd","");//邮箱密码
		    $emailstate=$this->getRequest()->getParam("emailstate","");//邮箱状态0可用；1不可用
		    $tempdata=parent::parameteraudit(array($emailid,$emailstate),array($ownname,$emailaccount,$emailpwd),5);
		    $ownname=$tempdata[0];
		    $emailaccount=$tempdata[1];
		    $emailpwd=$tempdata[2];
		    
		    $sysemailModel=new Application_Model_SystemEmail();
		    $data=array(
		        "crm_EmailAccount"=>$emailaccount,
		        "crm_EmailPwd"=>$emailpwd,
		        "crm_OwnName"=>$ownname,
		        "crm_EmailState"=>$emailstate
		    );
		    $res=$sysemailModel->addemail($emailid, $data);
		    if($res==0)
		    {
		        $res=empty($emailid)?0:1;
		    }
		    else 
		    {
		        $res=2;
		    }
		    echo json_encode($res);
		    exit;
    	}
    	else {
	    	//说明token过期或者token错误，需要重新登录
    		session_destroy();
    		echo json_encode(3);
		    exit;	
	    }
	}
	
	/**
	 * 删除邮箱信息
	 */
	public function sysemaildelAction()
	{
		$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
			$emailid=$this->getRequest()->getParam("emailid","");//邮箱ID
			//判断参数是否合法
			parent::parameteraudit(array($emailid),"",1);
			$sysemailModel=new Application_Model_SystemEmail();
			$sysemailModel->emaildetail($emailid,1);
			echo "<script>alert('删除成功！');window.location.href='/Systemset/sysemail';</script>";
			exit;
    	}
    	else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();
	    }
	}
	
	//用户数据同步页面
	public function synchrodataAction()
	{
	
	}
	
	/**
	 * 用户信息同步方法
	 */
	public function usermsgsyncAction()
	{
		$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s==0) {
		    if($_SESSION['loginuser']['crm_UserLavel']!=0)
		    {
		        //如果不是管理员就没有权限同步数据
		        echo json_encode(2);
		        exit;
		    }
		    $clientModel=new Application_Model_ClientMsg();
		    $db=$clientModel->getAdapter();
			$synctype=$this->getRequest()->getParam("synctype","");//数据同步类型；0是同步新注册用户；1同步已有用户数据信息
			parent::parameteraudit(array($synctype),"",1);
			if($synctype==0)//新用户数据同步
			{
			    $lasttime="SELECT MAX(crm_RegTime)AS lstime FROM crm_clientmsg";
			}
			else//已有用户信息更新
			{
			    $lasttime="SELECT MIN(crm_UpdateTime)AS lstime FROM crm_clientmsg WHERE crm_UpdateTime IS NOT NULL AND crm_UpdateTime!=''";
			}
			$requesttime=$db->query($lasttime)->fetchAll();
			$requesttime=empty($requesttime[0]['lstime'])?0:$requesttime[0]['lstime'];
			//将获取的接口数据保存到crm客户表中
			$returnmsg=$clientModel->usermsgsync($synctype, $requesttime);
			echo json_encode($returnmsg);
			exit;
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		session_destroy();
    		echo json_encode(3);
			exit;
	    }
	}
	
}