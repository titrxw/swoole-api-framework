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
        $this->_components[SYSTEM_APP_NAME] = $this->_conf;
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

    public function appHasComponents($system)
    {
        if (!empty($this->_appConf[$system])) {
            return true;
        }
        return false;
    }

    public function setAppComponents($system, $conf)
    {
        $this->_appConf[$system] = $conf['components'];
        $this->_composer->setComposers($system, $conf['composer']);
        // $this->_appConf[$system]['composer'] = $conf['composer'];
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
                if ($system == SYSTEM_APP_NAME) {
                    $this->_conf[$key] = $conf['default'] ?? [];
                } else {
                    $this->_appConf[$system][$key] = $conf['app'] ?? [];
                }
            }
            unset($conf);
            return true;
        }
        $this->triggerException(new \Exception('components key or classpath can not be empty'));
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
        catch (\Exception $e)
        {
            $this->triggerException(new \Exception('components add failed ' . $e->getMessage()));
        }
    }

    public function setComponentConf($haver, $component, $conf)
    {
        $this->_conf[$component] = $conf['default'];
        $this->_appConf[$haver][$component] = $conf['app'];
    }

    public function getComponentConf($haver, $component)
    {
        if ($haver == SYSTEM_APP_NAME && $component !== 'dispatcher') {
            $haver = $this->getComponent(SYSTEM_APP_NAME, 'dispatcher')->getSystem();
        }
        return array(
            'default' => $this->_conf[$component] ?? [],
            'app' => $this->_appConf[$haver][$component] ?? []
        );
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
                    $this->triggerException(new \Exception('instance' . $classPath . 'have to instance of Component', 500));
                }
            }
            else
            {
                if (COMPOSER && $this->_composer->checkComposer($haver,$key)) {
                    $_params = $this->getComponentConf($haver, $key);
                    $this->_instances[$haver][$key] = $this->_composer->getComposer($haver, $key, array_merge($_params['default'], $_params['app'], $params));
                }
                else
                {
                    $this->triggerException(new \Exception("components {$key} not exists", 500));
                }
            }
        }
        catch (\Exception $e)
        {
            $msg = $e->getMessage();
            // $msg = empty($msg) ? ' maybe this class not instance of Components ' : $msg;
            $this->triggerException(new \Exception( $msg, 500));
        }
        catch (\Error $e)
        {
            $msg = $e->getMessage();
            // $msg = empty($msg) ? ' maybe this class not instance of Components ' : $msg;
            $this->triggerException(new \Error( $msg, 500));
        }

        return $this->_instances[$haver][$key];
    }

    public function unInstall($haver, $componentKey, $completeDel = true)
    {
        if ($completeDel) {
            $this->_completeDelInstanceComponents[$haver][] = $componentKey;
        }
        else
        {
            $this->_delInstanceComponents[$haver][] = $componentKey;
        }
    }

    public function getClassPathByKey($haver, $key)
    {
        return $this->_components[$haver][$key] ?? null;
    }

    protected function destroyComponent($haver, $key)
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
}