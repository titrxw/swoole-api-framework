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
    protected const EPOCH_OFFSET = 1483200000000;
    protected const SIGN_BITS = 1;
    protected const TIMESTAMP_BITS = 41;
    protected const DATACENTER_BITS = 5;
    protected const WORK_ID_BITS = 5;
    protected const SEQUENCE_BITS = 12;

    /**
     * @var int
     */
    protected $signLeftShift = self::TIMESTAMP_BITS + self::DATACENTER_BITS + self::WORK_ID_BITS + self::SEQUENCE_BITS;
    protected $timestampLeftShift = self::DATACENTER_BITS + self::WORK_ID_BITS + self::SEQUENCE_BITS;
    // 在使用中datacenter少用   也就是把workerid做成10为  那么就需要把datacenter左移64位  workerid 左移17位  可以用zookeeper作为发号器
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
        // 按照占用5为来算的话  范围是0-31
        $this->workId = SYSTEM_WORK_ID;
        // 这里不建议这样，会有重复的可能
        $this->dataCenterId = crc32(SYSTEM_CD_KEY);
    }

    public function setWrokerId($workerId)
    {
        $this->workId = $workerId;
        return $this;
    }

    public function setDataCenterId($dataCenterId)
    {
        $this->dataCenterId = $dataCenterId;
        return $this;
    }

    //生成一个ID
    public function nextId()
    {
        $timestamp = $this->timeGen();
        $lastTimestamp = $this->lastTimestamp;
        //判断时钟是否正常  闰秒 时间回拨
        if ($timestamp <= $lastTimestamp) {
            $time = $lastTimestamp - $timestamp;
            $this->triggerThrowable(new \Exception("Clock moved backwards.  Refusing to generate id for $time milliseconds", 500));
        }
        //生成唯一序列
        if ($lastTimestamp == $timestamp) {
            
            // 防止超过12位
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
