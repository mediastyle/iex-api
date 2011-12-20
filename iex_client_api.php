<?php

/**
 * @author Ronni Elken Lindsgaard <ronni@mediastyle.dk>
 * @copyright Copyright (C) 2011-2012, MediaStyle (http://mediastyle.dk)
 * @package IEXApi
 */

class IexClientApi {
  private $ch = null;
  private $post = array();

  public function __construct($key,$secret){
    $this->open($key);
    $this->post['secret'] = $secret;
  }

  public function open($key){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,IEX_URL);
    curl_setopt($ch,CURLOPT_POST,TRUE);

    $this->ch = $ch;
  }

  public function query($type,$action,$data=array()){
    $post = $this->post;
    $post['type'] = $type;
    $post['action'] = $action;
    $post['data'] = $data;
    curl_setopt(CURLOPT_POSTFIELDS,http_build_url($post));
    return curl_exec($this->ch);
  }

  public function close(){
    curl_close($this->ch);
  }
}
