<?php

class Atlas_Model_LoginLogs
{
	protected $_login_id;
	protected $_user_id;
	protected $_login_date;
	protected $_logout_date;
	
	public function __construct(array $options = NULL)
	{
            // if attributes were given set the base values
            if( is_array($options) ){
                    $this->setOptions($options);
            }
	} #end __construct function

	public function __set($name, $value)
	{
            // if an unknown variable is used throw exception
            $method = 'set' . $name;
            if( !method_exists($this, $method) ){
                    throw new Exception('Invalid login log property used');
            }
            $this->$method($value);
	} #end __set function

	public function __get($name)
	{
            // if an unknown variable is used throw exception
            $method = 'get' . $name;
            if( !method_exists($this, $method) ){
                    throw new Exception('Invalid login log property used');
            }
            return $this->method();
	} #end __get function

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

	public function setLogin_id($login_id)
	{
            $this->_login_id = $login_id;
            return $this;
	} #end setLogin_id function

	public function getLogin_id()
	{
            return $this->_login_id;
	} #end getLogin_id function

	public function setUser_id($user_id)
	{
            $this->_user_id = $user_id;
            return $this;
	} #end setUser_id function
	
	public function getUser_id()
	{
            return $this->_user_id;
	} #end getUser_id function

	public function setLogin_date($login_date)
	{
            $this->_login_date = $login_date;
            return $this;
	} #end setLogin_date function
	
	public function getLogin_date()
	{
            return $this->_login_date;
	} #end getLogin_date function
	
	public function setLogout_date($logout_date)
	{
            $this->_logout_date = $logout_date;
            return $this;
	} #end setLogin_date function

	public function getLogout_date()
	{
            return $this->_logout_date;
	} #end getLogin_date function

	public function toArray()
	{
            $class_vars = get_class_vars(__CLASS__);
            $results    = array();
            foreach( $class_vars as $index=>$value ){
                    $results[substr($index, 1)] = $this->$index;
            }
            return $results;
	} #end toArray function
}

?>