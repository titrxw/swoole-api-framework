<?php
namespace framework\components\response;

class SwooleHeader extends Header
{
  public function send($response = '')
  {
    foreach ($this->_headers as $key=>$item)
    {
        $response->header($key,$item);
    }
    $this->_headers = [];
  }
}