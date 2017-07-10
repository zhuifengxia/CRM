<?php
/**
 * 
 *客户回访记录表模型
 *
 */
class Application_Model_ClientVisitrecord extends Zend_Db_Table
{
    protected $_name='crm_clientvisitrecord';//客户回访记录表
    protected $_primary='crm_ID';//表id
    
	/**
	 * 拜访客户列表，拜访客户列表总数
	 * @param array $getdata
	 * @param int $start
	 * @param int $num
	 * @return array
	 */
    public function visitlistsum($getdata,$start=NULL,$num=NULL){
    	$db=self::getAdapter();
   		 //根据$num是否为空选择sql语句
    	if (empty($num)){
    		$sql="SELECT COUNT(v.crm_ID) AS num FROM crm_clientvisitrecord v,crm_clientmsg c WHERE c.crm_ClientID=v.crm_ClientID AND v.crm_EmployID={$_SESSION['loginuser']['crm_EmployID']}";
    	}else {
    		$sql="SELECT v.*,c.crm_ClientName FROM crm_clientvisitrecord v LEFT JOIN crm_clientmsg c ON c.crm_ClientID=v.crm_ClientID WHERE 1=1";
    	}
    	
   		if (!empty($getdata['statime'])&&!empty($getdata['endtime'])){
			$sql.=" AND crm_VisitTime BETWEEN '".$getdata['statime']."' AND '".$getdata['endtime']."'";
		}
   		if(!empty($getdata['name'])){
			$sql.=" AND crm_ClientName LIKE '%".$getdata['name']."%'";
		}
    	if(!empty($getdata['vtype'])||empty($getdata['vtype'])=="0"){
			$sql.=" AND crm_VisitType='".$getdata['vtype']."'";
		}
		
   		 //根据$num是否为空返回$sum或$userlst
		if (empty($num)){
			$sum=$db->query($sql)->fetchAll();
			return $sum;
		}else {
			$sql.=" ORDER BY crm_ID DESC LIMIT $start,$num";
			$userlst=$db->query($sql)->fetchAll();
			return $userlst;
		}
    
    }
   /**
    * 反馈信息列表，反馈信息列表总数
    * @param array $getdata
    * @param int $start
    * @param int $num
    */ 
	public function feedbacklstsum($getdata,$start=NULL,$num=NULL){
		$db=self::getAdapter();
     	//根据$num是否为空选择sql语句
    	if (empty($num)){
    		$sql="SELECT COUNT(crm_VisitBackMsg) AS num FROM crm_clientvisitrecord v,crm_clientmsg c WHERE c.crm_ClientID=v.crm_ClientID";
    		
    	}else {
    		$sql="SELECT v.*,c.crm_ClientName FROM crm_clientvisitrecord v LEFT JOIN crm_clientmsg c ON c.crm_ClientID=v.crm_ClientID WHERE 1=1";
    	}
    	
   	    if(!empty($getdata['statime'])&&!empty($getdata['endtime'])){
			$sql.=" AND v.crm_VisitTime BETWEEN '".$getdata['statime']."' AND '".$getdata['endtime']."'";
		}
   		if(!empty($getdata['name'])){
			$sql.=" AND c.crm_ClientName LIKE '%".$getdata['name']."%'";
		}
		
   		 //根据$num是否为空返回$sum或$userlst
		if (empty($num)){
			$sum=$db->query($sql)->fetchAll();
			return $sum;
		}else {
			$sql.=" ORDER BY v.crm_ID DESC LIMIT $start,$num";
			$feedbacklst=$db->query($sql)->fetchAll();
			return $feedbacklst;
		}
    
    }
    
}
