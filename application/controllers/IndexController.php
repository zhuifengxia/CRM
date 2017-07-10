<?php
if(!isset($_SESSION)) //开启session
{ 
   session_start();
}
require_once 'BaseController.php';
 /**
  * 
  * 登录验证控制器
  *
  */
class IndexController extends BaseController
{
	
	/**
	 * 登录页面
	 */
    public function indexAction()
    {
 		//用户已经登录则直接进入CRM管理首页
 		if ($_SESSION['loginuser'])
 		{
 		     $this->forward('default','Medicrm');
 		} else {
    		$this->render('index','index');
       	}
    }
    /**
     * 登录验证
     */
    public function defaultAction()
    {
        $userModel=new Application_Model_Employee();
	    $db=$userModel->getAdapter();	    
	    if (!empty($_SESSION['loginuser'])) {       
	        $this->_forward('default','Medicrm');
	    } else {
	        $username=$this->getRequest()->getParam("username","");//用户名
	        $userpwd=$this->getRequest()->getParam("userpwd","");//用户密码
	        $tempdata=parent::parameteraudit("",array($username,$userpwd),2);
	        $username=$tempdata[0];
	        $userpwd=$tempdata[1];
	        $where=" crm_LoginName='".$username."' AND crm_LoginPwd='".$userpwd."'";
	        $userinfo=$userModel->fetchAll($where)->toArray();
	        if (count($userinfo)>0) {
				$token=$userModel->generatetoken($userinfo);//生成token
				$effecttime=date("YmdHis",strtotime('+1 day'));//token有效期,当前时间加一天
				$data=array(
					'crm_UserToken'=>$token,
					'crm_EffectTime'=>$effecttime
				);
				$userid=$userinfo[0][crm_EmployID];//登录用户ID
				$where="crm_EmployID=$userid";
				
				$userModel->update($data, $where);
				
				$sql="SELECT * FROM crm_employee WHERE crm_EmployID=".$userid;
		        $loginuser=$db->query($sql)->fetchAll();
	            $_SESSION['loginuser']=$loginuser[0];
	            $this->_forward('default','Medicrm');
	        } else {
	            $this->view->logmsg="登陆失败，账号或密码错误";
	            $this->render('index');
	        }
	    }	
    }

    /**
     * 退出登录
     */
 	public function loutAction()
    {
		parent::nopermissions();
    }
}
?>
