<?php
/**
 * The first PHP Library for Elica's REST API.
 *
 * @package Elica
 *
 * @licence GNU Public License version 2
 * @author Atanas Georgiev <atanas@fmi.uni-sofia.bg>
 */

/* Load ElicaUtil */
require_once('ElicaUtil.php');

/**
 * Elica API class
 */
class ElicaAPI {
  /* Contains the last HTTP status code returned. */
  public $http_code;
  /* Contains the last API call. */
  public $url;
  /* Set up the API root URL. */
  public $host = "http://62.44.100.145:8080/elica/rest/elicaservices/";
  /* Set timeout default. */
  public $timeout = 30;
  /* Set connect timeout. */
  public $connecttimeout = 30; 
  /* Verify SSL Cert. */
  public $ssl_verifypeer = FALSE;
  /* Respons format. */
  public $format = 'json';
  /* Decode returned json data. */
  public $decode_json = TRUE;
  /* Contains the last HTTP headers returned. */
  public $http_info;
  /* Set the useragnet. */
  public $useragent = 'ElicaClient v0.1.0-beta1';
  /* Immediately retry the API call if the response was not successful. */
  //public $retry = TRUE;


  /**
   * Debug helpers
   */
  function lastStatusCode() { return $this->http_status; }
  function lastAPICall() { return $this->last_api_call; }

  /**
   * construct ElicaAPI object
   */
  function __construct() {
  }

  /**
   * GET wrapper for Request.
   */
  function get($url, $parameters = array()) {
    $response = $this->Request($url, 'GET', $parameters);
    if ($this->format === 'json' && $this->decode_json) {
      return json_decode($response);
    }
    return $response;
  }
  
  /**
   * POST wrapper for Request.
   */
  function post($url, $parameters = array()) {
    $response = $this->Request($this->host.$url, 'POST', $parameters);
    if ($this->format === 'json' && $this->decode_json) {
      return json_decode($response);
    }
    return $response;
  }

  /**
   * Format request
   */
  function Request($url, $method, $parameters) {
    if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
      $url = "{$this->host}{$url}.{$this->format}";
    }    
    $request = new ElicaRequest($method, $url, $parameters);
    switch ($method) {
    case 'GET':
      return $this->http($request->to_url(), 'GET');
    default:
      //return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
      return $this->http($url, $method, $parameters);
    }
  }

  /**
   * Make an HTTP request
   *
   * @return API results
   */
  function http($url, $method, $postfields = NULL) {
    $this->http_info = array();
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
    curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);                                         
    curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
    curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
    curl_setopt($ci, CURLOPT_HEADER, FALSE);

    switch ($method) {
      case 'POST':
        if (!empty($postfields)) {
            $data_string = json_encode($postfields);
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, "POST");                                        
            curl_setopt($ci, CURLOPT_POSTFIELDS, $data_string);                                     
            curl_setopt($ci, CURLOPT_HTTPHEADER, array(                                                       
                'Content-Type: application/json',                                                           
                'Content-Length: ' . strlen($data_string)));         
        }
        break;
    }

    curl_setopt($ci, CURLOPT_URL, $url);
    
    $response = curl_exec($ci);
    
    $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
   
    curl_close ($ci);
    
    return $response;
  }

  /**
   * Get the header info to store.
   */
  function getHeader($ch, $header) {
    $i = strpos($header, ':');
    if (!empty($i)) {
      $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
      $value = trim(substr($header, $i + 2));
      $this->http_header[$key] = $value;
    }
    return strlen($header);
  }
}
