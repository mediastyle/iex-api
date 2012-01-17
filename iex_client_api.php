<?php

/**
 * @author Ronni Elken Lindsgaard <ronni@mediastyle.dk>
 * @copyright Copyright (C) 2011-2012, MediaStyle (http://mediastyle.dk)
 * @package IEXApi
 */
define('IEX_URL','http://localhost/iiphoenix/index.php');

class IexClientApi {
  private $ch = null;
  private $post = array();

  public function __construct($key,$secret){
    $this->open();
    $this->post['secret'] = $secret;
    $this->post['key'] = $key;
  }

  public function open(){
    $ch = curl_init();

    curl_setopt($ch,CURLOPT_URL,IEX_URL);
    curl_setopt($ch,CURLOPT_HEADER,FALSE);
    curl_setopt($ch,CURLOPT_POST,TRUE);

    $this->ch = $ch;
  }

  public function query($type,$action,$data=array(),$config=array()){
    $post = $this->post;
    $post['type'] = $type;
    $post['action'] = $action;
    foreach($config as $key=>$value){
      $post[$key] = $value;
    }
    $post['data'] = $data;
    curl_setopt($this->ch,CURLOPT_POSTFIELDS,$this->build_post($post));
    return curl_exec($this->ch);
  }

  private function build_post($fields=array(),$prefix =
  '',$postfix=''){
    $values = array();
    foreach($fields as $field=>$value){
      if(is_array($value)){
        $values[] = $this->build_post($value,$prefix . $field . $postfix .'[',']');
      } else {
        $values[] = $prefix . $field . $postfix . '=' . $value;
      }
    }
    return implode($values,'&');
  }

  public function close(){
    curl_close($this->ch);
  }
}
