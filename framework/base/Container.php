<?php
namespace framework\base;

class Container extends Base
{
    protected static $instance;
    protected $_composer;
    protected $_instances;
    protected $_components;
    protected $_delInstanceComponents = array();
    protected $_completeDelInstanceComponents = array();

    protected function init()
    {
        $this->_components = array();
        $this->_instances = array();
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

    /**
     * @param $key
     * @param $classPath
     * @param $conf   array('default' => array(),'app'=> array())
     * @return bool
     * @throws \Exception
     */
    public function addComponent($key, $classPath, $conf = array())
    {
        if(!empty($key)&&!empty($classPath))
        {
            $this->_components[$key] = $classPath;
            if(!empty($conf))
            {
                $this->_conf[$key] = empty($conf['default']) ? array() : $conf['default'];
                $this->_appConf[$key] = empty($conf['app']) ? array() : $conf['app'];
            }
            unset($conf);
            return true;
        }
        throw new \Exception('components key or classpath can not be empty');
    }

    public function addComponents($components)
    {
        try
        {
            foreach ($components as $key=>$classPath)
            {
                $this->addComponent($key, $classPath);
            }
            unset($components);
        }
        catch (\Exception $e)
        {
            throw new \Exception('components add failed ' . $e->getMessage());
        }
    }

    public function getComponent($key, $params = array())
    {
        try
        {
            if (empty($key)) {
                return false;
            }

            if (!empty($this->_instances[$key])) {
                return $this->_instances[$key];
            }

            $classPath = $this->getClassPathByKey($key);
            if (!empty($classPath))
            {
                $conf = array(
                    'default' => empty($this->_conf[$key]) ? array() : $this->_conf[$key],
                    'app' => empty($this->_appConf[$key]) ? array() : $this->_appConf[$key]
                );

                $instance = new $classPath($conf);
                unset($conf);

                if ($instance instanceof Component) {
                    $instance->setUniqueId($key);
                    $this->_instances[$key] = $instance;
                    unset($instance);
                }
                else
                {
                    throw new \Exception('instance' . $classPath . 'have to instance of Component', 500);
                }
            }
            else
            {
                if (COMPOSER && $this->_composer->checkComposer($key)) {
                    $this->_instances[$key] = $this->_composer->getComposer($key, $params);
                }
                else
                {
                    throw new \Exception("components {$key} not exists", 500);
                }
            }
        }
        catch (\Exception $e)
        {
            $msg = $e->getMessage();
            $msg = empty($msg) ? ' maybe this class not instance of Components ' : $msg;
            throw new \Exception( $msg, 500);
        }

        return $this->_instances[$key];
    }

    public function unInstall($componentKey, $completeDel = true)
    {
        if ($completeDel) {
            $this->_completeDelInstanceComponents[] = $componentKey;
        }
        else
        {
            $this->_delInstanceComponents[] = $componentKey;
        }
    }

    public function getClassPathByKey($key)
    {
        return empty($this->_components[$key]) ? null : $this->_components[$key];
    }

    protected function destroyComponent($key)
    {
        if(empty($key))
            return false;

        unset($this->_components[$key]);
        unset($this->_instances[$key]);
    }

    protected function destroyComponentsInstance($key)
    {
        if(empty($key))
            return false;

        unset($this->_instances[$key]);
    }

    public function finish()
    {
        foreach ($this->_delInstanceComponents as $item)
        {
            $this->destroyComponentsInstance($item);
        }
        foreach ($this->_completeDelInstanceComponents as $item)
        {
            $this->destroyComponent($item);
        }

        $this->_delInstanceComponents = array();
        $this->_completeDelInstanceComponents = array();
    }
}