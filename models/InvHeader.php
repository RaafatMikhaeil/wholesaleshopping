<?php

class Atlas_Model_InvHeader {

    protected $_inv_id;
    protected $_inv_cust_id;
    protected $_inv_date;
    protected $_inv_total;
    protected $_inv_user_id;
    protected $_inv_notes;
    protected $_inv_discount;
    protected $_inv_paid;
    protected $_inv_status;
    
    protected $_inv_payment;
    protected $_inv_ccp;
    protected $_inv_cca;
    protected $_inv_shipping;
    protected $_inv_qty;
    protected $_inv_sub_total;
    protected $_inv_credits;
    
    protected $_inv_payments;
    protected $_inv_outstanding;
    
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

    public function setInv_id($inv_id) {
        $this->_inv_id = $inv_id;
        return $this;
    }

#end setInv_id function

    public function getInv_id() {
        return $this->_inv_id;
    }

#end getInv_id function

    public function setInv_cust_id($inv_cust_id) {
        $this->_inv_cust_id = $inv_cust_id;
        return $this;
    }

#end setInv_cust_id function

    public function getInv_cust_id() {
        return $this->_inv_cust_id;
    }

#end getInv_cust_id function

    public function setInv_date($inv_date) {
        $this->_inv_date = $inv_date;
        return $this;
    }

#end setInv_date function

    public function getInv_date() {
        return $this->_inv_date;
    }

#end getInv_date function

    public function setInv_total($inv_total) {
        $this->_inv_total = $inv_total;
        return $this;
    }

#end setInv_total function

    public function getInv_total() {
        return $this->_inv_total;
    }

#end getInv_total function

    public function setInv_user_id($inv_user_id) {
        $this->_inv_user_id = $inv_user_id;
        return $this;
    }

#end setInv_user_id function

    public function getInv_user_id() {
        return $this->_inv_user_id;
    }

#end getInv_user_id function

    public function setInv_notes($inv_notes) {
        $this->_inv_notes = $inv_notes;
        return $this;
    }

#end setInv_notes function

    public function getInv_notes() {
        return $this->_inv_notes;
    }

#end getInv_notes function
    
    public function setInv_discount($inv_discount) {
        $this->_inv_discount = $inv_discount;
        return $this;
    }#end setInv_discount function

    public function getInv_discount() {
        return $this->_inv_discount;
    }#end getInv_discount function
    
    public function setInv_paid($inv_paid) {
        $this->_inv_paid = $inv_paid;
        return $this;
    }#end setInv_discount function

    public function getInv_paid() {
        return $this->_inv_paid;
    }#end getInv_discount function
    
    public function setInv_status($inv_status) {
        $this->_inv_status = $inv_status;
        return $this;
    }#end setInv_discount function

    public function getInv_status() {
        return $this->_inv_status;
    }#end getInv_discount function
    
    public function setInv_payment($inv_payment) {
        $this->_inv_payment = $inv_payment;
        return $this;
    }#end setInv_discount function

    public function getInv_payment() {
        return $this->_inv_payment;
    }#end getInv_discount function
    
    public function setInv_ccp($inv_ccp) {
        $this->_inv_ccp = $inv_ccp;
        return $this;
    }#end setInv_discount function

    public function getInv_ccp() {
        return $this->_inv_ccp;
    }#end getInv_discount function
    
    public function setInv_cca($inv_cca) {
        $this->_inv_cca = $inv_cca;
        return $this;
    }#end setInv_discount function

    public function getInv_cca() {
        return $this->_inv_cca;
    }#end getInv_discount function
    
    public function setInv_shipping($inv_shipping) {
        $this->_inv_shipping = $inv_shipping;
        return $this;
    }#end setInv_discount function

    public function getInv_shipping() {
        return $this->_inv_shipping;
    }#end getInv_discount function
    
    public function setInv_qty($inv_qty) {
        $this->_inv_qty = $inv_qty;
        return $this;
    }#end setInv_discount function

    public function getInv_qty() {
        return $this->_inv_qty;
    }#end getInv_discount function
    
    public function setInv_sub_total($inv_sub_total) {
        $this->_inv_sub_total = $inv_sub_total;
        return $this;
    }#end setInv_discount function
    public function getInv_sub_total() {
        return $this->_inv_sub_total;
    }#end getInv_discount function
    
    public function setInv_credits($inv_credits) {
        $this->_inv_credits = $inv_credits;
        return $this;
    }#end setInv_discount function
    public function getInv_credits() {
        return $this->_inv_credits;
    }#end getInv_discount function
    
    public function setInv_payments($inv_payments) {
        $this->_inv_payments = $inv_payments;
        return $this;
    }
    public function getInv_payments() {
        return $this->_inv_payments;
    }
    
    public function setInv_outstanding($inv_outstanding) {
        $this->_inv_outstanding = $inv_outstanding;
        return $this;
    }
    public function getInv_outstanding() {
        return $this->_inv_outstanding;
    }
}

?>