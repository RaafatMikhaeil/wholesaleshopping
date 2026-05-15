<?php

class Atlas_Model_InvPayments {

    protected $_payment_id;
    protected $_inv_id;
    protected $_user_id;
    protected $_payment_type_id;
    protected $_payment_datetime;
    protected $_payment_amount;
    protected $_payment_contact;
    
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

    public function setPayment_id($payment_id) {
        $this->_payment_id = $payment_id;
        return $this;
    }

    public function getPayment_id() {
        return $this->_payment_id;
    }
    
    public function setInv_id($inv_id) {
        $this->_inv_id = $inv_id;
        return $this;
    }

    public function getInv_id() {
        return $this->_inv_id;
    }

    public function setUser_id($user_id) {
        $this->_user_id = $user_id;
        return $this;
    }

    public function getUser_id() {
        return $this->_user_id;
    }
    
    public function setPayment_type_id($payment_type_id) {
        $this->_payment_type_id = $payment_type_id;
        return $this;
    }

    public function getPayment_type_id() {
        return $this->_payment_type_id;
    }

    public function setPayment_datetime($payment_datetime) {
        $this->_payment_datetime = $payment_datetime;
        return $this;
    }

    public function getPayment_datetime() {
        return $this->_payment_datetime;
    }

    public function setPayment_amount($payment_amount) {
        $this->_payment_amount = $payment_amount;
        return $this;
    }

    public function getPayment_amount() {
        return $this->_payment_amount;
    }

    public function setPayment_contact($payment_contact) {
        $this->_payment_contact = $payment_contact;
        return $this;
    }

    public function getPayment_contact() {
        return $this->_payment_contact;
    }
}

?>