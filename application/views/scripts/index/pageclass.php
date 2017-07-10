<?php 
class PageClass
{
    private $style="border:1px solid #E1E2E3;padding:5px 10px;margin-left:5px;";
    private $fontstyle="padding:5px 10px;margin-left:5px;font-weight:bold;";
    function pagemsg($spage,$cpage,$pageurl,$urlpara)
    {
        $pagemsg="";
        if($cpage!=1)
        {
            $pagemsg.="<a href='$pageurl?cpage=1".$urlpara."' style='".$this->style."'>首页</a>";
            $pagemsg.="<a href='$pageurl?cpage=".($cpage-1)."$urlpara' style='".$this->style."'>上一页</a>";
        }
        
        if($spage<=10)
        {
            for($i=1;$i<=$spage;$i++)
            {
                if($i!=$cpage)
                {
                    $pagemsg.="<a href='$pageurl?cpage=".$i."$urlpara' style='".$this->style."'>$i</a>";
                }
                else
                {
                    $pagemsg.="<span style='".$this->fontstyle."'>$i</span>";
                }
            }
        }
        else
        {
            $start=$cpage-5<=0?1:$cpage-5;
            $end1=$cpage+4<10?10:$cpage+4;
            $end=$end1>=$spage?$spage:$end1;
            if($end<$end1)
            {
                $start-=$end1-$end;
            }
            for($i=$start;$i<=$end;$i++)
            {
                if($i!=$cpage)
                {
                    $pagemsg.="<a href='$pageurl?cpage=".$i."$urlpara' style='".$this->style."'>$i</a>";
                }
                else
                {
                    $pagemsg.="<span style='".$this->fontstyle."'>$i</span>";
                }
            }
        }
        if($cpage!=$spage)
        {
            $pagemsg.="<a href='$pageurl?cpage=".($cpage+1)."$urlpara' style='".$this->style."'>下一页</a>";
            $pagemsg.="<a href='$pageurl?cpage=".$spage."$urlpara' style='".$this->style."'>末页</a>";
        }
        return $pagemsg;
    }
}
?>