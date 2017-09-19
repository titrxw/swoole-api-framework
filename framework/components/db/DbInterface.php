<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/2
 * Time: 22:04
 */
namespace framework\components\db;

interface DbInterface
{
    public function getRow($sql,$value=array());
    public function getAll($sql,$value=array());
    public function query($sql);
    public function fetchAll();
}