<?php
namespace framework\components\url;
use framework\base\Component;

class Url extends Component
{
    protected $_defaultType;
    protected $_defaultSystem;
    protected $_defaultController;
    protected $_defaultAction;
    protected $_defaultSystemKey;
    protected $_defaultControllerKey;
    protected $_defaultActionKey;
    protected $_defaultSeparator;
    protected $_currentModule;
    protected $_curRoute = [];

    public function run()
    {
        $this->_currentModule = $this->formatUrl();
        return $this->_currentModule;
    }

    public function getCurrentModule()
    {
        return $this->_currentModule;
    }

    protected function formatUrl()
    {
        $type = $this->getType();
        if ($type === '?')
        {
            $system = empty($_GET[$this->getDefauktSystemKey()]) ? $this->getDefaultSystem() : $_GET[$this->getDefauktSystemKey()];
            $urlInfo =  array(
                'system' => $system,
                'controller' => empty($_GET[$this->getDefaultControllerKey()]) ? $this->getDefaultController() : $_GET[$this->getDefaultControllerKey()],
                'action' => empty($_GET[$this->getDefaultActionKey()]) ? $this->getDefaultAction() : $_GET[$this->getDefaultActionKey()]
            );
        }
        else
        {
            $routerKey = $this->getValueFromConf('routerKey');
            if ($routerKey)
            {
                $query = empty($_GET[$routerKey]) ? '' : $_GET[$routerKey];
            }
            else
            {
                $query = $this->getPathInfo();
                $query = ltrim($query,'/');
            }
            $tmpQuery = explode($this->getSeparator(), $query);
            $keyStart = 0;
            if (in_array($tmpQuery[0], $this->getValueFromConf('systems',[]))) {
                $system = $tmpQuery[0];
                unset($tmpQuery[0]);
                $keyStart = 1;
            } else {
                $system = $this->getDefaultSystem();
            }
            $urlInfo =  array(
                'system' => $system,
                'controller' => empty($tmpQuery[0 + $keyStart]) ? $this->getDefaultController() : $tmpQuery[0 + $keyStart],
                'action' => empty($tmpQuery[1 + $keyStart]) ? $this->getDefaultAction() : $tmpQuery[1 + $keyStart]
            );
            if (!empty($tmpQuery[0]) && $tmpQuery[0] === 'favicon.ico') {
//                处理图标
                return false;
            }
            $count = count($tmpQuery);
            $_GET = [];
            for($i=2 + $keyStart;$i < $count; $i+=2)
            {
                $_GET[$tmpQuery[$i]] = !isset($tmpQuery[$i+1]) ?  '' : $tmpQuery[$i+1];
            }
            unset($tmpQuery);
        }
        $this->_curRoute = $urlInfo;
        unset($urlInfo);

        return $this->_curRoute;
    }

    public function getPathInfo()
    {
        return $_SERVER['PATH_INFO'] ?? '';
    }

    public function getCurRoute()
    {
        return $this->_curRoute;
    }

    public function getType()
    {
        if(!$this->_defaultType)
        {
            $this->_defaultType = $this->getValueFromConf('type', '?');

            if(!in_array($this->_defaultType,array('/','?'))) {
                $this->_defaultType = '?';
            }
        }
        return $this->_defaultType;
    }

    protected function getSeparator()
    {
        if(!$this->_defaultSeparator)
        {
            $this->_defaultSeparator = $this->getValueFromConf('separator', '/');
        }
        return $this->_defaultSeparator;
    }

    protected function getDefaultController()
    {
        if (!$this->_defaultController)
        {
            $this->_defaultController = $this->getValueFromConf('defaultController', 'index');
        }
        return $this->_defaultController;
    }

    protected function getDefaultSystem()
    {
        if (!$this->_defaultSystem)
        {
            $this->_defaultSystem = $this->getValueFromConf('defaultSystem');
        }
        return $this->_defaultSystem;
    }

    protected function getDefaultAction()
    {
        if (!$this->_defaultAction)
        {
            $this->_defaultAction = $this->getValueFromConf('defaultAction', 'index');
        }
        return $this->_defaultAction;
    }

    protected function getDefauktSystemKey()
    {
        if (!$this->_defaultSystemKey)
        {
            $this->_defaultSystemKey = $this->getValueFromConf('systemKey', 's');
        }
        return $this->_defaultSystemKey;
    }

    protected function getDefaultControllerKey()
    {
        if (!$this->_defaultControllerKey)
        {
            $this->_defaultControllerKey = $this->getValueFromConf('controllerKey', 'm');
        }
        return $this->_defaultControllerKey;
    }

    protected function getDefaultActionKey()
    {
        if(!$this->_defaultActionKey)
        {
            $this->_defaultActionKey = $this->getValueFromConf('actionKey', 'act');
        }
        return $this->_defaultActionKey;
    }


    public function createUrl($url)
    {
        $tmpUrl = $_SERVER['HTTP_HOST'] . $_SERVER['URL'] . '?';
        if ($this->getType() === '?')
        {
            if(is_array($url))
            {
                foreach ($url as $key=>$item)
                {
                    $tmpUrl .= $key . '=' . $item . '&';
                }
                $tmpUrl = trim($tmpUrl, '&');
            }
            else
            {
                $tmpUrl .= $url;
            }
        }
        else
        {
            $tmpUrl .= $url;
        }

        unset($urlModule, $url);
        return $tmpUrl;
    }
}