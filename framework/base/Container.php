<?php
namespace framework\base;

class Container extends Base
{
    protected static $instance;
    protected $_composer;
    protected $_instances;
    protected $_components;
    protected $_delInstanceComponents = [];
    protected $_completeDelInstanceComponents = [];

    protected function init()
    {
        $this->_components = [];
//        $this->_components[SYSTEM_APP_NAME] = $this->_conf;
        $this->_instances = [];
        self::$instance = $this;
    }

    public static function getInstance()
    {
        return self::$instance;
    }


    public function setComposer(Composer $composer)
    {
        $this->_composer = $composer;
    }




//    该方法不应该放这里
    public function appHasComponents($system)
    {
        if (!empty($this->_appConf[$system])) {
            return true;
        }
        return false;
    }

//    向app中添加components
    public function setAppComponents($system, $conf)
    {
        $this->_appConf[$system] = $conf['components'];
        $this->_composer->setAppComposers($system, $conf['composer']);
    }





//    设置组件的配置  做到系统组件和app组件的隔离
    public function setComponentConf($haver, $component, $conf)
    {
        if ($haver == SYSTEM_APP_NAME) {
            $this->_conf[$component] = $conf['default'];
        } else {
            $this->_appConf[$haver][$component] = $conf['app'];
        }
    }

    public function getComponentConf($haver, $component)
    {
        return array(
            'default' => $this->_conf[$component] ?? [],
            'app' => $haver != SYSTEM_APP_NAME ? $this->_appConf[$haver][$component] ?? [] : []
        );
    }

    public function getClassPathByKey($haver, $key)
    {
        return $this->_components[$haver][$key] ?? null;
    }


//    这里使用  commponents的原因是 组件可能删除 所以要获取实际的情况  但是组件不一定实例化
    public function hasComponent($haver, $component)
    {
        if (!empty($this->_components[$haver][$component])) {
            return true;
        }
        return false;
    }


    /**
     * @param $key
     * @param $classPath
     * @param $conf   array('default' => [],'app'=> [])
     * @return bool
     * @throws \Exception
     */
    public function addComponent($system, $key, $classPath, $conf = [])
    {
        if($key&&$classPath)
        {
            $this->_components[$system][$key] = $classPath;
            if($conf)
            {
                $this->setComponentConf($system, $key, $conf);
            }
            unset($conf);
            return true;
        }
        $this->triggerThrowable(new \Exception('components key or classpath can not be empty'));
    }

    public function addComponents($system, $components)
    {
        try
        {
            foreach ($components as $key=>$classPath)
            {
                $this->addComponent($system, $key, $classPath);
            }
            unset($components);
        }
        catch (\Throwable $e)
        {
            $this->triggerThrowable(new \Exception('components add failed ' . $e->getMessage()));
        }
    }

    public function getComponent($haver, $key, $params = [])
    {
        try
        {
            if (!$key) {
                return false;
            }

            if (!empty($this->_instances[$haver][$key])) {
                return $this->_instances[$haver][$key];
            }

            $classPath = $this->getClassPathByKey($haver, $key);
            if ($classPath)
            {
                $instance = new $classPath($this->getComponentConf($haver, $key));

                if ($instance instanceof Component) {
                    $instance->setUniqueId($key);
                    $this->_instances[$haver][$key] = $instance;
                    unset($instance);
                }
                else
                {
                    unset($instance);
                    $this->triggerThrowable(new \Exception('instance' . $classPath . 'have to instance of Component', 500));
                }
            }
            else
            {
                if (COMPOSER && $this->_composer->checkComposer($haver,$key)) {
                    $_params = $this->getComponentConf($haver, $key);
                    $this->_instances[$haver][$key] = $this->_composer->getComposer($haver, $key, array_merge($_params['default'], $_params['app'], $params));
                    $this->unInstall($haver, $key);
                }
                else
                {
                    $this->triggerThrowable(new \Exception("components {$key} not exists", 500));
                }
            }
        }
        catch (\Throwable $e)
        {
            $msg = $e->getMessage();
            // $msg = empty($msg) ? ' maybe this class not instance of Components ' : $msg;
            $this->triggerThrowable(new \Exception( $msg, 500));
        }

        return $this->_instances[$haver][$key];
    }

    public function unInstall($haver, $componentKey, $completeDel = false)
    {
        if ($completeDel) {
            $this->_completeDelInstanceComponents[$haver][] = $componentKey;
        } else {
            $this->_delInstanceComponents[$haver][] = $componentKey;
        }
    }

    public function destroyComponent($haver, $key)
    {
        if(!$key)
            return false;

        unset($this->_components[$haver][$key]);
        unset($this->_instances[$haver][$key]);
    }

    public function destroyComponentsInstance($haver,$key)
    {
        if(!$key)
            return false;

        unset($this->_instances[$haver][$key]);
    }

    public function finish($haver)
    {
        $this->_delInstanceComponents[$haver] = $this->_delInstanceComponents[$haver]?? [];
        $this->_completeDelInstanceComponents[$haver] = $this->_completeDelInstanceComponents[$haver]?? [];
        foreach ($this->_delInstanceComponents[$haver] as $item)
        {
            $this->destroyComponentsInstance($haver, $item);
        }
        foreach ($this->_completeDelInstanceComponents[$haver] as $item)
        {
            $this->destroyComponent($haver ,$item);
        }

        $this->_delInstanceComponents[$haver] = [];
        $this->_completeDelInstanceComponents[$haver] = [];
    }

    public function end()
    {
        foreach ($this->_instances as  $haver => $item) {
            foreach ($item as $key => $value) {
                $this->destroyComponent($haver, $key);
            }
        }
    }
}