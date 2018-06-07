<?php
namespace Common\Util;
/**
 *
 * @author user
 *
 */
class AjaxPage{
		
	public $pagecount;//获取每页显示记录数
	public $pagenum;//获取当前页码
	public $pagemax=5;//获取最大可显示页码数
	public $pageoffset=0;//开始的页码
	public $pagesum=0;//获取总页数
	public $pageCenter=0;//中间偏移数
	public $pagefirst=0;//显示的第一页码数
	public $pagelast=0;//显示的最后的页码数
	
	public $count;//总数据条数
	
		public function __construct($sum=0,$pagenum=0,$perPage=0)
		{
			//获取每页显示记录数
			$this->pagecount = $perPage;
			//获取当前页码
			$this->pagenum = $pagenum;
			
			//获取总数据条数
			$this->count   = $sum;
			//计算出偏移量
			$this->pageoffset = ($pagenum - 1) * $this->pagecount;
			//获取总页数
			$this->pagesum = ceil($sum / $this->pagecount);
		}
		
		/**
		 *
		 * @return multitype:number
		 */
		public function getCounts()
		{
			
			
			
			if($this->pagesum > $this->pagemax){
				//计算出中间页码数
				$this->pageCenter = ceil($this->pagemax / 2);
				//计算出第一个可显示的页码和最后一个显示的页码
				if($this->pagenum > $this->pageCenter){
					$this->pagefirst = $this->pagenum - $this->pageCenter + 1;
					if($this->pagenum > $this->pagesum - $this->pageCenter){
						$this->pagefirst = $this->pagesum - $this->pagemax + 1;
					}
				}else{
					$this->pagefirst = 1;
				}
				$this->pagelast = $this->pagefirst + $this->pagemax - 1;
			}else{
				$this->pagefirst = 1;
				$this->pagelast  = $this->pagesum;
			}
			
			
			return $datas = array(
            	             'pageFirst' => $this->pagefirst,//显示的第一页
            	             'pageAll'   => $this->pagesum,//总页数
			   'pageMax'   => $this->pagelast,//显示的总页量数
			   'pageOffset'=> $this->pageoffset,//从哪个地方开始取数据
			   'curPage'   => $this->pagenum,//当前页数
			   'perPage'   => $this->pagecount,//每页显示的记录数
			   'counts'	   => $this->count,//总数据条数
			);
		}

}