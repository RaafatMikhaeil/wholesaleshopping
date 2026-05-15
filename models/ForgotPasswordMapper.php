<?php

class Atlas_Model_ForgotPasswordMapper
{
	protected $_dbTable;
	
	// set the default db handle
	public function setDbTable($dbTable)
	{
		// if a string was given return an object
		if( is_string($dbTable) ){
			$dbTable = new $dbTable();
		}
		// ensure the dbTable is of the correct instance
		if( !$dbTable instanceof Zend_Db_Table_Abstract ){
			throw new Exception('Invalid table data object provided');
		}
		
		// set the db table and return the handle
		$this->_dbTable = $dbTable;
		return $this;
	} #end setDbTable() function
	
	// return the default db handle
	public function getDbTable()
	{
		// if the object is not set, set it and return it
		if( NULL === $this->_dbTable ){
			$this->setDbTable('Atlas_Model_DbTable_ForgotPassword');
		}
		return $this->_dbTable;
	} #end getDbTable() function
	
	// save the attributes of a given db object
	public function save(Atlas_Model_ForgotPassword $reset)
	{
		// push the data into an array
		$data = $reset->toArray();
		
		// if the row in the db doesnt exist create the row
		// otherwise update the existing row
		if( NULL === ($reset_id = $reset->getReset_id()) || $reset_id == 0 ){
			unset($data['reset_id']);
			$reset_id = $this->getDbTable()->insert($data);
			return $reset_id;
		} else {
			$this->getDbTable()->update($data, array('reset_id = ?' => $reset_id));
			return $reset_id;
		}
	} #end save() function
	
	// remove a row from the database that matches the id given
	public function remove($reset_id)
	{
		$this->getDbTable()->delete("reset_id='$reset_id'");
		
	} #end remove() function
	
	// find a row in the database based on the primary key and set the values
	// in the db object given by the user
	public function find($reset_id)
	{
		$reset = new Atlas_Model_ForgotPassword();
		
		// attempt to locate the row in the database
		// if it doesn't exist throw an exception
		$result = $this->getDbTable()->find($reset_id);
		if( 0 == count($result) ){
			throw new Exception("Given entry doesn't exist");
		}
		
		// get the data and push it to the object
		$row = $result->current();
		$reset->setOptions($row->toArray());
		
		return $reset;
		
	} #end find() function
	
	// find all entries from the database for the given table
	public function fetchAll()
	{
		// gather all of the entries in the database
		// and push their values into an array
		$resultSet = $this->selectAll()->query()->fetchAll();
		$entries   = array();
		foreach( $resultSet as $row ){
			$entry = new Atlas_Model_ForgotPassword();
			$entry->setOptions($row);
			
			$entries[] = $entry;
		}
		
		// return the results
		return $entries;
	} #end fetchAll() function
	
	// transform a select statement into a result set
	public function fetch($select = NULL)
	{
		if( $select != NULL ) {
			return $select->query()->fetchAll();
		} else {
			return array();
		}
		
	} #end fetch() function
	
	// return a select statement for the table
	public function selectAll()
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			   ->from(array("fp"=>"forgot_password"),
					  array("fp.reset_id", "fp.user_id", "fp.key", "fp.timestamp", "fp.ip", "fp.agent", "fp.fa_timestamp",
					  	"fp.failed_attempts"));
			   
		return $select;
	} #end selectAll() function
	
	public function findUser( $user_id )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			->from(array("fp"=>"forgot_password"),
				array("fp.reset_id", "fp.user_id", "fp.key", "fp.timestamp", "fp.ip", "fp.agent", "fp.fa_timestamp",
					  	"fp.failed_attempts"))
			->where("fp.user_id = ?", $user_id);
		
		return $select->query()->fetchAll();
	} #end findUser() function
	
	// check the key pair
	public function checkKeyPair( $user_id, $key, $ip, $agent )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			->from(array("fp"=>"forgot_password"),
				array("fp.reset_id", "fp.user_id", "fp.key", "fp.timestamp", "fp.ip", "fp.agent", "fp.fa_timestamp",
					  	"fp.failed_attempts"))
			->where("fp.user_id = ?", $user_id)
			->where("fp.key = ?",     $user_id."_".$key)
			->where("fp.ip = ?",      $ip)
			->where("fp.agent = ?",   $agent);
		$result = $select->query()->fetchAll();
		
		if( is_array($result) && count($result) > 0 ) {
			$current = time();
			$key_set = strtotime($result[0]['timestamp']);
			$minutes = round(abs($current-$key_set)/60,2);
			if( $minutes > 30 ) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	} #end checkKeyPair() function
	
	public function increaseFailedAttempts( $user_id )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			->from(array("fp"=>"forgot_password"),
				array("fp.reset_id", "fp.user_id", "fp.key", "fp.timestamp", "fp.ip", "fp.agent", "fp.fa_timestamp",
					  	"fp.failed_attempts"))
			->where("fp.user_id = ?", $user_id);
		$result = $select->query()->fetchAll();
		
		$entry = new Atlas_Model_ForgotPassword();
		if( is_array($result) && count($result) > 0 ) {
			$entry->setOptions($result[0]);
			$tokens = explode(" ", $result[0]['fa_timestamp']);
			if( $tokens[0] != date("Y-m-d", time()) ) {
				$entry->setFa_timestamp(date("Y-m-d H:i:s", time()))
					->setFailed_attempts(1);
			} else {
				$entry->setFailed_attempts($result[0]['failed_attempts']+1);
			}
		} else {
			$entry->setUser_id($user_id)
				->setKey("x")
				->setTimestamp("x")
				->setIp("x")
				->setAgent("x")
				->setFa_timestamp(date("Y-m-d H:i:s", time()))
				->setFailed_attempts(1);
		}
		
		$this->save($entry);
		return $entry->getFailed_attempts();
	} #end increaseFailedAttempts() function
	
	public function resetFailedAttempts( $user_id )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			->from(array("fp"=>"forgot_password"),
				array("fp.reset_id", "fp.user_id", "fp.key", "fp.timestamp", "fp.ip", "fp.agent", "fp.fa_timestamp",
					  	"fp.failed_attempts"))
			->where("fp.user_id = ?", $user_id);
		$result = $select->query()->fetchAll();
		
		$entry = new Atlas_Model_ForgotPassword();
		if( is_array($result) && count($result) > 0 ) {
			$entry->setOptions($result[0])
				->setFailed_attempts(0);
			$this->save($entry);
		}
	} #end resetFailedAttempts() function
        
        
        // check the key pair
	public function checkKey( $key, $ip, $agent )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			->from(array("fp"=>"forgot_password"),
				array("fp.reset_id", "fp.user_id", "fp.key", "fp.timestamp", "fp.ip", "fp.agent", "fp.fa_timestamp",
					  	"fp.failed_attempts"))
			->where("fp.key = ?",     $key)
			->where("fp.ip = ?",      $ip)
			->where("fp.agent = ?",   $agent);
		$result = $select->query()->fetchAll();
		
		if( is_array($result) && count($result) > 0 ) {
			$current = time();
			$key_set = strtotime($result[0]['timestamp']);
			$minutes = round(abs($current-$key_set)/60,2);
			if( $minutes > 360 ) {
				return false;
			} else {
				return $result[0]['user_id'];
			}
		} else {
			return false;
		}
	} #end checkKeyPair() function
	
}

?>