<?php
require_once 'BaseController.php';
/**
 * 
 *该控制器专门用于客户管理板块相关
 *
 */
class MedicrmController extends BaseController
{
    /**
     * CRM管理首页
     */
    public function defaultAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
    		$db=$userModel->getAdapter();
			//用户日增长数据
	        $usergrowth=$userModel->usergrowth();
	        $this->view->usergrowth=$usergrowth;	       
	        //今日拜访计划数
	        $plandata=$userModel->userplanmsg($_SESSION['loginuser']['crm_EmployID']);
	        $this->view->todyplan=$plandata;
	        //用户活跃度
	        $this->view->one=$userModel->useractive(0);//今天
	        $this->view->two=$userModel->useractive(1);//1-3个月
	        $this->view->three=$userModel->useractive(2);//3-6个月
	        $this->view->four=$userModel->useractive(3);//6-12个月
	        $this->view->five=$userModel->useractive(4);//一年及以上
	        //首页用户地区分布
	        $this->view->userdata=$userModel->useractive(5);
	    }
	    else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();
	    }	      
    }
    /**
     * 首页用户活跃度数据更新
     */
    public function updatemsgAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
		    $db=$userModel->getAdapter();		
			//用户活跃度
			$sum1=$userModel->useractive(0);//今天
			$sum2=$userModel->useractive(1);//1-3个月
			$sum3=$userModel->useractive(2);//3-6个月
			$sum4=$userModel->useractive(3);//6-12个月
			$sum5=$userModel->useractive(4);//一年及以上
			$result=array($sum1,$sum2,$sum3,$sum4,$sum5);
			echo json_encode($result);
			exit;
    	}
    	else {
	    	//说明token过期或者token错误，需要重新登录
	    	session_destroy();
    		echo json_encode(0);
			exit;
	    }
    }
    /**
     * 客户信息列表页面
     */
    public function customerlstAction()
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
			//取得传入的参数，和URL参数
			$paraarry=self::getdatafun();
			$getdata=$paraarry['getdata'];
			$urlpara=$paraarry['urlpara'];
			//获取客户总数
			$clientModel=new Application_Model_ClientMsg();
			$sum=$clientModel->clientlstsum($getdata);
			
			//定义每页显示几个
			$num=10;
			//当前是第几页
			$cpage=$this->getRequest()->getParam("cpage","");
			parent::parameteraudit(array($cpage),"",15);
			if ($cpage==""){$cpage="1";}
			$page=ceil($sum[0]['sum']/$num);
			$start=($cpage-1)*$num;//第几页从第几条开始
					
			$this->view->urlpara=$urlpara;	
			$this->view->lbcount=$sum[0]['sum'];//总共多少条		
			$this->view->cpage=$cpage;//当前是第几页
			$this->view->spage=$page;//总共多少页
			
			//查出所有客户信息列表
			$userlst=$clientModel->clientlstsum($getdata,$start,$num);
			$this->view->userlst=$userlst;
	        //列出所有科室
			$partmentModel=new Application_Model_Department();
			$dep=$partmentModel->fetchAll()->toArray();
			$this->view->deplist=$dep;
			//获取一级城市列表
			$cityModel=new Application_Model_ChinaCity();
			$where1=" ParentID=0";
			$ccity=$cityModel->fetchAll($where1)->toArray();
			$this->view->ccity=$ccity;
			
			$this->view->s2=$getdata['s2'];
			$this->view->hospital=$getdata['hospital'];
			$this->view->selarr=$getdata;
    	}
    	else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();	
	    }
		
    }
    /**
     * 客户信息修改、添加、查看
     */
    public function customerdetailAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0){
	    	$clientModel=new Application_Model_ClientMsg();
	    	$db=$clientModel->getAdapter();
	    	$plandata=$userModel->userplanmsg($_SESSION['loginuser']['crm_EmployID']);
	        //今日拜访计划数
	        $this->view->todyplan=$plandata;
	    	$upd=$this->getRequest()->getParam("upd","");//该参数判断是编辑还是添加,同时也是客户信息id
	    	$ck=$this->getRequest()->getParam("ck","");
	    	parent::parameteraudit(array($upd,$ck),"",2);
	    	if (!empty($upd))
	    	{
	    		$where=" crm_ClientID=".$upd;
	    		$clientinfo=$clientModel->fetchAll($where)->toArray();
	    		$this->view->clientinfo=$clientinfo[0];
	    	}
	    	//列出所有科室
			$partmentModel=new Application_Model_Department();
			$dep=$partmentModel->fetchAll()->toArray();
			//获取一级城市列表
			$cityModel=new Application_Model_ChinaCity();
			$where1=" ParentID=0";
			$ccity=$cityModel->fetchAll($where1)->toArray();
			$this->view->ccity=$ccity;
			$this->view->deplist=$dep;
	    	$this->view->upd=$upd;
	    	$this->view->ck=$ck;
    	}
    	else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();	
	    }
    } 
    /**
     * 客户信息保存
     */
    public function clientmsgsaveAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
	    	$clientModel=new Application_Model_ClientMsg();
	    	$paraarry=self::getdatafun();
			$getdata=$paraarry['getdata'];
			$data=array(
				"crm_ZlpAccount"=>$getdata['zlpaccount'],
				"crm_ZlpNickName"=>$getdata['nickname'],
				"crm_ClientName"=>$getdata['name'],
				"crm_ClientGender"=>$getdata['gender'],
				"crm_ClientPhone"=>$getdata['phone'],
				"crm_ClientEmail"=>$getdata['email'],
				"crm_ClientHospital"=>$getdata['hospital'],
				"crm_ClientSection"=>$getdata['depart'],
				"crm_ClientTitle"=>$getdata['ctitle'],
				"crm_ClientCity"=>$getdata['s1'].",".$getdata['s2'],
				"crm_ClientType"=>$getdata['type'],
				"crm_ClientSource"=>$getdata['source'],
				"crm_ClientQQ"=>$getdata['qq'],
				"crm_WeChat"=>$getdata['wechat'],
				"crm_EmployID"=>$_SESSION['loginuser']['crm_EmployID']
			);
			if (empty($getdata['cid'])) {
				//添加信息
				$clientModel->insert($data);
				echo json_encode(0);
				exit;
			} else {
				//编辑信息
				$where=" crm_ClientID=".$getdata['cid'];
				$clientModel->update($data, $where);
				echo json_encode(1);
				exit;
			}
    	}
    	else {
	    	//说明token过期或者token错误，需要重新登录
	    	session_destroy();
    		echo json_encode(2);
			exit;
	    }
		
    }
    /**
     * 客户信息删除
     */
    public function custdelAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
	    	$clientModel=new Application_Model_ClientMsg();
	    	$db=$clientModel->getAdapter();
	    	$cid=$this->getRequest()->getParam("cid","");//客户信息id
	    	parent::parameteraudit(array($cid),"",1);
	    	$where="crm_ClientID=".$cid;
	    	$clientModel->delete($where);
	    	echo "<script>alert('客户信息删除成功！');</script>";
			echo "<script>window.location.href='/Medicrm/customerlst';</script>";
			exit();
    	}
    	else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();
	    }
    }
    /**
     * 发送邮件页面
     */
    public function sendemailAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0){
	    	$plandata=$userModel->userplanmsg($_SESSION['loginuser']['crm_EmployID']);
	        //今日拜访计划数
	        $this->view->todyplan=$plandata;
	        
	       	$paraarry=self::getdatafun();
	       	$getdata=$paraarry['getdata'];
			$clientModel=new Application_Model_ClientMsg();
			$sum=$clientModel->clientlstsum($getdata);
			$lbcount=$sum[0][sum];
			$result=$clientModel->clientlstsum($getdata,0,$lbcount);
			$emailarr=array();
			foreach ($result as $re){
				$emailarr[]=$re[crm_ClientEmail];
			}
	    	$useremail=implode(",",$emailarr);
	    	$this->view->username=$useremail;
    	}
    	else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();	
	    }
    }
    
    /**
     * 邮件发送、保存
     */
    public function emailsaveAction()
	{
		$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
			$mailrecordModel=new Application_Model_Mailrecord();
			$db=$mailrecordModel->getAdapter();	
			$emzt=$this->getRequest()->getParam("subject","");//主题
			$emfj=$this->getRequest()->getParam("textfield","");//附件
			$emzw=$this->getRequest()->getParam("datacountent","");//正文
	    	$emsjr=$this->getRequest()->getParam("clientname","");//收件人
	    	$emaildata=parent::parameteraudit("",array($emzt,$emfj,$emsjr),3);
	    	$emzt=$emaildata[0];
	    	$emfj=$emaildata[1];
//	    	$emzw=$emaildata[2];
	    	$emsjr=$emaildata[2];
	    	$status=0;
	    	//收件人加入数组
	    	$useremail=explode(",", $emsjr);
	    	//获取系统添加的发送邮箱和登录密码
	    	$sql="SELECT crm_OwnName,crm_EmailAccount,crm_EmailPwd FROM crm_systememail WHERE crm_EmailState=0" ;
		    $sysmail=$db->query($sql)->fetchAll();
		    //请添加系统发送邮箱
		    if (count($sysmail) == 0) {
		    	echo "<script>alert('请添加系统发送邮箱');window.location.href='/Systemset/sysemail';</script>";
		    	exit();
		    }else {
		    	$num=rand(0,count($sysmail)-1);//账号随机数
		    } 
		    // 定义SMTP的验证参数，设置正确的邮箱和登录密码
			$config=array(
				'auth'=>'login',
				'username'=>$sysmail[$num]['crm_EmailAccount'],
				'password'=>$sysmail[$num]['crm_EmailPwd']
			);
			$transport=new Zend_Mail_Transport_Smtp('smtp.qq.com',$config);// 实例化验证的对象
			$mail=new Zend_Mail('UTF-8');// 实例化发送邮件对象
	    	$mail->setBodyHtml($emzw);// 发送邮件的主体
	    	$mail->setFrom($sysmail[$num]['crm_EmailAccount'],$sysmail[$num]['crm_OwnName']);// 定义邮件发送使用的邮箱 
	    	$mail->addTo($useremail,'');// 定义邮件的接收邮箱  
	    	$mail->setSubject($emzt);// 定义邮件主题  
	    	
			if(!empty($_FILES['cousefile']['tmp_name'])) {
	    	    $exten_name=pathinfo($_FILES['cousefile']['name'],PATHINFO_EXTENSION);//得到附件扩展名
	    	    //验证上传文件是否合法，是否是pdf文件类型
	    		if($exten_name=="pdf" || $exten_name=="xls" || $exten_name=="doc" || $exten_name=="docx" || $exten_name=="jpeg" || $exten_name=="png") {
	    		    //添加附件到待发送邮件中
		    		$attach = $mail->createAttachment(file_get_contents($_FILES['cousefile']['tmp_name']));
		    		//附件类型
		    		$attach->type = $_FILES['cousefile']['type'];
		    		//附件名称
		    		$name = iconv('utf-8','gbk',$_FILES['cousefile']['name']);
		    		$attach->filename = $name;
	    		} else {
	    			$status=1;
	    			header("Content-Type: text/html; charset=utf-8");
	    			echo "<script>alert('邮件发送失败,附件格式错误！');window.location.href='/Medicrm/sendemail';</script>";
	    		}
	    	}
	    	if ($status != 1) {
	    		try {
	    			$mail->send($transport);// 执行发送操作 
	    		}
	    		catch (Exception $e) {
	    			$status=1;
	    			header("Content-Type: text/html; charset=utf-8");
	    			echo "<script>alert('邮件发送失败,".$e->getMessage()."');window.location.href='/Medicrm/sendemail';</script>";
	    		}
	    	}
	    	if ($status != 1) {
	    		$data=array(
			    'crm_IsSend'=>'1',
		    	'crm_MailTitle'=>$emzt,
		    	'crm_MailCoutent'=>$emzw,
			    'crm_MailUser'=>$sysmail[$num]['crm_OwnName'],
		    	'crm_SendTime'=>date('Y-m-d H:i:s'),
			    'crm_MailStatus'=>"已发送至邮箱"
			    );
			    try {	    		
					$mailrecordModel->insert($data);//添加记录
					header("Content-Type: text/html; charset=utf-8");
					echo "<script>alert('邮件发送成成功！');window.location.href='/Medicrm/sendemail';</script>";
			    }
			    catch (Exception $e){
			    	header("Content-Type: text/html; charset=utf-8");
			    	"<script>alert('邮件添加记录失败,".$e->getMessage()."！');window.location.href='/Medicrm/sendemail';</script>";
			    }
	    	}
	    	exit();		
    	}
		else {
	    	//说明token过期或者token错误，需要重新登录
	    	session_destroy();
    		echo json_encode(2);
			exit();
	    }
	}
	
    /**
     * 获取省份下级市
     */
    public function changecityAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
		    $cityModel=new Application_Model_ChinaCity();
	    	$cityid=$this->getRequest()->getParam("cityid","");//城市一级id
	    	parent::parameteraudit(array($cityid),"",1);
	    	$where="ParentID=".$cityid;
	    	$county=$cityModel->fetchAll($where)->toArray();
	    	echo json_encode($county);
			exit();
    	}
		else {
	    	//说明token过期或者token错误，需要重新登录
    		session_destroy();
    		echo json_encode(0);
			exit();
	    }
		
    }
    
  	/**
  	 * 获取选择的市内的所有医院
  	 */
    public function getcityhospitalAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
	    	$areaid=$this->getRequest()->getParam("areaid","");//地级市id
	    	parent::parameteraudit(array($areaid),"",1);
	    	$hospitalModel=new Application_Model_ChinaHospital();
	    	$db=$hospitalModel->getAdapter();
	    	$sql="SELECT a.* FROM china_hospital a , ruiyicity b WHERE a.AreaID=b.CityID AND (b.parentid=".$areaid.")";    	
	    	$areahosptial=$db->query($sql)->fetchAll();
	    	echo json_encode($areahosptial);
			exit();
    	}
		else {
	    	//说明token过期或者token错误，需要重新登录
    		session_destroy();
    		echo json_encode(0);
			exit();	
	    }
    }

    /**
     * 拜访信息
     */
    public function visitrecordAction()
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
	        
			//取得传入的参数，和URL参数
			$paraarry=self::getdatafun();
			$getdata=$paraarry['getdata'];
			$urlpara=$paraarry['urlpara'];		
			//获取客户总数
			$visitModel=new Application_Model_ClientVisitrecord();;
			$sum=$visitModel->visitlistsum($getdata);
			//定义每页显示几个
			$num=10;
			$cpage=1;
			//当前是第几页
			$cpage=$this->getRequest()->getParam("cpage","");
			parent::parameteraudit(array($cpage),"",3);
			if ($cpage==""){$cpage="1";}
			$page=ceil($sum[0]['num']/$num);
			$start=($cpage-1)*$num;
			
			$this->view->lbcount=$sum[0]['num'];		
			$this->view->cpage=$cpage;
			$this->view->spage=$page;
			$this->view->urlpara=$urlpara;
			//查出所有客户回访记录列表
			$userlst=$visitModel->visitlistsum($getdata,$start,$num);
			
			$this->view->visitlst=$userlst;
			$this->view->selarr=$getdata;
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();
	    }
    }
    /**
     * 记录人列表
     */
    public function noterlstAction(){
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0){
    		$db=$userModel->getAdapter();
    		$sql="SELECT crm_EmployID, crm_Name FROM crm_employee WHERE 1=1";
    		$data=$db->query($sql)->fetchAll();
    		echo json_encode($data);
    		exit();
    	}
    }
	/**
	 * 拜访信息修改、添加
	 */
    public function visitdetailAction()
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
	    	$upd=$this->getRequest()->getParam("upd","");//该参数判断是编辑还是添加,同时也是拜访信息id
	    	$ck=$this->getRequest()->getParam("ck","");//该参数判断是查看信息
	    	parent::parameteraudit(array($ck),"",2);
	    	if (!empty($upd))
	    	{
	    		//$where="SELECT v.*,c.crm_ClientName FROM crm_clientvisitrecord v LEFT JOIN crm_clientmsg c ON c.crm_ClientID=v.crm_ClientID WHERE v.crm_ID=".$upd;
	    		$where="SELECT * FROM crm_clientvisitrecord WHERE crm_ID=".$upd;
	    		$visitinfo=$db->query($where)->fetchAll();
	    		//获取客户名
	    		$userwhere="SELECT crm_ClientName FROM crm_clientmsg WHERE crm_ClientID=".$visitinfo[0]['crm_ClientID']."";
	    		$userinfo=$db->query($userwhere)->fetchAll();
	    		$visitinfo[0]['crm_ClientName']=$userinfo[0]['crm_ClientName'];
	    		
	    		//获取员工姓名
	    		$employwhere="SELECT crm_Name FROM crm_employee WHERE crm_EmployID=".$visitinfo[0]['crm_EmployID']."";
	    		$employinfo=$db->query($employwhere)->fetchAll();
	    		
	    		$this->view->employinfo=$employinfo[0];
	    		$this->view->visitinfo=$visitinfo[0];
	    		$this->view->userinfo=$userinfo[0]['crm_ClientName'];
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
     * 回访信息保存
     */
    public function visitsaveAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
	    	$visitModel=new Application_Model_ClientVisitrecord();
	    	$db=$visitModel->getAdapter();
	    	$name=$this->getRequest()->getParam("name","");//客户姓名
	    	$vtype=$this->getRequest()->getParam("vtype","");//回访方式
	    	$vtime=$this->getRequest()->getParam("vtime","");//回访时间
	    	$status=$this->getRequest()->getParam("status","");//回访状态
	    	$vmsg=$this->getRequest()->getParam("vmsg","");//回访主题
	    	$vbmsg=$this->getRequest()->getParam("vbmsg","");//回访用户反馈
			$cid=$this->getRequest()->getParam("cid","");//回访信息id
			$noter=$this->getRequest()->getParam("noter","");//记录人
			$vistdata=parent::parameteraudit(array($cid),array($name,$vtype,$vtime,$status,$vmsg,$vbmsg,$noter),8);
			$name=$vistdata[0];
			$vtype=$vistdata[1];
			$vtime=$vistdata[2];
			$status=$vistdata[3];
			$vmsg=$vistdata[4];
			$vbmsg=$vistdata[5];
			$noter=$vistdata[6];	
			//根据客户姓名获取其id
			if (!empty($name)) {
				$sql="SELECT crm_ClientID FROM crm_clientmsg WHERE crm_ClientName='".$name."'";
				$clientinfo=$db->query($sql)->fetchAll();
				if (count($clientinfo) == "") {
					echo json_encode(2);//该用户不存在
					exit;
				}
			}
			
			//已经添加的用户不能再添加
			$where="crm_ClientID=".$clientinfo[0]['crm_ClientID'];
			$clientModel = new Application_Model_ClientVisitrecord();
			$client=$clientModel->fetchAll($where)->toArray();
			if (count($client) >= 1){
				echo json_encode(3);//该用户已添加
				exit();
			}
			//根据记录人姓名取其id
			if (!empty($noter)){
				$sql="SELECT crm_EmployID FROM crm_employee WHERE crm_Name='".$noter."'";
				$noterid=$db->query($sql)->fetchAll();
			}
			$data=array(
				"crm_ClientID"=>$clientinfo[0]['crm_ClientID'],
				"crm_VisitTime"=>$vtime,
				"crm_VisitType"=>$vtype,
				"crm_VisitMsg"=>$vmsg,
				"crm_VisitStatus"=>$status,
				"crm_EmployID"=>$noterid[0]['crm_EmployID'],
			    "crm_VisitBackMsg"=>$vbmsg
			);
			//添加信息
			if (empty($cid)) {
				$visitModel->insert($data);
				echo json_encode(0);
				exit;
			} else {//编辑信息
				$where=" crm_ID=".$cid;
				$visitModel->update($data, $where);
				echo json_encode(1);
				exit;
			}
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		session_destroy();
    		echo "<script>window.location.href='/index/index';</script>";
    		exit();	
	    }
    }

    /**
     * 拜访信息删除
     */
    public function visitdelAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
	    	$visitModel=new Application_Model_ClientVisitrecord();
	    	$db=$visitModel->getAdapter();
	    	$cid=$this->getRequest()->getParam("cid","");//拜访信息id
	    	parent::parameteraudit(array($cid),"",1);
	    	$where="crm_ID=".$cid;
	    	$visitModel->delete($where);
	    	echo "<script>alert('该条拜访信息删除成功！');</script>";
			echo "<script>window.location.href='/Medicrm/visitrecord';</script>";
			exit();
    	}
   		else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();	
	    }
		
    }
    

 	/**
 	 * 获取客户信息表中全部用户名
 	 */
    public function lstuserAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
	    	$db=$userModel->getAdapter();
	    	$sql="SELECT crm_ClientID,crm_ClientName FROM crm_clientmsg";
		   	$lstuser=$db->query($sql)->fetchAll();
		   	echo json_encode($lstuser);
		    exit();
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		session_destroy();
    		echo "<script>window.location.href='/index/index';</script>";
    		exit();	
	    }
    }
    
    
    /**
     * 反馈信息
     */
    public function feedbacklstAction()
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
	           	
			//取得传入的参数，和URL参数
			$paraarry=self::getdatafun();
			$getdata=$paraarry['getdata'];
			$urlpara=$paraarry['urlpara'];
			//获取客户总数
			$feedbacklstModel=new Application_Model_ClientFeedback();
			$sum=$feedbacklstModel->feedbacklstsum($getdata);
			//定义每页显示几个
			$num=10;
			//当前是第几页
			$cpage=$this->getRequest()->getParam("cpage","");
			parent::parameteraudit(array($cpage),"",2);
			if ($cpage==""){$cpage=1;}
			$page=ceil($sum[0]['num']/$num);
			$start=($cpage-1)*$num;
			$this->view->lbcount=$sum[0]['num'];		
			$this->view->cpage=$cpage;
			$this->view->spage=$page;
			$this->view->urlpara=$urlpara;
			//查出所有客户回访记录列表
			$feedbacklst=$feedbacklstModel->feedbacklstsum($getdata,$start,$num);
			$this->view->feedbacklst=$feedbacklst;
			$this->view->selarr=$getdata;
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();	
	    }
    }

	/**
	 * 反馈信息修改、添加
	 */
    
    public function feedbackdetailAction()
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
	    	$upd=$this->getRequest()->getParam("upd","");//该参数判断是编辑还是添加,同时也是拜访信息id
	    	$ck=$this->getRequest()->getParam("ck","");//该参数判断是查看信息
	    	parent::parameteraudit(array($upd,$ck),"",2);
	    	if (!empty($upd)) {
	    		//$where="SELECT v.*,c.crm_ClientName,c.crm_ClientPhone,c.crm_ClientEmail FROM crm_clientvisitrecord v LEFT JOIN crm_clientmsg c ON c.crm_ClientID=v.crm_ClientID WHERE v.crm_ID=".$upd;
	    		$feedbackModel=new Application_Model_ClientFeedback();
	    		$db=$feedbackModel->getAdapter();
	    		$where="SELECT * FROM crm_clientvisitrecord  WHERE crm_ID=".$upd." LIMIT 1";
	    		$feedbackinfo=$db->query($where)->fetchAll();	    		
	    		$usersql="SELECT crm_ClientName, crm_ClientPhone, crm_ClientEmail FROM crm_clientmsg WHERE crm_ClientID=".$feedbackinfo[0]['crm_ClientID']." LIMIT 1";
	    		$userdata=$db->query($usersql)->fetchAll();
	    		$feedbackinfo[0]['crm_ClientName']=$userdata[0]['crm_ClientName'];
	    		$feedbackinfo[0]['crm_ClientPhone']=$userdata[0]['crm_ClientPhone'];
	    		$feedbackinfo[0]['crm_ClientEmail']=$userdata[0]['crm_ClientEmail'];
	    		$this->view->feedbackinfo=$feedbackinfo[0];
	    	}
	    	$this->view->upd=$upd;
	    	$this->view->ck=$ck;
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();	
	    }
    }
	/**
	 * 反馈信息保存
	 */
    public function feedbacksaveAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
	    	$feedbackModel=new Application_Model_ClientFeedback();
	    	$db=$userModel->getAdapter();
	    	$name=$this->getRequest()->getParam("name","");//客户姓名
	    	$fabstract=$this->getRequest()->getParam("fabstract","");//反馈摘要
	    	$content=$content=$this->getRequest()->getParam("content","");//反馈内容
	    	$ftime=$this->getRequest()->getParam("ftime","");//反馈时间
	    	$floginuser=$this->getRequest()->getParam("floginuser","");//记录人
			$fid=$this->getRequest()->getParam("fid","");//反馈信息id
			$feeddata=parent::parameteraudit(array($fid),array($name,$fabstract,$content,$ftime,$floginuser),6);
			$name=$feeddata[0];
			$fabstract=$feeddata[1];
			$content=$feeddata[2];
			$ftime=$feeddata[3];
			$floginuser=$feeddata[4];
			if (!empty($name)) {
				$sql="SELECT crm_ClientID FROM crm_clientmsg WHERE crm_ClientName='".$name."'";
			   	$useid=$db->query($sql)->fetchAll();
				if (count($useid)<=0)//如果客户id不存在说明客户表中不存在该客户
			   	{
			   		echo json_encode(2);
			    	exit();
			   	}
			}
	    	if (!empty($floginuser)) {
		    	$sql="SELECT crm_EmployID FROM crm_employee WHERE crm_LoginName='".$floginuser."'";
				$employid=$db->query($sql)->fetchAll();
	    		if (count($employid)<=0)//员工ID不存在
			   	{
			   		echo json_encode(3);
			    	exit();
			   	}
	    	}
			$data=array(
				"crm_ClientID"=>$useid[0]['crm_ClientID'],//回访客户ID
				"crm_VisitMsg"=>$fabstract,//反馈摘要
				"crm_VisitBackMsg"=>$content,//反馈内容
				"crm_VisitTime"=>$ftime,//反馈时间	
				"crm_EmployID"=>$employid[0]['crm_EmployID'],//提交人为当前登录的用户名
				"crm_VisitStatus"=>1
			);
			//编辑信息
			if (!empty($fid)) {
				$where=" crm_ID=".$fid;
				$feedbackModel->update($data, $where);
				echo json_encode(1);
				exit;
			}
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		session_destroy();
    		echo "<script>window.location.href='/index/index';</script>";
    		exit();	
	    }
    }

	/**
	 * 反馈信息删除
	 */
    public function feedbackdelAction()
    {
    	$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s==0){
	    	$feedbackModel=new Application_Model_ClientFeedback();
	    	$db=$feedbackModel->getAdapter();
	    	$fid=$this->getRequest()->getParam("fid","");//反馈信息id集合
	    	parent::parameteraudit(array($fid),"",1);
	    	$where=" crm_ID=".$fid;
	    	$feedbackModel->delete($where);
	    	echo json_encode(0);
			exit();
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		session_destroy();
    		echo "<script>window.location.href='/index/index';</script>";
    		exit();	
	    }
    }
 	/**
 	 * 导出客户信息excel
 	 */
	public function excelcustomAction()
	{	
		$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
			$paraarry=self::getdatafun();
			$getdata=$paraarry['getdata'];
			$clientModel=new Application_Model_ClientMsg();
			$sum=$clientModel->clientlstsum($getdata);
			$lbcount=$sum[0]['sum'];
			$result=$clientModel->clientlstsum($getdata,0,$lbcount);
			header ( "Content-type:application/vnd.ms-excel" );
			header ( "Content-Disposition:filename=客户回访记录.xls" );
			$html="<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
				<html xmlns='http://www.w3.org/1999/xhtml'>
				<head>
				<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
				<title>无标题文档</title>
				<style>
				td{
				text-align:center;
				font-size:20px;
				font-family:宋体;
				border:1px solid #000000;
				color:#152122;
				width:100px;
				}
				table,tr{
				border-style:none;
				}
				.title{
				background:#ffffff;
				color:#000000;
				font-weight:bold;
				}
				</style>
				</head>
					
				<body>
				<table width='800' border='1'>
				<tr>
				<td class='title'>珍立拍账号</td>
				<td class='title'>珍立拍ID</td>
				<td class='title'>珍立拍昵称</td>
				<td class='title'>客户姓名</td>
				<td class='title'>客户性别</td>
				<td class='title'>客户电话</td>
				<td class='title'>客户邮箱</td>
				<td class='title'>客户所在医院</td>
				<td class='title'>客户所在科室</td>
				<td class='title'>客户职称</td>
				<td class='title'>所在城市</td>
				<td class='title'>客户类型</td>
				<td class='title'>客户来源</td>
				<td class='title'>客户QQ</td>
				<td class='title'>客户微信</td>
				</tr>";
			
			foreach ($result as $r) {
				if($r['crm_ClientType']==0){
					$clienttype="医生";
				}elseif($r['crm_ClientType']==1){
					$clienttype="护士";
				}elseif($r['crm_ClientType']==2){
					$clienttype="医药行业";
				}elseif($r['crm_ClientType']==3){
					$clienttype="医学生";
				}elseif($r['crm_ClientType']==4){
					$clienttype="赔付宝";
				}elseif($r['crm_ClientType']==5){
					$clienttype="其他人群";
				}elseif($r['crm_ClientType']==6){
					$clienttype="实习医生";
				}
				
				if($r['crm_ClientSource']==0){
					$clientsource="珍立拍用户";
				}elseif($r['crm_ClientSource']==1){
					$clientsource="睿医用户";
				}elseif($r['crm_ClientSource']==2){
					$clientsource="外部合作";
				}elseif($r['crm_ClientSource']==3){
					$clientsource="实习医生";
				}
	
				$html.="<tr><td>".$r['crm_ZlpAccount']."</td>
					<td>".$r['crm_ZlpID']."</td>
					<td>".$r['crm_ZlpNickName']."</td>
					<td>".$r['crm_ClientName']."</td>
					<td>".(empty($r['crm_ClientGender'])?'女':'男')."</td>
					<td>".$r['crm_ClientPhone']."</td>
					<td>".$r['crm_ClientEmail']."</td>
					<td>".$r['crm_ClientHospital']."</td>
					<td>".$r['crm_ClientSection']."</td>
					<td>".$r['crm_ClientTitle']."</td>
					<td>".$r['crm_ClientCity']."</td>
					<td>".$clienttype."</td>
					<td>".$clientsource."</td>
					<td>".$r['crm_ClientQQ']."</td>
					<td>".$r['crm_WeChat']."</td></tr>";
			}
			$html.="</table></body></html>";
			echo $html;exit();
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();	
	    }
	}
    /**
     * 拜访信息导出excel
     */
	public function excelexportAction()
	{
		$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0){
			$paraarry=self::getdatafun();
			$getdata=$paraarry['getdata'];
			//获取拜访信息总数
			$visitModel=new Application_Model_ClientVisitrecord();;
			$sum=$visitModel->visitlistsum($getdata);
			$num=$lbcount=$sum[0][num];
			//获取拜访信息列表
			$result=$visitModel->visitlistsum($getdata,0,$num);
			header ( "Content-type:application/vnd.ms-excel" );
			header ( "Content-Disposition:filename=客户回访记录.xls" );
			$html="<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
				<html xmlns='http://www.w3.org/1999/xhtml'>
				<head>
				<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
				<title>无标题文档</title>
				<style>
				td{
				text-align:center;
				font-size:20px;
				font-family:宋体;
				border:1px solid #000000;
				color:#152122;
				width:100px;
				}
				table,tr{
				border-style:none;
				}
				.title{
				background:#ffffff;
				color:#000000;
				font-weight:bold;
				}
				</style>
				</head>
					
				<body>
				<table width='800' border='1'>
				<tr>
				<td class='title'>回访记录ID</td>
				<td class='title'>回访客户名</td>
				<td class='title'>回访时间</td>
				<td class='title'>回访方式</td>
				<th class='title'>回访备注</th>
				<th class='title'>回访状态</th>
				</tr>";
			
			foreach ($result as $r){
				if($r['crm_VisitType']=="0"){
					$visittype="短信";
				}elseif($r['crm_VisitType']=="1"){
					$visittype="邮件";
				}
				elseif($r['crm_VisitType']=="2"){
					$visittype="电话";
				}
				elseif($r['crm_VisitType']=="3"){
					$visittype="实体";
				}
				$html.="<tr><td>".$r['crm_ID']."</td>
					<td>".$r['crm_ClientName']."</td>
					<td>".$r['crm_VisitTime']."</td>
					<td>".$visittype."</td>
					<td>".$r['crm_VisitMsg']."</td>
					<td>".(empty($r['crm_VisitStatus'])?'未回访':'已回访')."</td></tr>";
			}
			$html.="</table></body></html>";
			echo $html;exit();
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		parent::nopermissions();	
	    }
	}	
	/**
	 * 用户详细数据同步
	 */
	public function datasycAction()
	{
		$userid=$_SESSION['loginuser']['crm_EmployID'];
    	$token=$_SESSION['loginuser']['crm_UserToken'];
    	$userModel=new Application_Model_Employee();
    	$s=$userModel->tokenverify($userid,$token);
    	if ($s == 0) {
		    $requesttime=$this->getRequest()->getParam("requesttime","");
		    $tempdata=parent::parameteraudit("",array($requesttime),1);
		    $requesttime=$tempdata[0];
		    $clientModel=new Application_Model_ClientMsg();
		    $returnmsg=$clientModel->usermsgsync(2, $requesttime);
			echo json_encode($returnmsg);
			exit;
    	} else {
	    	//说明token过期或者token错误，需要重新登录
    		session_destroy();
    		echo json_encode(2);
    		exit;	
	    }
	}
	
	/**
	 * 把传递过来的值存进数组,传入的参数为空时，不在URL上显示
	 */
	public function getdatafun() 
	{
    		$s1=$this->getRequest()->getParam("s1","");//所属省
			$s2=$this->getRequest()->getParam("s2","");//所属市
			$hospital=$this->getRequest()->getParam("hospital","");//所属医院
			$depart=$this->getRequest()->getParam("depart","");//所属科室
			$phone=$this->getRequest()->getParam("phone","");//手机号码
			$email=$this->getRequest()->getParam("email","");//邮箱
			$wechat=$this->getRequest()->getParam("wechat","");//微信
			$name=$this->getRequest()->getParam("name","");//客户姓名
			$gender=$this->getRequest()->getParam("gender","");//客户性别 0:女 1:男
			$zlpaccount=$this->getRequest()->getParam("zlpaccount","");//珍立拍账号
			$nickname=$this->getRequest()->getParam("nickname","");//珍立拍昵称
			$type=$this->getRequest()->getParam("type","");//客户类型
			$source=$this->getRequest()->getParam("source","");//客户来源
			$statime=$this->getRequest()->getParam("statime","");//开始日期
			$endtime=$this->getRequest()->getParam("endtime","");//结束日期
			$rad=$this->getRequest()->getParam("rad","");//1:存在手机号/2:无手机号有邮箱/3:无手机号无邮箱
			$vtype=$this->getRequest()->getParam("vtype","");//拜访类型
			$qq=$this->getRequest()->getParam("qq","");//QQ
			$cid=$this->getRequest()->getParam("cid","");//客户id
			$ctitle=$this->getRequest()->getParam("ctitle","");//客户职称

    		$datamsg=parent::parameteraudit(array($phone,$gender,$type,$source,$rad,$vtype,$qq,$cid),array($s1,$s2,$hospital,$depart,$email,$wechat,$name,$zlpaccount,$nickname,
    			$statime,$endtime,$ctitle),20);
			
			$getdata=array();
			if ($s1!="-1"&&$s1!=""){	
				$getdata['s1']=$datamsg[0];
			}
			if ($s2!="-1"&&$s2!=""){
				$getdata['s2']=$datamsg[1];
			}
			if ($hospital!="-1"&&$hospital!=""){
				$getdata['hospital']=$datamsg[2];
			}
			if ($depart!="-1"&&$depart!=""){
				$getdata['depart']=$datamsg[3];
			}
			if ($phone!=""){
				$getdata['phone']=$phone;
			}
			if ($email!=""){
				$getdata['email']=$datamsg[4];
			}
			if ($wechat!=""){
				$getdata['wechat']=$datamsg[5];
			}
			if ($name!=""){
				$getdata['name']=$datamsg[6];
			}
			if ($gender!="-1"&&$gender!=""){
				$getdata['gender']=$gender;
			}
			if ($zlpaccount!=""){
				$getdata['zlpaccount']=$datamsg[7];
			}
			if ($nickname!=""){
				$getdata['nickname']=$datamsg[8];
			}
			if ($type!="-1"&&$type!=""){
				$getdata['type']=$type;
			}
			if ($source!="-1"&&$source!=""){
				$getdata['source']=$source;
			}
			if ($statime!=""){
				$getdata['statime']=$datamsg[9];
			}
	   		if ($endtime!=""){
				$getdata['endtime']=$datamsg[10];
			}
			if ($rad!=""){
				$getdata['rad']=$rad;
			}
			if ($vtype!="-1"&&$vtype!=""){
				$getdata['vtype']=$vtype;
			} 
			if ($qq!=""){
				$getdata['qq']=$qq;
			}
			if ($ctitle!=""){
				$getdata['ctitle']=$datamsg[11];
			}
			if ($cid!=""){
				$getdata['cid']=$cid;
			}

			//传入的参数为空时，不在URL上显示
			$tmparray=array();
			if(!empty($getdata)){
				foreach ($getdata as $key => $value){
					$tmparray[]=$key."=".$value;
				}
				$urlpara="&".implode("&", $tmparray);
			}else{
				$urlpara="";
			};
			
			$paraarry=array();
			$paraarry['getdata']=$getdata;
			$paraarry['urlpara']=$urlpara;		
			return $paraarry;
	}
}