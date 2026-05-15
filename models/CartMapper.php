<?php

class Atlas_Model_CartMapper {

    protected $_dbTable;

    public function setDbTable($dbTable) {
        // if a string was given return an object
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        // ensure the dbTable is of the correct instance
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception("Invalid table data object provided");
        }

        // set the db table and return the handle
        $this->_dbTable = $dbTable;
        return $this;
    }

#end setDbTable function

    public function getDbTable() {
        // if the object is not set, set it and return it
        if (NULL === $this->_dbTable) {
            $this->setDbTable("Atlas_Model_DbTable_Cart");
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_Cart $entry) {
        // push the data into an array
        $data = $entry->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($cat_id = $entry->getCart_id()) || $cat_id == 0) {
            unset($data["cart_id"]);
            $cat_id = $this->getDbTable()->insert($data);
            return $cat_id;
        } else {
            $this->getDbTable()->update($data, array("cart_id = ?" => $cat_id));
            return $cat_id;
        }
    }

#end save function

    public function remove($cart_id) {
        $this->getDbTable()->delete("cart_id='$cart_id'");
    }

    public function clear($cust_id) {
        $this->getDbTable()->delete("cust_id='$cust_id'");
    }
#end remove function

    public function find($cart_id) {
        $entry = new Atlas_Model_ProdsCart();

        // attempt to locate the row in the database
        // if it doesn"t exist return NULL
        $result = $this->getDbTable()->find($cart_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist in the system.");
        }

        // get the data and push it to the object
        $row = $result->current();
        $entry->setOptions($row->toArray());

        return $entry;
    }

#end find function

    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $results = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($results as $row) {
            $entry = new Atlas_Model_Cart();
            $entry->setOptions($row);
            $entries[] = $entry;
        }

        // return the results
        return $entries;
    }

#end fetchAll function

    public function selectAll() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "cart"),
                        array('t.*'))
                ->order(array('t.cat_id asc'));

        // return the select statement	
        return $select;
    }

#end selectAll function

    public function buildUserCart($cust_id) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array("t" => "cart"),
                        array(
                            't.cart_id as id',
                            'p.prod_desc as product_name',
                            'p.prod_sale as product_price',
                            'p.prod_image as product_image',
                            'p.prod_code as product_code',
                            't.qty as qty',
                            new Zend_Db_Expr('(t.qty*p.prod_sale) as total_price'),
                            ))
                ->join(array("p"=>"prods"), "t.prod_id=p.prod_id", array())
                ->where('t.cust_id = ?', $cust_id);
        $result = $select->query()->fetchAll();
        return $result;
    }
    
    public function buildUserCartCheckOut($cust_id) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array("t" => "cart"),
                        array(
                            't.cart_id as id',
                            't.prod_id as product_id',
                            'p.prod_desc as product_name',
                            'p.prod_sale as product_price',
                            'p.prod_image as product_image',
                            'p.prod_code as product_code',
                            't.qty as qty',
                            new Zend_Db_Expr("CONCAT(p.prod_desc, '(',t.qty,')') AS ItemQty"),
                            new Zend_Db_Expr('(t.qty*p.prod_sale) as total_price'),
                            ))
                ->join(array("p"=>"prods"), "t.prod_id=p.prod_id", array())
                ->where('t.cust_id = ?', $cust_id);
        $result = $select->query()->fetchAll();
        return $result;
    }

    public function buildUserCartCount($cust_id) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false);
        $select->from(array("t" => "cart"),
                        array(
                            'count(*) as item_count',
                            ))
                ->where('t.cust_id = ?', $cust_id);
        $result = $select->query()->fetch();
        return $result['item_count'];
    }
    
    public function CheckItemCart($cust_id,$prod_id) {
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "cart"),
                        array('*'))
                ->where('t.cust_id = ?', $cust_id)
                ->where('t.prod_id = ?', $prod_id);
        $result = $select->query()->fetchAll();
        if(count($result) == 0){
            return true;
        }else{
            return false;
        }
    }
    
    public function buildItemCart($cust_id,$prod_id) {
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "cart"),
                        array('*'))
                ->where('t.cust_id = ?', $cust_id)
                ->where('t.prod_id = ?', $prod_id);
        $result = $select->query()->fetch();
        return $result;
    }
    
    public function updateCartItem($prod_id,$cust_id,$qty)
    {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("al"=>"cart"), 
                        array('al.*'))
                ->where("cart_id = ?", $prod_id)
                ->where("cust_id = ?", $cust_id);

        // return the select statement	
        $result = $select->query()->fetch();

        if ($result){
            $entry = new Atlas_Model_Cart();
            $entry->setOptions($result);
            $entry->setQty($qty);
            $this->save($entry);
        }   
    } #end updateOrderItem function

}

?>