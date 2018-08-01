<?php
namespace framework\components\uniqueid;
use framework\base\Component;

class UniqueId extends Component
{
    /**
     * Offset from Unix Epoch
     * Unix Epoch : January 1 1970 00:00:00 GMT
     * Epoch Offset : January 1 2000 00:00:00 GMT
     */
    const EPOCH_OFFSET = 1483200000000;
    const SIGN_BITS = 1;
    const TIMESTAMP_BITS = 41;
    const DATACENTER_BITS = 5;
    const WORK_ID_BITS = 5;
    const SEQUENCE_BITS = 12;

    /**
     * @var int
     */
    protected $signLeftShift = self::TIMESTAMP_BITS + self::DATACENTER_BITS + self::WORK_ID_BITS + self::SEQUENCE_BITS;
    protected $timestampLeftShift = self::DATACENTER_BITS + self::WORK_ID_BITS + self::SEQUENCE_BITS;
    protected $dataCenterLeftShift = self::WORK_ID_BITS + self::SEQUENCE_BITS;
    protected $workIdLeftShift = self::SEQUENCE_BITS;
    protected $maxSequenceId = -1 ^ (-1 << self::SEQUENCE_BITS);
    protected $maxWorkId = -1 ^ (-1 << self::WORK_ID_BITS);
    protected $maxDataCenterId = -1 ^ (-1 << self::DATACENTER_BITS);
    protected $sequenceMask = -1 ^ (-1 << self::SEQUENCE_BITS);


    /**
     * @var mixed
     */
    protected $dataCenterId;

    /**
     * @var mixed
     */
    protected $workId;

    /**
     * @var null|int
     */
    protected $lastTimestamp = null;
    protected $sequence = 0;

    protected function init()
    {
        if(SYSTEM_WORK_ID< 0){
            $this->triggerThrowable(new \Exception("workerId can't be  less than 0", 500));
        }
        //赋值
        $this->workId = SYSTEM_WORK_ID;
        $this->dataCenterId = SYSTEM_CD_KEY;
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
            
            $this->sequence = ($this->sequence + 1) & $this->sequenceMask;
            if ($this->sequence == 0) {
                $timestamp = $this->tilNextMillis($lastTimestamp);
            }
        } else {
            $this->sequence = 0;
        }
        $this->lastTimestamp = $timestamp;
        //
        //组合3段数据返回: 时间戳.工作机器.序列
        $nextId = (($timestamp - self::EPOCH_OFFSET) << $this->timestampLeftShift) | ($this->dataCenterId << $this->dataCenterLeftShift) | ($this->workId << $this->workIdLeftShift) | $this->sequence;
        return $nextId;
    }

    //取当前时间毫秒  
    protected function timeGen()
    {
        $timestramp = (float)\sprintf("%.0f", \microtime(true) * 1000);
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