<?php
namespace framework\tool;
use framework\traits\Throwable;

class UniqueId
{
    use Throwable;
    //开始时间,固定一个小于当前时间的毫秒数即可  
    const twepoch =  1474992000000;//2016/9/28 0:0:0  
  
    //机器标识占的位数  
    const workerIdBits = 10;  
  
    //毫秒内自增数点的位数  
    const sequenceBits = 12;  
  
    protected $workId = 0;  
  
    //要用静态变量  
    protected $lastTimestamp = -1;  
    protected $sequence = 0;  
  
  
    public function __construct($workId)
    {
        //机器ID范围判断  
        $maxWorkerId = -1 ^ (-1 << self::workerIdBits);  
        if($workId > $maxWorkerId || $workId< 0){
            $this->triggerThrowable(new \Exception("workerId can't be greater than ".$this->maxWorkerId." or less than 0", 500));
        }  
        //赋值  
        $this->workId = $workId;  
    }  
  
    //生成一个ID  
    public function nextId()
    {
        $timestamp = $this->timeGen();
        $lastTimestamp = $this->lastTimestamp;  
        //判断时钟是否正常  
        if ($timestamp < $lastTimestamp) {
            $time = $lastTimestamp - $timestamp;
            $this->triggerThrowable(new \Exception("Clock moved backwards.  Refusing to generate id for $time milliseconds", 500));
        }  
        //生成唯一序列  
        if ($lastTimestamp == $timestamp) {  
            $sequenceMask = -1 ^ (-1 << self::sequenceBits);  
            $this->sequence = ($this->sequence + 1) & $sequenceMask;
            if ($this->sequence == 0) {  
                $timestamp = $this->tilNextMillis($lastTimestamp);  
            }  
        } else {
            $this->sequence = 0;
        }
        $this->lastTimestamp = $timestamp;
        //  
        //时间毫秒/数据中心ID/机器ID,要左移的位数  
        $timestampLeftShift = self::sequenceBits + self::workerIdBits;  
        $workerIdShift = self::sequenceBits;  
        //组合3段数据返回: 时间戳.工作机器.序列  
        $nextId = (($timestamp - self::twepoch) << $timestampLeftShift) | ($this->workId << $workerIdShift) | $this->sequence;  
        return $nextId;  
    }  
  
    //取当前时间毫秒  
    protected function timeGen()
    {
        $timestramp = (float)sprintf("%.0f", microtime(true) * 1000);  
        return  $timestramp;  
    }  
  
    //取下一毫秒  
    protected function tilNextMillis($lastTimestamp)
    {
        $timestamp = $this->timeGen();  
        while ($timestamp <= $lastTimestamp) {  
            $timestamp = $this->timeGen();  
        }  
        return $timestamp;  
    }
}