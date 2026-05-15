<?php

class Atlas_Model_PageGroups
{
	protected $_page_group_id;
	protected $_page_group_name;
	protected $_page_id;
	protected $_parent_id;
	protected $_group_active;
	protected $_image;
	
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
	
	public function setPage_group_id($page_group_id)
	{
		$this->_page_group_id = $page_group_id;
		return $this;
	} #end setPage_group_id() function
	
	public function getPage_group_id()
	{
		return $this->_page_group_id;
	} #end getPage_group_id() function
	
	public function setPage_group_name($page_group_name)
	{
		$this->_page_group_name = $page_group_name;
		return $this;
	} #end setPage_group_name() function
	
	public function getPage_group_name()
	{
		return $this->_page_group_name;
	} #end getPage_group_name() function
	
	public function setPage_id($page_id)
	{
		$this->_page_id = $page_id;
		return $this;
	} #end setPage_id() function
	
	public function getPage_id()
	{
		return $this->_page_id;
	} #end getPage_id() function

	public function setParent_id($parent_id)
	{
		$this->_parent_id = $parent_id;
		return $this;
	} #end setParent_id() function

	public function getParent_id()
	{
		return $this->_parent_id;
	} #end getParent_id() function

	public function setGroup_active($group_active)
	{
		$this->_group_active = $group_active;
		return $this;
	} #end setGroup_active() function
	
	public function getGroup_active()
	{
		return $this->_group_active;
	} #end getGroup_active() function
	
	public function setImage($image)
	{
		$this->_image = $image;
		return $this;
	} #end setImage() function
	
	public function getImage()
	{
		return $this->_image;
	} #end getImage() function
	
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