<?php

class Atlas_Model_Pages
{
	protected $_page_id;
	protected $_page_name;
	protected $_page_group_id;
	protected $_page_parent_id;
	protected $_page_active;
        protected $_host;
	protected $_path;
	protected $_show_in_dashboard;
	
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
	
	public function setPage_id($page_id)
	{
		$this->_page_id = $page_id;
		return $this;
	} #end setPage_id() function
	
	public function getPage_id()
	{
		return $this->_page_id;
	} #end getPage_id() function
	
	public function setPage_name($page_name)
	{
		$this->_page_name = $page_name;
		return $this;
	} #end setPage_name() function
	
	public function getPage_name()
	{
		return $this->_page_name;
	} #end getPage_name() function
	
	public function setPage_group_id($page_group_id)
	{
		$this->_page_group_id = $page_group_id;
		return $this;
	} #end setPage_group_id() function
	
	public function getPage_group_id()
	{
		return $this->_page_group_id;
	} #end getPage_group_id() function
	
	public function setPage_parent_id($page_parent_id)
	{
		$this->_page_parent_id = $page_parent_id;
		return $this;
	} #end setPage_parent_id() function

	public function getPage_parent_id()
	{
		return $this->_page_parent_id;
	} #end getPage_parent_id() function

	public function setPage_active($page_active)
	{
		$this->_page_active = $page_active;
		return $this;
	} #end setPage_active() function
	
	public function getPage_active()
	{
		return $this->_page_active;
	} #end getPage_active() function

    public function setHost($host)
    {
        $this->_host = $host;
        return $this;
    } #end setHost() function

    public function getHost()
    {
        return $this->_host;
    } #end getHost() function
	
	public function setPath($path)
	{
		$this->_path = $path;
		return $this;
	} #end setPath() function
	
	public function getPath()
	{
		return $this->_path;
	} #end getPath() function
	
	public function setShow_in_dashboard($show_in_dashboard)
	{
		$this->_show_in_dashboard = $show_in_dashboard;
		return $this;
	} #end setShow_in_dashboard() function
	
	public function getShow_in_dashboard()
	{
		return $this->_show_in_dashboard;
	} #end getShow_in_dashboard() function
	
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