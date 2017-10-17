<?php
namespace framework\base;

class Model extends Component
{
    protected function init()
    {
        $this->unInstall();
    }

    protected function selectDb($db)
    {
        $this->getComponent('db')->selectDb($db);
        return $this;
    }

    protected function getRow($sql,$value=array())
    {
        return $this->getComponent('db')->getRow($sql, $value);
    }

    protected function getAll($sql,$value=array())
    {
        return $this->getComponent('db')->getAll($sql, $value);
    }

    protected function query($sql)
    {
        $this->getComponent('db')->query($sql);
        return $this;
    }

    protected function fetchAll($mode = \PDO::FETCH_BOTH)
    {
        return $this->getComponent('db')->fetchAll($mode);
    }
}