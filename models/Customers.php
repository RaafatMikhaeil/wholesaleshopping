<?php

class Atlas_Model_Customers {

    protected $_cust_id;
    protected $_cust_name;
    protected $_cust_address;
    protected $_cust_city;
    protected $_cust_state;
    protected $_cust_zip;
    protected $_cust_phone;
    protected $_cust_email;
    protected $_cust_contact;
    protected $_cust_lic_no;
    protected $_cust_lic_exp;
    protected $_cust_status;

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

#end toArray function

    public function setCust_id($cust_id) {
        $this->_cust_id = $cust_id;
        return $this;
    }

#end setCust_id function

    public function getCust_id() {
        return $this->_cust_id;
    }

#end getCust_id function

    public function setCust_name($cust_name) {
        $this->_cust_name = $cust_name;
        return $this;
    }

#end setCust_name function

    public function getCust_name() {
        return $this->_cust_name;
    }

#end getCust_name function

    public function setCust_address($cust_address) {
        $this->_cust_address = $cust_address;
        return $this;
    }

#end setCust_address function

    public function getCust_address() {
        return $this->_cust_address;
    }

#end getCust_address function

    public function setCust_city($cust_city) {
        $this->_cust_city = $cust_city;
        return $this;
    }

#end setCust_city function

    public function getCust_city() {
        return $this->_cust_city;
    }

#end getCust_city function

    public function setCust_state($cust_state) {
        $this->_cust_state = $cust_state;
        return $this;
    }

#end setCust_state function

    public function getCust_state() {
        return $this->_cust_state;
    }

#end getCust_state function

    public function setCust_zip($cust_zip) {
        $this->_cust_zip = $cust_zip;
        return $this;
    }

#end setCust_zip function

    public function getCust_zip() {
        return $this->_cust_zip;
    }

#end getCust_zip function

    public function setCust_phone($cust_phone) {
        $this->_cust_phone = $cust_phone;
        return $this;
    }

#end setCust_phone function

    public function getCust_phone() {
        return $this->_cust_phone;
    }

#end getCust_phone function

    public function setCust_email($cust_email) {
        $this->_cust_email = $cust_email;
        return $this;
    }

#end setCust_email function

    public function getCust_email() {
        return $this->_cust_email;
    }

#end getCust_email function

    public function setCust_contact($cust_contact) {
        $this->_cust_contact = $cust_contact;
        return $this;
    }

#end setCust_contact function

    public function getCust_contact() {
        return $this->_cust_contact;
    }

#end getCust_contact function

    public function setCust_lic_no($cust_lic_no) {
        $this->_cust_lic_no = $cust_lic_no;
        return $this;
    }

#end setCust_lic_no function

    public function getCust_lic_no() {
        return $this->_cust_lic_no;
    }

#end getCust_lic_no function

    public function setCust_lic_exp($cust_lic_exp) {
        $this->_cust_lic_exp = $cust_lic_exp;
        return $this;
    }

#end setCust_lic_no function

    public function getCust_lic_exp() {
        return $this->_cust_lic_exp;
    }

    
    public function setCust_status($cust_status) {
        $this->_cust_status = $cust_status;
        return $this;
    }

#end setCust_lic_no function

    public function getCust_status() {
        return $this->_cust_status;
    }
#end getCust_lic_no function
}

?>