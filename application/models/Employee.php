<?php
/**
 * 员工表模型
 *
 */
class Application_Model_Employee extends Zend_Db_Table
{
    protected $_name='crm_employee';//员工表
    protected $_primary='crm_EmployID';//表id
    

    /**
     * 得到当前用户当天的未处理的前三条拜访信息
     * @param unknown_type $userid
     * @return multitype:array
     */
    public function userplanmsg($userid)
    {
        $db=self::getAdapter();
        $date= date('Y-m-d',strtotime('+1day'));       
        $datetime=date("Y-m-d H:i:s");
        $where="SELECT crm_ID,crm_VisitTime,crm_VisitType FROM crm_clientvisitrecord WHERE crm_EmployID=".$userid." AND crm_VisitTime>='$datetime' AND crm_VisitTime<'$date' AND crm_VisitStatus=0 ORDER BY crm_VisitTime DESC";
        $data=$db->query($where)->fetchAll();
        return $data;
    }
    
    /**
     * 得到用户日增长数据
     */
    public function usergrowth()
    {
        $db=self::getAdapter();
        $date=date("Y-m-d");
        $where="SELECT COUNT(crm_ClientID) AS num FROM crm_clientmsg WHERE crm_RegTime LIKE '$date%'";
        $growthnum=$db->query($where)->fetchAll();
        $result=array();
        $result['growthnum']=$growthnum[0]['num'];
        for($i=1;$i<=24;$i++)
        {
            if($i<10)
            {
                $newi=" 0$i";
            }
            else 
            {
                $newi=" $i";
            }
            $hourdata=self::hourgrowth($date.$newi);
            $result["$newi"]=$hourdata;
        }
        return $result;
    }
    
    public function hourgrowth($hour)
    {
        $db=self::getAdapter();
        $where="SELECT COUNT(crm_ClientID) AS num FROM crm_clientmsg WHERE crm_RegTime LIKE '$hour%'";
        $data=$db->query($where)->fetchAll();
        return $data[0]['num'];
    }
    
    /**
     * 得到用户活跃度数据信息
     * @param array $seltype
     */
    public function useractive($seltype)
    {
        $db=self::getAdapter();
        $today=date("Y-m-d");
        $sql="SELECT COUNT(crm_ClientID) sum FROM crm_clientmsg WHERE 1=1";
        $usercount=$db->query($sql)->fetchAll();
        switch($seltype)
        {
            case 0://用户当天活跃度
                $selsql=" AND crm_LoginTime LIKE '%".$today."%'";
                break;
            case 1://1-3个月的用户活跃度
                $selsql=" AND crm_LoginTime between '".date('Y-m-d',strtotime('-3 month'))."' AND '".$today."'";
                break;
            case 2://3-6个月的用户活跃度
                $selsql=" AND crm_LoginTime between '".date('Y-m-d',strtotime('-6 month'))."' AND '".date('Y-m-d',strtotime('-3 month'))."'";
                break;
            case 3://6-12个月的用户活跃度
                $selsql=" AND crm_LoginTime between '".date('Y-m-d',strtotime('-12 month'))."' AND '".date('Y-m-d',strtotime('-6 month'))."'";
                break;
            case 4://1年及一年以上的用户活跃度
                $selsql=" AND crm_LoginTime<'".date('Y-m-d',strtotime('-12 month'))."'";
                break;
            case 5://用户地区分布
                $Employeeql="SELECT UserCity2,usernum,round((usernum/".$usercount[0]['sum'].")*100,2) as percent From (SELECT distinct substring_index(crm_ClientCity,',',1)as UserCity2,count(crm_ClientID)as usernum FROM crm_clientmsg GROUP BY UserCity2) c";
                $userdata=$db->query($Employeeql)->fetchAll();
                return $userdata;
                break;
        }
        $typedata=$db->query($sql.$selsql)->fetchAll();
        $activenum=round(($typedata[0]['sum']/$usercount[0]['sum'])*100,2);
        return $activenum;
    }
	/**
	 * 生成用户token
	 */
	public function generatetoken(){		
		$charid = strtoupper(md5(uniqid(mt_rand(), true)));
    	$hyphen = chr(45);// "-"
    	$uuid = substr($charid, 0, 8).$hyphen
		    	.substr($charid, 8, 4).$hyphen
		    	.substr($charid,12, 4).$hyphen
		    	.substr($charid,16, 4).$hyphen
		    	.substr($charid,20,12);
		return $uuid;
	}
	
	/**
	 * token验证
	 * @param int $userid
	 * @param string $token
	 */
	public function tokenverify($userid,$token){
		$state=0;
    	$db=self::getAdapter();
    	$where=$db->quoteInto('crm_EmployID=?', $userid).$db->quoteInto(' And crm_UserToken=?', $token);
	    $userinfo=self::fetchAll($where)->toArray();
		if(count($userinfo)>0){
	    	$now=date('Y-m-d H:i:s');
	    	if($now>$userinfo[0]['crm_EffectTime']){
	    		$state=1;//用户token过期
	    		}
	    }else{
	    	$state=2;//用户token错误
	    	}
    	return $state;
	}
	
		
}