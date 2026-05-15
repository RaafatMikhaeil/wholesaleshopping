<?php

class Atlas_Model_PasswordResetMapper
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
			$this->setDbTable('Atlas_Model_DbTable_PasswordReset');
		}
		return $this->_dbTable;
	} #end getDbTable() function
	
	// save the attributes of a given db object
	public function save(Atlas_Model_PasswordReset $reset)
	{
		// push the data into an array
		$data = $reset->toArray();
		
		// if the row in the db doesnt exist create the row
		// otherwise update the existing row
		if( NULL === ($reset_id = $reset->getReset_id()) || $reset_id == 0 ){
			unset($data['reset_id']);
			$reset_id = $this->getDbTable()->insert($data);
			return $reset_id;
		}
		else {
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
		$reset = new Atlas_Model_PasswordReset();
		
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
			$entry = new Atlas_Model_PasswordReset();
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
			   ->from(array("pr"=>"password_reset"),
					  array("pr.reset_id", "pr.user_id", "pr.last_reset", "pr.last_password"));
			   
		return $select;
	} #end selectAll() function
	
	// test length of time before last reset
	public function buildResetTest( $user_id )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			->from(array("pr"=>"password_reset"),
				array("pr.last_reset"))
			->where("pr.user_id = ?", $user_id);
		$result = $select->query()->fetchAll();
		
		if( !isset($result[0]['last_reset']) ) {
			$data = new Atlas_Model_PasswordReset();
			$data->setUser_id($user_id)
				->setLast_reset(date("Y-m-d", time()));
			$this->save($data);
			
			return false;
		}
		
		$last_retest = strtotime($result[0]['last_reset']);
		$next_retest = date(
			"Ymd", 
			mktime(
				0,0,0,
				(date("m", $last_retest)+Zend_Registry::get("password_reset_time") ),
				date("d", $last_retest),
				date("Y", $last_retest)
                        ));
		if( date("Ymd", time()) > $next_retest ) {
			return true;
		} else {
			return false;
		}
	} #end buildRetestTest() function
	
	// make sure the last password is not equal to the current password
	public function buildPasswordTest( $user_id )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			->from(array("pr"=>"password_reset"),
				array("COUNT(*) AS match"))
			->join(array("u"=>"users"), "pr.last_password=u.password AND pr.user_id=u.user_id", array())
			->where("pr.user_id = ?", $user_id);
		$result = $select->query()->fetchAll();
		
		if( $result[0]['match'] > 0 ) {
			return true;
		} else {
			return false;
		}
	} #end buildPasswordTest() function
	
	// make sure the new password doesn't match the old one
	public function buildPasswordCheck( $user_id, $password )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			->from(array("pr"=>"password_reset"),
				array("COUNT(*) AS match"))
			->where("pr.user_id = ?", $user_id)
			->where("pr.last_password = ?", $password);
		$result = $select->query()->fetchAll();
		
		if( $result[0]['match'] > 0 ) {
			return true;
		} else {
			return false;
		}
	} #end buildPasswordCheck() function
	
	// update the timestamp of last reset
	public function updateTime( $user_id )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			->from(array("pr"=>"password_reset"),
				array("pr.reset_id", "pr.user_id", "pr.last_reset", "pr.last_password"))
			->where("pr.user_id = ?", $user_id);
		$results = $select->query()->fetchAll();
		
		$reset = new Atlas_Model_PasswordReset();
		$reset->setOptions($results[0]);
		$reset->setLast_reset(date("Y-m-d", time()));
		$this->save($reset);
	} #end updateTime() function
	
	// update the password of the last reset
	public function updatePassword( $user_id )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			->from(array("pr"=>"password_reset"),
				array("pr.reset_id", "pr.user_id", "pr.last_reset", "pr.last_password"))
			->where("pr.user_id = ?", $user_id);
		$results = $select->query()->fetchAll();
		
		$mapper   = new Atlas_Model_UsersMapper();
		$password = $mapper->buildPassword($user_id);
		
		$reset = new Atlas_Model_PasswordReset();
		$reset->setOptions($results[0]);
		$reset->setLast_password($password);
		$this->save($reset);
	} #end updatePassword() function
	
}

?>