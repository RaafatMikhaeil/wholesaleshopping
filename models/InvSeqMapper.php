<?php

class Atlas_Model_InvSeqMapper {

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
            $this->setDbTable("Atlas_Model_DbTable_InvSeq");
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_InvSeq $entry) {
        // push the data into an array
        $data = $entry->toArray();
        $inv_id = $entry->getInv_id(); 
        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === $inv_id || (int) $inv_id == 0) {
            $inv_id = $this->getDbTable()->insert($data);
            return $inv_id;
        } else {
            $this->getDbTable()->update($data, array("inv_id = ?" => $inv_id));
            return $inv_id;
        }
    }

    public function insert(Atlas_Model_InvSeq $entry) {
        $data = $entry->toArray();
        $inv_id = $this->getDbTable()->insert($data);
        return $inv_id;
    }
    
#end save function

    public function remove($inv_id) {
        $this->getDbTable()->delete("inv_id='$inv_id'");
    }

#end remove function

    public function find($inv_id) {
        $entry = new Atlas_Model_InvSeq();

        // attempt to locate the row in the database
        // if it doesn"t exist return NULL
        $result = $this->getDbTable()->find($inv_id);
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
            $entry = new Atlas_Model_InvSeq();
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
        $select->from(array("t" => "inv_seq"),
                array('t.*'));

        // return the select statement	
        return $select;
    }

#end selectAll function

    public function buildInvId() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(
                        array("p" => "inv_seq"),
                        array('MIN(p.inv_id) as InvId')
                );

        // return the select statement	
        $result = $select->query()->fetch();
        return (int) $result['InvId'];
    }

    
    public function buildInvIds() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(
                        array("p" => "inv_seq"),
                        array('p.inv_id as InvId')
                )
                ->order(array('t.inv_id ASC'));

        // return the select statement	
        return $select->query()->fetchAll();
    }
#end selectAll function

    public function processForm($form_data = NULL) {
        if ($form_data == NULL) {
            throw new Exception("No data given to the model for processing.");
        }

        if ((int) $form_data["payment_type_id"] > 0) {
            $entry = $this->find($form_data["payment_type_id"]);
            $entry->setOptions($form_data);
            $payment_type_id = $this->save($entry);
        } else {
            unset($form_data["payment_type_id"]);
            $entry = new Atlas_Model_InvPaymentTypes();
            $entry->setOptions($form_data);
            $payment_type_id = $this->save($entry);
        }

        return $payment_type_id;
    }

#end processForm function
}

?>