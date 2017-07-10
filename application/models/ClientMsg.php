<?php
/**
 * 客户信息表模型
 *
 */
class Application_Model_ClientMsg extends Zend_Db_Table
{
    protected $_name='crm_clientmsg';//客户信息表
    protected $_primary='crm_ClientID';//表id
        
    /**
     * 得到用户列表，用户列表总数
     * @param arrray $getdata
     * @param int $start
     * @param int $num
     * @return array
     */
    public function clientlstsum($getdata,$start=NULL,$num=NULL)//当$start和$num不被传入时默认为NULL
    {
    	$db=self::getAdapter();
    	//根据$num是否为空选择sql语句
    	if (empty($num)){
    		$sql="SELECT COUNT(crm_ClientID) sum FROM crm_clientmsg WHERE 1=1";
    	}else {
    		$sql="SELECT * FROM crm_clientmsg WHERE 1=1";
    	}
    		
    	if(!empty($getdata['s1'])&&empty($getdata['s2'])){//选择省未选择市的情况
    		$province=mb_substr($getdata['s1'],0,2,'utf-8');
			$sql.=" AND crm_ClientCity LIKE '%".$province."%'";
		}
		if(!empty($getdata['s1'])&&!empty($getdata['s2'])){//同时选择省市的情况		
			$city=mb_substr($getdata['s2'],0,2,'utf-8');	
			$sql.=" AND crm_ClientCity LIKE '%".$city."%'";			
		}
    	if(!empty($getdata['hospital'])){
			$sql.=" AND crm_ClientHospital='".$getdata['hospital']."'";
		}
		if(!empty($getdata['depart'])){
			$sql.=" AND crm_ClientSection='".$getdata['depart']."'";
		}
   		if(!empty($getdata['phone'])){
			$sql.=" AND crm_ClientPhone LIKE'%".$getdata['phone']."%'";
		}
    	if(!empty($getdata['email'])){
			$sql.=" AND crm_ClientEmail LIKE '%".$getdata['email']."%'";
		}
    	if(!empty($getdata['wechat'])){
			$sql.=" AND crm_WeChat LIKE '%".$getdata['wechat']."%'";
		}
		if(!empty($getdata['name'])){
			$sql.=" AND crm_ClientName LIKE '%".$getdata['name']."%'";
		}
    	if(!empty($getdata['gender'])||$getdata['gender']=="0"){
			$sql.=" AND crm_ClientGender='".$getdata['gender']."'";
		}
    	if(!empty($getdata['zlpaccount'])){
			$sql.=" AND crm_ZlpAccount LIKE '%".$getdata['zlpaccount']."%'";
		}
    	if(!empty($getdata['nickname'])){
			$sql.=" AND crm_ZlpNickName LIKE '%".$getdata['nickname']."%'";
		}
    	if(!empty($getdata['type'])||$getdata['type']=="0"){
			$sql.=" AND crm_ClientType='".$getdata[type]."'";
		}
    	if(!empty($getdata['source'])||$getdata['source']=="0"){
			$sql.=" AND crm_ClientSource='".$getdata['source']."'";
		}
   		if (!empty($getdata['statime'])&&!empty($getdata['endtime']))
		{	
			$sql.=" AND crm_LoginTime BETWEEN '".$getdata['statime']."' AND '".$getdata['endtime']."'";
		}
    	if(!empty($getdata['rad']))
		{
			switch ($getdata['rad']){
				case 1:
					$sql.=" AND crm_ClientPhone!=''";
					break;
				case 2:
					$sql.=" AND crm_ClientEmail!=''";
					break;
				case 3:
					$sql.=" AND crm_ClientPhone='' AND crm_ClientEmail!=''";
					break;
				case 4:
					$sql.=" AND crm_ClientPhone='' AND crm_ClientEmail=''";
					break;
			}
		}
		//$num是否为空返回$sum或$userlst
		if (empty($num)){
			$sum=$db->query($sql)->fetchAll();
			return $sum;
		}else {
			$sql.=" ORDER BY crm_ClientID DESC LIMIT $start,$num";
			$userlst=$db->query($sql)->fetchAll();
			return $userlst;
		}
    }
    
    
    /**
     * 用户信息同步
     * @param unknown_type $synctype
     * @param unknown_type $requesttime
     */
    public function usermsgsync($synctype,$requesttime)
    {
        $returnmsg=0;
        $db=self::getAdapter();
        $requestname="ldfadmin";//请求用户名
        $code=md5("科技有限公司");//请求密钥，固定字符串
        $encry=md5($requestname.$requesttime.$synctype.$code."four");
        $url="http://localhost.com/Apicrm/usersync?requestname=$requestname&requesttime=$requesttime&code=$code&datatype=$synctype&encrypt=$encry";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        if($output==false)
        {
        	return 1;
        }
        $res=json_decode($output,true);
        $res1=$res['data'];
        //将获取的接口数据保存到crm客户表中
        try
        {
            foreach ($res1 as $r)
            {
                $data=array(
                    "crm_ZlpAccount"=>$r['UserName'],//珍立拍账号
                    "crm_ZlpID"=>$r['UserID'],//珍立拍ID
                    "crm_ClientName"=>$r['FullName'],//客户姓名
                    "crm_ZlpNickName"=>$r['NickName'],//珍立拍昵称
                    "crm_ClientGender"=>$r['UserSex'],//客户性别
                    "crm_ClientPhone"=>$r['LinkPhone'],//客户联系电话
                    "crm_ClientEmail"=>$r['LinkMail'],//客户邮箱
                    "crm_ClientHospital"=>$r['HospitalName'],//客户所在医院
                    "crm_ClientSection"=>$r['PartmentName'],//客户所在科室
                    "crm_ClientTitle"=>$r['DoctorTitle'],//客户职称
                    "crm_ClientCity"=>$r['UserCity'],//客户所在城市
                    "crm_ClientType"=>empty($r['UserType'])?0:$r['UserType'],//客户类型
                    "crm_ClientSource"=>empty($r['UserSource'])?0:$r['UserSource'],//客户来源
                    "crm_WeChat"=>$r['WebChat'],//客户微信号
                    "crm_LoginTime"=>empty($r['LastTime'])?null:$r['LastTime'],//客户最后登录时间
                    "crm_RegTime"=>empty($r['RegTime'])?null:$r['RegTime'],//客户注册时间
                    "crm_UpdateTime"=>empty($r['UserUpdateTime'])?null:$r['UserUpdateTime'],//客户信息更新时间
                    "crm_EmployID"=>$_SESSION['loginuser']['crm_EmployID']//客户所属员工ID
                );
                //查询当前用户数据库中是否存在
                $where="crm_ZlpID={$r['UserID']}";
                $crmuser=$clientModel->fetchAll($where)->toArray();
                if($synctype==0)//新注册用户信息同步
                {
                    if(empty($crmuser))
                    {
                        $clientModel->insert($data);
                    }
                    else
                    {
                        $clientModel->update($data, $where);
                    }
                }
                else if($synctype==1)//已有用户信息更新
                {
                    $clientModel->update($data, $where);
                }
                else if($synctype==2)//具体用户信息更新
                {
                    $clientModel->update($data, $where);
                }
            }
        }
        catch (Exception $e)
        {
           $returnmsg=1;
        }
        return $returnmsg;
    }
}