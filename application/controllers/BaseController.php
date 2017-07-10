<?php
class BaseController extends Zend_Controller_Action
{
    public function init()
    {
        //初始化数据库适配器
        $url=constant("APPLICATION_PATH") .DIRECTORY_SEPARATOR .'configs' .DIRECTORY_SEPARATOR .'application.ini';
        $dbconfig=new Zend_Config_Ini($url,"mysql");
        $db=Zend_Db::factory($dbconfig->db);
        $db->query('SET NAMES UTF8');
        Zend_Db_Table::setDefaultAdapter($db);
        
       //前台页面controller集合
        $webarr=array("/Employee/","/Medicrm/","/Systemset/");
        //初始化状态；
        $webool=false;
        //得到当前请求页面地址
        $aa=$_SERVER['REQUEST_URI'];
        //检索当前请求页面是否是后台controller中的一个,是的话状态改为true
        foreach ($webarr as $value) 
        {
            if (stristr($aa,$value))
            {
                $webool=true;
            }
        }
        //如果是访问的后台管理相关controller，加入后台session相关判断
        if($webool)
        {
            if(!isset($_SESSION)) //如果session没有开启则开启session
            {
                session_start();
            }
            if (empty($_SESSION['loginuser']))//是否处于登陆状态，否则转入登陆页面
            {
                echo "<script type='text/javascript'>window.location.href='/index/index';</script>";
                exit();
            }
        }
        
    }
    //参数判断
    public function parameteraudit($params,$strparams,$paramnum=0)
    {
    	if(!empty($params))
    	{
    		$pamsg=false;
    		if(count(array_diff($_REQUEST,array("")))<=$paramnum)//参数个数
    		{
    			foreach ($params as $param)
    			{
    				if(is_int($param))//是否整数
    				{
    					$pamsg=true;
    				}
    				else if(is_string($param)&&!empty($param)) //是否数字字符串
    				{	
    					$pamsg=is_numeric($param);
    				}
    				else if (empty($param))
    				{
    					$pamsg=true;//判断是否为""
    				}
    				//如果判断参数不合法就终止循环，不再判断其他参数是否合法
    				if(!$pamsg)
    				{
    					break;
    				}
    			}
    		}
    		//根据上面判断数据，最终结果false就输出错误到页面上
    		if(!$pamsg)
    		{
    			echo "Without permission";
    			exit();
    		}
    	}
        
    	if(!empty($strparams))
    	{
    		$returnmsg=array();//字符串过滤数据返回
    		//过滤字符串
    		foreach ($strparams as $str)
    		{
    			$returnmsg[]=addslashes(htmlspecialchars($str));
    		}
    		return $returnmsg;
    	}
    }
 	//后台token错误信息调用
    public function nopermissions()
    {
        session_destroy();
        echo "<script>window.location.href='/index/index';</script>";
        exit();
    }
}

?>