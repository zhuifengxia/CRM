<?php
class Application_Model_SystemEmail extends Zend_Db_Table
{
	protected $_name='crm_systememail';
    protected $_primary='crm_SystemID';//系统邮箱表
	
	//得到所有的系统邮箱信息列表
	public function emaildatalst()
	{
		$emaildatalst=self::fetchAll()->toArray();
		return $emaildatalst;
	}
	
	/*
	 方法说明：根据邮箱ID对数据进行相关操作
	 参数：
	 $emailid:邮箱ID；
	 $typeid：操作类型0：根据ID得到目标邮箱详细信息；1删除目标邮箱信息
	*/
	public function emaildetail($emailid,$typeid)
	{
		$datawhere="crm_SystemID=".$emailid;
		if($typeid==1)
		{
			self::delete($datawhere);
		}
		else
		{
			$emaildetail=self::fetchAll($datawhere)->toArray();
			return $emaildetail[0];
		}
	}
	
	/*
	 方法说明：添加/编辑邮箱信息
	 参数：
	 $emailid:邮箱ID，编辑邮箱所用
	 $emaildata:需要修改/添加的邮箱信息
	 */
	public function addemail($emailid,$emaildata)
	{
	    if(empty($emailid))
	    {
	        self::insert($emaildata);
	    }
	    else 
	    {
	        $datawhere="crm_SystemID=".$emailid;
	        self::update($emaildata,$datawhere);
	    }
	    return 0;
	}
}
