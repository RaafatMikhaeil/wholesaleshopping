<?php

class Atlas_Model_Notifications
{
	protected $_notification_id;
	protected $_user_id;
	protected $_timestamp;
	protected $_message;
	protected $_is_active;
	
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
	
	public function setNotification_id($notification_id)
	{
		$this->_notification_id = $notification_id;
		return $this;
	} #end setNotification_id() function
	
	public function getNotification_id()
	{
		return $this->_notification_id;
	} #end getNotification_id() function
	
	public function setUser_id($user_id)
	{
		$this->_user_id = $user_id;
		return $this;
	} #end setUser_id() function
	
	public function getUser_id()
	{
		return $this->_user_id;
	} #end getUser_id() function
	
	public function setTimestamp($timestamp)
	{
		$this->_timestamp = $timestamp;
		return $this;
	} #end setTimestamp() function
	
	public function getTimestamp()
	{
		return $this->_timestamp;
	} #end getTimestamp() function
	
	public function setMessage($message)
	{
		$this->_message = $message;
		return $this;
	} #end setMessage() function
	
	public function getMessage()
	{
		return $this->_message;
	} #end getMessage() function
	
	public function setIs_active($is_active)
	{
		$this->_is_active = $is_active;
		return $this;
	} #end setIs_active() function
	
	public function getIs_active()
	{
		return $this->_is_active;
	} #end getIs_active() function
	
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