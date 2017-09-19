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
        $this->getComponent('Pdo')->selectDb($db);
        return $this;
    }

    protected function getRow($sql,$value=array())
    {
        return $this->getComponent('Pdo')->getRow($sql, $value);
    }

    protected function getAll($sql,$value=array())
    {
        return $this->getComponent('Pdo')->getAll($sql, $value);
    }

    protected function query($sql)
    {
        $this->getComponent('Pdo')->query($sql);
        return $this;
    }

    protected function fetchAll($mode = \PDO::FETCH_BOTH)
    {
        return $this->getComponent('Pdo')->fetchAll($mode);
    }
}