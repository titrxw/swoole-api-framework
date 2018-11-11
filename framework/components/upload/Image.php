<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-4-3
 * Time: 下午9:30
 */

namespace framework\components\upload;

class Image extends Upload
{
  protected function securityVeritify($filePath, $mime)
  {
      $handle = fopen($filePath, 'r');
      if (!$handle) {
        $this->triggerThrowable(new \Error('can not open file ' . $filePath, 500));
      }

      $head = fread($handle, 8);
      switch ($this->_mime[$mime]) {
        case 'jpg':
        if ($head != 'ffd8ffe000104a46') {
          return false;
        } 
        break;
        case 'png':
        if ($head != '89504e470d0a1a0a') {
          return false;
        } 
        break;
        default:
        return false;
      }
      
      return false;
  }
}