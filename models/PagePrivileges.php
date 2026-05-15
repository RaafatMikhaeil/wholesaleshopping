<?php

class Atlas_Model_PagePrivileges
{
	protected $_page_privilege_id;
	protected $_page_id;
	protected $_permission_group_id;
	
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
	
	public function setPage_privilege_id($page_privilege_id)
	{
		$this->_page_privilege_id = $page_privilege_id;
		return $this;
	} #end setPage_privilege_id() function
	
	public function getPage_privilege_id()
	{
		return $this->_page_privilege_id;
	} #end getPage_privilege_id() function
	
	public function setPage_id($page_id)
	{
		$this->_page_id = $page_id;
		return $this;
	} #end setPage_id() function
	
	public function getPage_id()
	{
		return $this->_page_id;
	} #end getPage_id() function
	
	public function setPermission_group_id($permission_group_id)
	{
		$this->_permission_group_id = $permission_group_id;
		return $this;
	} #end setPermission_group_id() function
	
	public function getPermission_group_id()
	{
		return $this->_permission_group_id;
	} #end getPermission_group_id() function
	
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