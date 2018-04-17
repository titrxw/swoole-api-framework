<?php

namespace framework\components\page;
use framework\base\Component;

class Page extends Component
{
    protected $_needSaveArgsFromCurrentUrl=array('act','m');
    protected $_pageBtnNum;
    protected $_count=0;
    protected $_pageCount;
    protected $_separator;
    protected $_offset=0;
    protected $_pageKey;
    protected $_param=[];
    protected $_url='';
    protected $_curPage=0;
    protected $_protocol;

    protected function init()
    {
        $this->unInstall();
    }

    protected function initNeedData($count,$pageCount,$param=[])
    {
        $this->_count=intval($count)>0?$count:0;
        $pageCount = (int) $pageCount;
        if ($pageCount <= 0)
        {
            $pageCount = $this->getValueFromConf('pageCount',10);
        }

        $this->_pageCount=$pageCount;
        $this->_pageKey=$this->getValueFromConf('pageKey','page');
        $this->_param=is_array($param)?$param:$this->_param;
        $this->_protocol = $this->getValueFromConf('protocol', 'http');
        $this->_pageBtnNum = $this->getValueFromConf('pageBtnNum', 6);
        $paramkeys=array_keys($this->_param);
        foreach ($this->_needSaveArgsFromCurrentUrl as $key=>$item)
        {
            if(in_array($item,$paramkeys))
                unset($this->_needSaveArgsFromCurrentUrl[$key]);
        }
        unset($paramkeys, $param);
    }

    /**
     * 获取当前的页数
     * @return unknown
     */
    private function getCurrentPage()
    {
        if(!$this->_curPage)
            $this->_curPage=(int) $_GET[$this->_pageKey] ?? 1;
        return $this->_curPage;
    }

	/**
	 * @return unknown|string
	 * 获取当前的访问连接
	 */
    private function getCurrentUrl()
    {
        if($this->_url)
            return $this->_url;

        $url = $this->getComponent(SYSTEM_APP_NAME, 'url');

        $type = $url->getType();
        if ($type === '?')
        {
            $this->_separator = '&';
        }
        else
        {
            $this->_separator = '/';
        }
        if(!$this->_separator||$this->_separator=='&')
        {
            /**
             * 表示使用的是默认的链接的方式
             */
            $this->_url=$_SERVER['HTTP_HOST'] . $_SERVER['URL'] . '?';
            $urlArgs=explode($this->_separator, $url->getPathInfo());
	        $urlArgs=array_unique($urlArgs);
            foreach ($this->_needSaveArgsFromCurrentUrl  as $nitem)
            {
                $len=strlen($nitem);
                foreach ($urlArgs as $k=>$item)
                       if(substr($item, 0,$len)==$nitem)
                       {
	                       $this->_url .= $item . $this->_separator;
	                       unset($urlArgs[$k]);
                       }
            }
            unset($urlArgs);
            foreach ($this->_param as  $key=>$item)
                if(!is_integer($key))
                    $this->_url.=$key.'='.$item.$this->_separator;
            $this->_separator='=';
        }
        else 
        {
        	$tmpparam='';
	        foreach ($this->_param as  $key=>$v)
		        if(!is_integer($key))
			        $tmpparam.=$key.$this->_separator.$v.$this->_separator;
	             else
		             $tmpparam.=$v.$this->_separator;
        	$tmpargs=explode($this->_separator,$_SERVER['REQUEST_URI']);
            $tmpurl='';
			foreach ($this->_needSaveArgsFromCurrentUrl as $item)
			{
				foreach ($tmpargs as $k=>$tmparg)
				{
					if($item==$tmparg)
					{
						$tmpurl .= $item . $this->_separator . $tmpargs[$k + 1] . $this->_separator;
						unset($tmpargs[$k]);
						unset($tmpargs[$k + 1]);
					}
				}
			}
			$curRoute = $url->getCurRoute();
            $this->_url=$_SERVER['HTTP_HOST'] . $_SERVER['URL'].$this->_separator  .$curRoute['system'] . $this->_separator .$curRoute['controller'] . $this->_separator . $curRoute['action']. $this->_separator.$tmpparam.$tmpurl;
            unset($curRoute);
        }
        unset($url);
        return $this->_url;
    }
    /**
     * 绘制上一页的标签
     */
    private function pagePrev()
    {
        if($this->getCurrentPage()>1)
            return "<a href=\"" . $this->_protocol . "://".$this->getCurrentUrl().$this->_pageKey.$this->_separator.($this->getCurrentPage()-1)."\">上一页</a>";
        else
            return '';
    }
    /**
     * 绘制下一页的标签
     */
    private function pageNext()
    {
        if(ceil($this->_count/$this->_pageCount)>$this->getCurrentPage())
            return "<a href=\"" . $this->_protocol . "://".$this->getCurrentUrl().$this->_pageKey.$this->_separator.($this->getCurrentPage()+1)."\">下一页</a>";
        else
            return '';
    }
    /**
     * 绘制第一页的标签
     */
    private function start()
    {
        if($this->getCurrentPage()>1&&(ceil($this->_count/$this->_pageCount))>$this->_pageBtnNum)
            return "<a href=\"" . $this->_protocol . "://".$this->getCurrentUrl().$this->_pageKey.$this->_separator."1 \">首页</a>";
        return '';
    }
    /**
     * 绘制最后一个的标签
     */
    private function end()
    {
        $count=ceil($this->_count/$this->_pageCount);
        if($count>$this->_pageBtnNum&&$this->getCurrentPage()< $count)
            return "<a href=\"" . $this->_protocol . "://".$this->getCurrentUrl().$this->_pageKey.$this->_separator.$count."\">尾页</a>";
        return '';
    }
    /**
     * 绘制分页标签
     */
    public function out($count = 0,$pageCount = 10,$param=[])
    {
        $this->initNeedData($count, $pageCount, $param);
        $element='<div>';
        $element.=$this->start();
        $element.=$this->pagePrev();
        $pagecount=ceil($this->_count/$this->_pageCount);
        $num=$pagecount<$this->_pageBtnNum?$pagecount:$this->_pageBtnNum;
        $start=$pagecount>$this->_pageBtnNum?$this->getCurrentPage():1;
        $start=($pagecount-$start+1)<$this->_pageBtnNum?($pagecount-$this->_pageBtnNum+1):$start;
        $start=$start>0?$start:1;
        for($i=0;$i<$num;$i++)
            $element.="<a style=\"width: 29px;height:29px;text-align:center;border:1px solid ;display:inline-block;line-height:29px;text-decoration:none;color:black;\" href=\"" . $this->_protocol . "://".$this->getCurrentUrl().$this->_pageKey.$this->_separator.($start+$i)."\">".($start+$i)."</a>";
        $element.=$this->pageNext();
        $element.=$this->end();
        $element.="</div>";
        return $element;
    }
}

