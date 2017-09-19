<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/2
 * Time: 16:30
 */
namespace framework\components\cache;
interface CacheInterface
{
    public function getCacheKey($key);
    public function get($key,$default='');
    public function set($key,$value,$expire);
    public function rm($key);
    public function clear();
}