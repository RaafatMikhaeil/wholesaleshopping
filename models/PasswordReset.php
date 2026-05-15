<?php

class Atlas_Model_PasswordReset
{
	protected $_reset_id;
	protected $_user_id;
	protected $_last_reset;
	protected $_last_password;
	
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
	
	public function setLast_reset($last_reset)
	{
		$this->_last_reset = $last_reset;
		return $this;
	} #end setLast_reset() function
	
	public function getLast_reset()
	{
		return $this->_last_reset;
	} #end getLast_reset() function
	
	public function setLast_password($last_password)
	{
		$this->_last_password = $last_password;
		return $this;
	} #end setLast_password() function
	
	public function getLast_password()
	{
		return $this->_last_password;
	} #end getLast_password() function
	
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