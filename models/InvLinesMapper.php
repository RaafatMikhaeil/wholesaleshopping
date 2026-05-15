<?php

class Atlas_Model_InvLinesMapper
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
			$this->setDbTable("Atlas_Model_DbTable_InvLines");
		}
		
		return $this->_dbTable;
	} #end getDbTable function
	
	
	public function save( Atlas_Model_InvLines $entry )
	{
		// push the data into an array
		$data = $entry->toArray();
		
		// if the row in the db doesnt exist create the row
		// otherwise update the existing row
		if( NULL === ($inv_line_id = $entry->getInv_line_id()) || $inv_line_id == 0 ) {
			unset($data["inv_line_id"]);
			$inv_line_id = $this->getDbTable()->insert($data);
			return $inv_line_id;
		} else {
			$this->getDbTable()->update($data, array("inv_line_id = ?" => $inv_line_id));
			return $inv_line_id;
		}
	} #end save function
	
	
	public function remove( $inv_line_id )
	{
		$this->getDbTable()->delete("inv_line_id='$inv_line_id'");
	} #end remove function
	
        public function removeInvoice( $inv_id )
	{
		$this->getDbTable()->delete("inv_id='$inv_id'");
	} #end remove function
        
	public function find( $inv_line_id )
	{
		$entry = new Atlas_Model_InvLines();
		
		// attempt to locate the row in the database
		// if it doesn"t exist return NULL
		$result = $this->getDbTable()->find($inv_line_id);
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
			$entry = new Atlas_Model_InvLines();
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
		$select->from(array("t"=>"inv_lines"), 
				array('t.inv_line_id', 't.inv_id', 't.product_id', 't.product_price', 't.product_qty'));
		
		// return the select statement	
		return $select;
	} #end selectAll function
	
	public function buildLines($id)
	{
		// create a select statement for gathering all of the entries
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
                        ->from(array("t"=>"inv_lines"), 
				array(
                                        't.*',
                                        'p.*',
                                        new Zend_Db_Expr("CASE WHEN t.inv_line_type = 1 THEN 'SALE' WHEN t.inv_line_type = 2 THEN 'RETURN' WHEN t.inv_line_type = 3 THEN 'DAMAGE' ELSE 'SALE' END  as line_type")
                                    )
                                )
                        ->join(array("p"=>"prods"), "p.prod_id=t.product_id", array())
                        ->where("t.inv_id = ?", $id)
                        ->order(array('t.inv_line_id ASC'));
		
                $lines_info = $select->query()->fetchAll();
		// return the select statement	
		return $lines_info;
	} #end selectAll function
        
        
	public function processForm( $form_data = NULL )
	{
		if( $form_data == NULL ) {
			throw new Exception("No data given to the model for processing.");
		}
		
		if( (int)$form_data["inv_line_id"] > 0 ) {
			$entry = $this->find($form_data["inv_line_id"]);
			$entry->setOptions($form_data);
			$inv_line_id = $this->save($entry);
		} else {
			unset($form_data["inv_line_id"]);
			$entry = new Atlas_Model_InvLines();
			$entry->setOptions($form_data);
			$inv_line_id = $this->save($entry);
		}
		
		return $inv_line_id;
	} #end processForm function
	
}

?>