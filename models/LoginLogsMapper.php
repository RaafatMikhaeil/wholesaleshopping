<?php

class Atlas_Model_LoginLogsMapper
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
			$this->setDbTable('Atlas_Model_DbTable_LoginLogs');
		}
		return $this->_dbTable;
	} #end getDbTable() function
	
	// save the attributes of a given db object
	public function save(Atlas_Model_LoginLogs $l_entry)
	{
		// push the data into an array
		$data = $l_entry->toArray();
		
		// if the row in the db doesnt exist create the row
		// otherwise update the existing row
		if( NULL === ($login_id = $l_entry->getLogin_id()) || $login_id == 0 ){
			unset($data['login_id']);
			$login_id = $this->getDbTable()->insert($data);
			return $login_id;
		}
		else {
			$this->getDbTable()->update($data, array('login_id = ?' => $login_id));
			return $login_id;
		}
	} #end save() function
	
	// remove a row from the database that matches the id given
	public function remove($login_id)
	{
		$this->getDbTable()->delete("login_id='$login_id'");
		
	} #end remove() function
	
	// find a row in the database based on the primary key and set the values
	// in the db object given by the user
	public function find($login_id)
	{
		$l_entry = new Atlas_Model_LoginLogs();
		
		// attempt to locate the row in the database
		// if it doesn't exist throw an exception
		$result = $this->getDbTable()->find($login_id);
		if( 0 == count($result) ){
			throw new Exception("Given entry doesn't exist");
		}
		
		// get the data and push it to the object
		$row = $result->current();
		$l_entry->setOptions($row->toArray());
		
		return $l_entry;
		
	} #end find() function
	
	// find all entries from the database for the given table
	public function fetchAll()
	{
		// gather all of the entries in the database
		// and push their values into an array
		$resultSet = $this->selectAll()->query()->fetchAll();
		$entries   = array();
		foreach( $resultSet as $row ){
			$entry = new Atlas_Model_LoginLogs();
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
			->from(array("ll"=>"login_logs"),
				array("ll.login_id", "ll.user_id", "ll.login_date"))
			->order(array("ll.login_date DESC", "ll.user_id ASC"));
			   
		return $select;
	} #end selectAll() function
	
	// return a full list of recent logins
	public function getLoginList( $search="" )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			->from(array("ll"=>"login_logs"),
				array("ll.login_id", "ll.user_id", "ll.login_date",
					"u.username", "u.name"))
			->join(array("u"=>"users"), "ll.user_id=u.user_id", array());
		if( trim($search) != "" ) {
			$select->where("u.username LIKE '%".$search."%' OR u.name LIKE '%".$search."%'");
		}
		$select->order(array("ll.login_date DESC", "u.name ASC"));
			   
		return $select;
	} #end getLoginList() function

    public function getlastRecord($user_id)
    {
        $select = $this->getDbTable()->select();
        $select->from(array("t"=>"login_logs"),
                        array('t.login_id', 't.user_id', 't.login_date', 't.logout_date'))
                ->where("t.user_id = ?", $user_id)
                ->order(array("t.login_id DESC"))
                ->limit(1);
        // return the select statement
        return $select->query()->fetch();
     }
	
}

?>