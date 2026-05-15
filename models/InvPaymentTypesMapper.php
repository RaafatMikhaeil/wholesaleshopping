<?php

class Atlas_Model_InvPaymentTypesMapper
{
	protected $_dbTable;
	
	public function setDbTable( $dbTable )
	{
		// if a string was given return an object
		if( is_string($dbTable) ) {
			$dbTable = new $dbTable();
		}
		// ensure the dbTable is of the correct instance
		if( !$dbTable instanceof Zend_Db_Table_Abstract ) {
			throw new Exception("Invalid table data object provided");
		}
		
		// set the db table and return the handle
		$this->_dbTable = $dbTable;
		return $this;
	} #end setDbTable function
	
	
	public function getDbTable()
	{
		// if the object is not set, set it and return it
		if( NULL === $this->_dbTable ) {
			$this->setDbTable("Atlas_Model_DbTable_InvPaymentTypes");
		}
		
		return $this->_dbTable;
	} #end getDbTable function
	
	
	public function save( Atlas_Model_InvPaymentTypes $entry )
	{
		// push the data into an array
		$data = $entry->toArray();
		
		// if the row in the db doesnt exist create the row
		// otherwise update the existing row
		if( NULL === ($payment_type_id = $entry->getPaymentTypeId()) || $payment_type_id == 0 ) {
			unset($data["payment_type_id"]);
			$payment_type_id = $this->getDbTable()->insert($data);
			return $payment_type_id;
		} else {
			$this->getDbTable()->update($data, array("payment_type_id = ?" => $payment_type_id));
			return $payment_type_id;
		}
	} #end save function
	
	
	public function remove( $payment_type_id )
	{
		$this->getDbTable()->delete("payment_type_id='$payment_type_id'");
	} #end remove function
	
	
	public function find( $payment_type_id )
	{
		$entry = new Atlas_Model_InvPaymentTypes();
		
		// attempt to locate the row in the database
		// if it doesn"t exist return NULL
		$result = $this->getDbTable()->find($payment_type_id);
		if( 0 == count($result) ) {
    		throw new Exception("Given entry doesn't exist in the system.");
		}
		
		// get the data and push it to the object
		$row = $result->current();
		$entry->setOptions($row->toArray());
		
		return $entry;
	} #end find function
	
	
	public function fetchAll()
	{
		// gather all of the entries in the database
		// and push their values into an array
		$results = $this->selectAll()->query()->fetchAll();
		$entries = array();
		foreach( $results as $row ) {
			$entry = new Atlas_Model_InvPaymentTypes();
			$entry->setOptions($row);
			$entries[] = $entry;
		}
		
		// return the results
		return $entries;
	} #end fetchAll function
	
	
	public function selectAll()
	{
		// create a select statement for gathering all of the entries
		$select = $this->getDbTable()->select();
		$select->from(array("t"=>"inv_payment_types"), 
				array('t.*'));
		
		// return the select statement	
		return $select;
	} #end selectAll function
	
	public function buildPaymentTypes()
	{
		// create a select statement for gathering all of the entries
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
                        ->from(
                                array("p"=>"inv_payment_types"), 
				array('p.*')
                                )
                        ->order(array('p.payment_type ASC'));
		
		// return the select statement	
		return $select->query()->fetchAll();
	} #end selectAll function
        
	public function processForm( $form_data = NULL )
	{
		if( $form_data == NULL ) {
			throw new Exception("No data given to the model for processing.");
		}
		
		if( (int)$form_data["payment_type_id"] > 0 ) {
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
	} #end processForm function
	
}

?>