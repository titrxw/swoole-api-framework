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
    public function getRow($sql,$value=[]);
    public function getAll($sql,$value=[]);
    public function query($sql);
    public function fetchAll();
}