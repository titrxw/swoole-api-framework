<?php
namespace framework\base;

class Application extends Base
{
    protected $_container;

    protected function beforeInit()
    {
        return true;
    }

    protected function init()
    {
        $this->beforeInit();
        $this->initContainer();
        $this->addBaseComponents();
        $this->setExceptionHandle();
        $this->setErrorHandle();
        $this->setShutDownHandle();
    }

    public static function run($conf,$command = '')
    {
        unset($conf);
        return true;
    }

    public function initEnv()
    {

        
    }

    public function setAppConf($conf)
    {
        $this->_appConf = $conf;
    }

    protected function initContainer()
    {
        $conf = array(
            'default' => $this->_conf['components'],
            'app' => $this->_appConf['components']
        );
        $this->_container = new Container($conf);

        if (COMPOSER)
        {
            $composerConf = array(
                'default' => $this->_conf['composer'],
                'app' =>  $this->_appConf['composer']
            );
            $this->_container->setComposer(new Composer($composerConf));
        }

        unset($conf,
            $composerConf,
            $this->_conf['components'],
            $this->_conf['composer'],
            $this->_appConf['components'],
            $this->_appConf['composer']
        );
    }

    protected function addBaseComponents()
    {
        $components = array(
            'exception' => 'framework\\components\\exception\\Exception',
            'error' => 'framework\\components\\error\\Error',
            'shutdown' => 'framework\\components\\shutdown\\ShutDown',
            'url' => 'framework\\components\\url\\Url',
            'dispatcher' => 'framework\\components\\dispatcher\\Dispatcher',
            'request' => 'framework\\components\\request\\Request',
            'response' => 'framework\\components\\response\\Response',
            'helper' => 'framework\\tool\\Helper'
        );
        $this->_container->addComponents(SYSTEM_APP_NAME, $components);
        unset($components);
    }

    protected function setErrorHandle()
    {
        set_error_handler(array($this->_container->getComponent(SYSTEM_APP_NAME, 'error'), 'handleError'));
    }

    protected function setExceptionHandle()
    {
        set_exception_handler(array($this->_container->getComponent(SYSTEM_APP_NAME, 'exception'), 'handleException'));
    }

    protected function setShutDownHandle()
    {
        register_shutdown_function(array($this->_container->getComponent(SYSTEM_APP_NAME, 'shutdown'), 'handleShutDown'));
    }
}