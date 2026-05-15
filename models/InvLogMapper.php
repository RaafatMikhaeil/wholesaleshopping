<?php

class Atlas_Model_InvLogMapper
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
			$this->setDbTable("Atlas_Model_DbTable_InvLog");
		}
		
		return $this->_dbTable;
	} #end getDbTable function
	
	
	public function save( Atlas_Model_InvLog $entry )
	{
		// push the data into an array
		$data = $entry->toArray();
		
		// if the row in the db doesnt exist create the row
		// otherwise update the existing row
		if( NULL === ($log_id = $entry->getLog_id()) || (int)$log_id == 0 ) {
			unset($data["log_id"]);
			$log_id = $this->getDbTable()->insert($data);
			return $log_id;
		} else {
			$this->getDbTable()->update($data, array("log_id = ?" => $log_id));
			return $log_id;
		}
	} #end save function
	
	
	public function remove( $log_id )
	{
		$this->getDbTable()->delete("log_id='$log_id'");
	} #end remove function
	
        public function removeLogRecord( $log_record_id , $log_type )
	{
		$this->getDbTable()->delete(["log_record_id = $log_record_id ", "log_type='$log_type'" ]);
	} #end remove function
        
	public function find( $log_id )
	{
		$entry = new Atlas_Model_InvLog();
		
		// attempt to locate the row in the database
		// if it doesn"t exist return NULL
		$result = $this->getDbTable()->find($log_id);
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
			$entry = new Atlas_Model_InvLog();
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
		$select->from(array("t"=>"inv_log"), 
				array('t.*'));
		
		// return the select statement	
		return $select;
	} #end selectAll function
	
	public function buildRecordLogs($log_record_id , $log_type)
	{
		// create a select statement for gathering all of the entries
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
                        ->from(array("l"=>"inv_log"), 
				[   
                                    'l.log_id as EntryId',
                                    'l.log_record_id as RecordId',
                                    'l.log_text as Notes',
                                    'l.log_update as DataChanges',
                                    'l.log_datetime as DateTime',
                                    'u.name as User'
                                ] )
                        ->join(array("u" => "users"), "u.user_id=l.log_userid", array())
                        ->where("l.log_record_id = $log_record_id")
                        ->where("l.log_type = '$log_type'")
                        ->order(['l.log_id DESC']);
		
		// return the select statement	
		return $select->query()->fetchAll();
	} #end buildRecordLogs function
        
	public function processForm( $form_data = NULL )
	{
		if( $form_data == NULL ) {
			throw new Exception("No data given to the model for processing.");
		}
		
		if( (int)$form_data["log_id"] > 0 ) {
			$entry = $this->find($form_data["log_id"]);
			$entry->setOptions($form_data);
			$payment_id = $this->save($entry);
		} else {
			unset($form_data["log_id"]);
			$entry = new Atlas_Model_InvLog();
			$entry->setOptions($form_data);
			$payment_id = $this->save($entry);
		}
		
		return $payment_id;
	} #end processForm function
	
}

?>