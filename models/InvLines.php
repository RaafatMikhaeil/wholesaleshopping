<?php

class Atlas_Model_InvLines {

    protected $_inv_line_id;
    protected $_inv_id;
    protected $_product_id;
    protected $_product_price;
    protected $_product_qty;
    protected $_inv_line_type;
    protected $_inv_last_pp;

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

    public function setInv_line_id($inv_line_id) {
        $this->_inv_line_id = $inv_line_id;
        return $this;
    }

#end setInv_line_id function

    public function getInv_line_id() {
        return $this->_inv_line_id;
    }

#end getInv_line_id function

    public function setInv_id($inv_id) {
        $this->_inv_id = $inv_id;
        return $this;
    }

#end setInv_id function

    public function getInv_id() {
        return $this->_inv_id;
    }

#end getInv_id function

    public function setProduct_id($product_id) {
        $this->_product_id = $product_id;
        return $this;
    }

#end setProduct_id function

    public function getProduct_id() {
        return $this->_product_id;
    }

#end getProduct_id function

    public function setProduct_price($product_price) {
        $this->_product_price = $product_price;
        return $this;
    }

#end setProduct_price function

    public function getProduct_price() {
        return $this->_product_price;
    }

#end getProduct_price function

    public function setProduct_qty($product_qty) {
        $this->_product_qty = $product_qty;
        return $this;
    }
    public function getProduct_qty() {
        return $this->_product_qty;
    }

    public function setInv_line_type($inv_line_type) {
        $this->_inv_line_type = $inv_line_type;
        return $this;
    }
    public function getInv_line_type() {
        return $this->_inv_line_type;
    }
    
    public function setInv_last_pp($inv_last_pp) {
        $this->_inv_last_pp = $inv_last_pp;
        return $this;
    }
    public function getInv_last_pp() {
        return $this->_inv_last_pp;
    }
    
}

?>