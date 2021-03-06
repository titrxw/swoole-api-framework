<?php
namespace framework\components\response;
use framework\base\Component;

class Header extends Component
{
    protected $_headers;
    protected $_request;
    protected $_code = 200;
    protected $_curType;
    protected $_contentTypes = array(
      'xml'  => 'application/xml,text/xml,application/x-xml',
      'json' => 'application/json,text/x-json,application/jsonrequest,text/json',
      'png'  => 'image/png',
      'jpg'  => 'image/jpg,image/jpeg,image/pjpeg',
      'gif'  => 'image/gif',
      'csv'  => 'text/csv',
      'txt' => 'text/plain',
      'html' => 'text/html,application/xhtml+xml,*/*',
      'pdf' => 'application/pdf',
      'xls' => 'application/x-xls',
      'apk' => 'application/vnd.android.package-archive',
      'doc' => 'application/msword',
      'zip' => 'application/zip'
  );

  protected function init()
  {
    $this->initHeader();
    $this->contentType('html');
  }

  protected function initHeader()
  {
      $this->_headers = array(
          'X-Powered-By' => 'esay-framework',
          'server' => 'esay-framework'
      );
  }

  public function add($key, $header)
  {
    if($key && $header)
      $this->_headers[$key] = $header;
  }

  public function del($key)
  {
    if (!empty($this->_headers[$key])) {
      unset($this->_headers[$key]);
    }
  }

  public function setCode($code)
  {
      $this->_code = $code;
  }

  public function contentType($type, $charset = '')
  {
      $contentType = $this->_contentTypes[$type] ?? $this->_contentTypes[$this->getValueFromConf('defaultType', 'html')];
      $charset = empty($charset) ? $this->getValueFromConf('charset', 'utf-8') : $charset;
      $this->_curType = $type;
      $this->_headers['Content-Type'] = $contentType . '; charset=' . $charset;
  }

  public function noCache()
  {
      $this->add('Cache-Control','no-store, no-cache, must-revalidate');
      $this->add('Pragma','no-cache');
  }

  public function getCode()
  {
    return $this->_code;
  }

  public function getCurType()
  {
    return $this->_curType;
  }

  public function send($response = '')
  {
    http_response_code($this->_code);
    foreach ($this->_headers as $key=>$item)
    {
        header($key . ':' . $item);
    }
    $this->_headers = [];
  }

  public function rollback()
  {
    $this->initHeader();
    $this->contentType('html');
    $this->_code = 200;
  }
}
