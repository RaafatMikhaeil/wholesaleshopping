<?php

class Atlas_Model_ForgotPassword
{
	protected $_reset_id;
	protected $_user_id;
	protected $_key;
	protected $_timestamp;
	protected $_ip;
	protected $_agent;
	protected $_fa_timestamp;
	protected $_failed_attempts;
	
	// set the default values if options given
	public function __construct(array $options = NULL)
	{
		// if attributes were given set the base values
		if( is_array($options) ){
			$this->setOptions($options);
		}
	} #end __construct() function
	
	// check if a user defined variable exists
	public function __set($name, $value)
	{
		// if an unknown variable is used throw exception
		$method = 'set' . $name;
		if( !method_exists($this, $method) ){
			throw new Exception('invalid class variable called for write');
		}
		$this->$method($value);
	} #end __set() function
	
	// check if a user defined variable exists
	public function __get($name)
	{
		// if an unknown variable is used throw exception
		$method = 'get' . $name;
		if( !method_exists($this, $method) ){
			throw new Exception('invalid class variable called for read');
		}
		return $this->method();
	} #end __get() function
	
	public function setOptions(array $options)
	{
		// get a list of all setter methods and set each
		// value from the given array into the object
		$methods = get_class_methods($this);
		foreach( $options as $key=>$value ){
			$method = 'set' . ucfirst(strtolower($key));
			if( in_array($method, $methods) ){
				$this->$method($value);
			}
		}
		return $this;
	} #end setOptions function
	
	public function setReset_id($reset_id)
	{
		$this->_reset_id = $reset_id;
		return $this;
	} #end setReset_id() function
	
	public function getReset_id()
	{
		return $this->_reset_id;
	} #end getReset_id() function
	
	public function setUser_id($user_id)
	{
		$this->_user_id = $user_id;
		return $this;
	} #end setUser_id() function
	
	public function getUser_id()
	{
		return $this->_user_id;
	} #end getUser_id() function
	
	public function setKey($key)
	{
		$this->_key = $key;
		return $this;
	} #end setKey() function
	
	public function getKey()
	{
		return $this->_key;
	} #end getKey() function
	
	public function setTimestamp($timestamp)
	{
		$this->_timestamp = $timestamp;
		return $this;
	} #end setTimestamp() function
	
	public function getTimestamp()
	{
		return $this->_timestamp;
	} #end getTimestamp() function
	
	public function setIp($ip)
	{
		$this->_ip = $ip;
		return $this;
	} #end setIp() function
	
	public function getIp()
	{
		return $this->_ip;
	} #end getIp() function
	
	public function setAgent($agent)
	{
		$this->_agent = $agent;
		return $this;
	} #end setAgent() function
	
	public function getAgent()
	{
		return $this->_agent;
	} #end getAgent() function
	
	public function setFa_timestamp($timestamp)
	{
		$this->_fa_timestamp = $timestamp;
		return $this;
	} #end setFa_timestamp() function
	
	public function getFa_timestamp()
	{
		return $this->_fa_timestamp;
	} #end getFa_timestamp() function
	
	public function setFailed_attempts($failed_attempts)
	{
		$this->_failed_attempts = $failed_attempts;
		return $this;
	} #end setFailed_attempts() function
	
	public function getFailed_attempts()
	{
		return $this->_failed_attempts;
	} #end getFailed_attempts() function
	
	public function toArray()
	{
		$class_vars = get_class_vars(__CLASS__);
		$results    = array();
		foreach( $class_vars as $index=>$value ){
			$results[substr($index, 1)] = $this->$index;
		}
		return $results;
	} #end toArray() function
	
}

?>