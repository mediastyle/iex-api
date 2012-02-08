<?php

/**
 * @author Ronni Elken Lindsgaard <ronni@mediastyle.dk>
 * @copyright Copyright (C) 2011-2012, MediaStyle (http://mediastyle.dk)
 * @package IEXApi
 */
define('IEX_URL','http://dev.iex.dk/index.php');

class IexClientApi {
  private $ch = null;
  private $post = array();

  public function __construct($customer,$link,$secret){
    $this->open();
    $this->post['key'] = $customer . ':' . $link ':' . $secret;
  }

  public function open(){
    $ch = curl_init();

    curl_setopt($ch,CURLOPT_URL,IEX_URL);
    curl_setopt($ch,CURLOPT_HEADER,FALSE);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
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
    
    $postdata = $this->build_post($post);
    if(!$postdata)
      return false;
    curl_setopt($this->ch,CURLOPT_POSTFIELDS,$postdata);
    return curl_exec($this->ch);
  }

  private function build_post($fields=array(),$prefix =
  '',$postfix=''){
    $values = array();
    if(is_array($fields)){
      foreach($fields as $field=>$value){
        if(is_array($value)){
          $values[] = $this->build_post($value,$prefix . $field . $postfix .'[',']');
        } else {
          if(!is_object($value)){
            $values[] = $prefix . $field . $postfix . '=' . $value;
          }
        }
      }
      return implode($values,'&');
    }
    return false;
  }

  public function close(){
    curl_close($this->ch);
  }
}
