<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/2
 * Time: 21:54
 */
namespace framework\components\db;
use framework\base\Component;

class Pdo extends Component implements DbInterface
{
    protected $_execute;
    protected $_instances = array();
    protected $_defaultDb;
    protected $_currentDb;

    protected function init()
    {
        unset($this->_conf);
        foreach ($this->_appConf['db'] as $key=>$item)
        {
            $this->_defaultDb = $key;
            $this->_currentDb = $this->_defaultDb;
            break;
        }
    }

    protected function getPdoHandle()
    {
        if (!empty($this->_instances[$this->_currentDb]))
        {
//            这里也可使用计时器，在数据库将要断开是 发送数据库请求
//            if($this->reConnect())
//                return $this->_instances[$this->_currentDb];
//            else
//                return $this->getPdoHandle();

//            检测用定时器检测
            return $this->_instances[$this->_currentDb];
        }

        if(!empty($this->_appConf['db'][$this->_currentDb]) && is_array($this->_appConf['db'][$this->_currentDb]))
        {
            try
            {
                $dsn=$this->_appConf['db'][$this->_currentDb]['type'].":dbname=".$this->_appConf['db'][$this->_currentDb]['dbName'].";host=".$this->_appConf['db'][$this->_currentDb]['host'];
                if (!empty($this->_appConf['db'][$this->_currentDb]['persistent']) && $this->_appConf['db'][$this->_currentDb]['persistent'] === true)
                {
                    $this->_instances[$this->_currentDb]=new \PDO($dsn, $this->_appConf['db'][$this->_currentDb]['user'], $this->_appConf['db'][$this->_currentDb]['password'], array(\PDO::ATTR_PERSISTENT => true));
                }
                else
                {
                    $this->_instances[$this->_currentDb]=new \PDO($dsn, $this->_appConf['db'][$this->_currentDb]['user'], $this->_appConf['db'][$this->_currentDb]['password']);
                }
                $this->_instances[$this->_currentDb]->setAttribute(\PDO::ATTR_ORACLE_NULLS, true);
                $this->_instances[$this->_currentDb]->query("set names utf8");
                return $this->_instances[$this->_currentDb];
            }
            catch (\PDOException $e)
            {
                throw new \Exception($e->getMessage(),500);
            }
        }
        else
        {
            throw new \Exception("db {$this->_currentDb} not found",500);
        }
    }

    public function heartBeat()
    {
        foreach ($this->_instances as $item)
        {
            $item->getAttribute(\PDO::ATTR_SERVER_INFO);
        }
    }

//    protected function reConnect()
//    {
//        try
//        {
//            $this->_instances[$this->_currentDb]->getAttribute(\PDO::ATTR_SERVER_INFO);
//        }
//        catch (\PDOException $e)
//        {
//            if(strpos($e->getMessage(), 'MySQL server has gone away')!==false)
//            {
//                $this->_instances[$this->_currentDb] = null;
//                return false;
//            }
//        }
//        return true;
//    }

    public function query($sql)
    {
        if(!empty($sql))
        {
            $this->_execute=$this->getPdoHandle()->query($sql);
        }
        return $this;
    }

    protected function prepare($sql,$value=array())
    {
        if(!empty($sql))
        {
            $this->_execute=$this->getPdoHandle()->prepare($sql);
            $this->finish();
            if(count($value)>0)
            {
                foreach ($value as $key=>$item)
                {
                    $this->_execute->bindParam($key,$item);
                }
                unset($value);
            }
        }
        return $this;
    }

    protected function execute()
    {
        if($this->_execute && $this->_execute->execute())
            return true;
        else
            return false;
    }

    public function getRow($sql,$value=array())
    {
        if(!empty($sql))
        {
            $this->prepare($sql,$value);
            unset($value);
            if($this->execute())
            {
                return $this->_execute->fetch();
            }
        }
        else
            return array();
    }

    public function getAll($sql,$value=array())
    {
        if(!empty($sql))
        {
            $this->prepare($sql,$value);
            unset($value);
            if($this->execute())
            {
                return $this->_execute->fetchAll();
            }
        }
        else
            return array();
    }

    public function count($sql,$value=array())
    {
        if(!empty($sql))
        {
            $this->prepare($sql,$value);
            unset($value);
            if($this->execute())
            {
                return $this->_execute->rowCount();
            }
        }
        else
            return 0;
    }

    public function fetchAll($mode = \PDO::FETCH_BOTH)
    {
        if($this->_execute)
        {
            $this->_execute->setFetchMode($mode);
            $result = $this->_execute->fetchAll();
            $this->_execute->closeCursor();
            return $result;
        }
        else
            return array();
    }

    public function lastId()
    {
        return $this->getPdoHandle()->lastInsertId();
    }

    public function selectDb($db)
    {
        $this->_currentDb = $db;
        return $this;
    }

    protected function finish()
    {
        $this->_currentDb = $this->_defaultDb;
    }

    public function __destruct()
    {
        foreach ($this->_instances as &$instance) {
            $instance = null;
        }
        unset($this->_instances);
    }
}