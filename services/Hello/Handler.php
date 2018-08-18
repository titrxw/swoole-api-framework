<?php
namespace services\Hello;

class Handler implements HelloServiceIf
{
  public function sayHello($username)
  {
      return "Hello ".$username;
  }
}