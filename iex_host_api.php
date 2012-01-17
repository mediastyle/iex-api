<?php

/**
 * @author Ronni Elken Lindsgaard <ronni@mediastyle.dk>
 * @copyright Copyright (C) 2011-2012, MediaStyle (http://mediastyle.dk)
 * @package IEXApi
 */

class IexHostApi {
  private $handlers = array();
  public function __construct($secret){
    $this->secret = $secret;
    $this->post = $_POST;
  }

  private function authenticate(){
    if(md5($this->secret) == md5($this->post['secret']))
      return true;
    return false;
  }

  public function getType(){
    return $this->sanitise($this->post['type']);
  }

  public function getAction(){
    return $this->sanitise($this->post['action']);
  }

  public function getData(){
    return $this->sanitise($this->post['data']);
  }

  private function sanitise($value){
    if(is_array($value)){
      foreach($value as $k=> $v){
        $sanitised_value[$k] = $this->sanitise($v);
      }
    } else {
      $sanitised_value = addslashes($value);
    }
    return $sanitised_value;
  }

  public function setAction($type,$action,$function){
    $this->handlers[$type][$action] = $function;
  }

  public function process(){
    if($this->authenticate()){
      $handlers = $this->handlers;
      $type = $this->getType();
      $action = $this->getAction();
      if(isset($handlers[$type])){
        if(isset($handlers[$type][$action])){
          return call_user_func($handlers[$type][$action],$this->getData());
        } else {
          throw new Exception('No handler defined for action ' . $action .
          ' and type ' . $type);
        }
      } else {
      throw new Exception('No actions defined for type ' . $type);
      }
    }
  }
}
