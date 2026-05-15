<?php

class Atlas_Model_InvLog {

    protected $_log_id;
    protected $_log_record_id;
    protected $_log_text;
    protected $_log_update;
    protected $_log_type;
    protected $_log_datetime;
    protected $_log_userid;
    
    public function __construct(array $options = NULL) {
        // if attributes were given set the base values
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

#end __construct function

    public function __set($name, $value) {
        // if an unknown variable is used throw exception
        $method = "set" . $name;
        if (!method_exists($this, $method)) {
            throw new Exception("Invalid property used");
        }
        $this->$method($value);
    }

#end __set function

    public function __get($name) {
        // if an unknown variable is used throw exception
        $method = "get" . $name;
        if (!method_exists($this, $method)) {
            throw new Exception("Invalid property used");
        }
        return $this->method();
    }

#end __get function

    public function setOptions(array $options) {
        // get a list of all setter methods and set each
        // value from the given array into the object
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

#end setOptions function

    public function toArray() {
        $class_vars = get_class_vars(__CLASS__);
        $results = array();
        foreach ($class_vars as $index => $value) {
            $results[substr($index, 1)] = $this->$index;
        }
        return $results;
    }

    public function setLog_id($log_id) {
        $this->_log_id = $log_id;
        return $this;
    }

    public function getLog_id() {
        return $this->_log_id;
    }
    
    public function setLog_record_id($log_record_id) {
        $this->_log_record_id = $log_record_id;
        return $this;
    }

    public function getLog_record_id() {
        return $this->_log_record_id;
    }

    public function setLog_text($log_text) {
        $this->_log_text = $log_text;
        return $this;
    }

    public function getLog_text() {
        return $this->_log_text;
    }
    
    public function setLog_update($log_update) {
        $this->_log_update = $log_update;
        return $this;
    }

    public function getLog_update() {
        return $this->_log_update;
    }
    
    public function setLog_type($log_type) {
        $this->_log_type = $log_type;
        return $this;
    }

    public function getLog_type() {
        return $this->_log_type;
    }
    
    public function setLog_datetime($log_datetime) {
        $this->_log_datetime = $log_datetime;
        return $this;
    }
    
    public function getLog_datetime() {
        return $this->_log_datetime;
    }
    
    public function setLog_userid($log_userid) {
        $this->_log_userid = $log_userid;
        return $this;
    }
    
    public function getLog_userid() {
        return $this->_log_userid;
    }
}