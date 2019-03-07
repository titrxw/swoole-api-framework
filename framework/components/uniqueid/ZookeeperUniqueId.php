<?php
namespace framework\components\uniqueid;

class ZookeeperUniqueId extends UniqueId
{
    protected $dataCenterLeftShift = 0;
    protected $workIdLeftShift = parent::DATACENTER_BITS + parent::WORK_ID_BITS + parent::SEQUENCE_BITS;

    protected $_zookeeper;
    
    protected function init()
    {
        $path = $this->getValueFromConf('zookeeper_path');
        if (!$path) {
            $this->triggerThrowable(new \Exception("zookeeper listener path not be empty", 500));
        }

        $this->getHandle();

        $workers = $this->_zookeeper->getHandle()->getChildren($path); 
        if (!$workers) {
            $this->triggerThrowable(new \Exception("zookeeper node $path not exists", 500));
        }
        // 临时节点 顺序节点
        $this->_zookeeper->getHandle()->makeNode($path . '/snowflake_w_id-', \count($workers) , [], \Zookeeper::EPHEMERAL | \Zookeeper::SEQUENCE);
        
        $this->workId = \count($workers);
        $this->dataCenterId = 0;
    }


    public function getHandle ()
    {
        if (!$this->_zookeeper) {
            $this->_zookeeper = new \framework\components\zookeeper\Zookeeper($this->getValueFromConf('hosts'),$this->getValueFromConf('name'),$this->getValueFromConf('password'));
        }

        return $this->_zookeeper;
    }
}
