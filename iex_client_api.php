<?php

/**
 * @author Ronni Elken Lindsgaard <ronni@mediastyle.dk>
 * @copyright Copyright (C) 2011-2012, MediaStyle (http://mediastyle.dk)
 * @package IEXApi
 */

//define('IEX_URL','http://localhost/iiphoenix/http-pub/index.php');
define('IEX_URL','http://dev.iex.dk/index.php');

define('IEX_TRANSFER','transfer');
define('IEX_DELETE','delete');

class IexClientApi {
  private $ch = null;
  private $auth = array();
  private $tansfers = array();
  private $error_handler = '';

  public function __construct($customer,$link,$secret){
    $this->open();
    $auth = array(
      'customer' => $customer,
      'link' => $link,
      'job' => md5(time() . session_id()),
      'secret' => $secret,
    );
    $this->auth = $auth;
  }

  public function getKey($data){
    $auth = $this->auth;
    $hash = md5($auth['secret'] . serialize($data));
    return
    implode(':',array($auth['customer'],$auth['link'],$auth['job'], $hash));
  }

  private function open(){
    $ch = curl_init();

    curl_setopt($ch,CURLOPT_URL,IEX_URL);
    curl_setopt($ch,CURLOPT_HEADER,FALSE);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
    curl_setopt($ch,CURLOPT_POST,TRUE);

    $this->ch = $ch;
  }

  public function setErrorHandler($error_handler){
    if(function_exists($error_handler)){
      $this->error_handler = $error_handler;
      return true;
    }
    return false;
  }

  private function callErrorHandler($data){
    if($this->error_handler){
      //The handler could have been deleted since creation
      if(function_exists($this->error_handler)){ 
        return call_user_func($this->error_handler,$transfer);
      } else {
        throw Exception('Callback function '. $this->error_handler .
        ' does not exist');
      }
    } else { //error handler is not set
      return false;
    }
  }

  public function addTransfer($entity_type,$action,$data,$meta=array()){
    $transfer = $meta;
    $transfer['key'] = $this->getKey($data);
    $transfer['type'] = $entity_type;
    $transfer['action'] = $action;
    $transfer['data'] = $data;
    $this->transfers[] = $transfer;
  }

  public function doTransfer($return_result=FALSE){
    $transfers = $this->transfers;
    $responses = array();
    if(is_array($transfers)){ //heaven forbid it's not
      foreach($transfers as $transfer){
        $postfields = $this->buildPost($transfer);
        if(!$postfields){
          $responses[] = $this->callErrorHandler($transfer);
        } else {
          //We wish the transfers to be made sequentially for a reason.
          curl_setopt($this->ch,CURLOPT_POSTFIELDS,$postfields);
          $result = curl_exec($this->ch);
          $responses[] = $return_result ? $result : curl_getinfo($this->ch);
        }
      }
    }
    return $responses;
  }

  private function buildPost($fields=array(),$prefix =
  '',$postfix=''){
    $values = array();
    if(is_array($fields)){
      foreach($fields as $field=>$value){
        if(is_array($value)){
          $values[] = $this->buildPost($value,$prefix . $field . $postfix .'[',']');
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
