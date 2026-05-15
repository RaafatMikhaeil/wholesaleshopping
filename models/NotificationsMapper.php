<?php

class Atlas_Model_NotificationsMapper
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
			$this->setDbTable('Atlas_Model_DbTable_Notifications');
		}
		return $this->_dbTable;
	} #end getDbTable() function
	
	// save the attributes of a given db object
	public function save(Atlas_Model_Notifications $notification)
	{
		// push the data into an array
		$data = $notification->toArray();
		
		// if the row in the db doesnt exist create the row
		// otherwise update the existing row
		if( NULL === ($notification_id = $notification->getNotification_id()) || $notification_id == 0 ){
			unset($data['notification_id']);
			$notification_id = $this->getDbTable()->insert($data);
			return $notification_id;
		}
		else {
			$this->getDbTable()->update($data, array('notification_id = ?' => $notification_id));
			return $notification_id;
		}
	} #end save() function
	
	// remove a row from the database that matches the id given
	public function remove($notification_id)
	{
		$this->getDbTable()->delete("notification_id='$notification_id'");
		
	} #end remove() function
	
	// find a row in the database based on the primary key and set the values
	// in the db object given by the user
	public function find($notification_id)
	{
		$notification = new Atlas_Model_Notifications();
		
		// attempt to locate the row in the database
		// if it doesn't exist throw an exception
		$result = $this->getDbTable()->find($notification_id);
		if( 0 == count($result) ){
			throw new Exception("Given entry doesn't exist");
		}
		
		// get the data and push it to the object
		$row = $result->current();
		$notification->setOptions($row->toArray());
		
		return $notification;
		
	} #end find() function
	
	// find all entries from the database for the given table
	public function fetchAll()
	{
		// gather all of the entries in the database
		// and push their values into an array
		$resultSet = $this->selectAll()->query()->fetchAll();
		$entries   = array();
		foreach( $resultSet as $row ){
			$entry = new Atlas_Model_Notifications();
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
			   ->from(array("n"=>"notifications"),
					  array("n.notification_id", "n.user_id", "n.timestamp", "n.message", "n.is_active"))
			   ->order("n.timestamp DESC");
			   
		return $select;
	} #end selectAll() function
	
	// return a select statement for the table
	public function getList()
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			->from(array("n"=>"notifications"),
				array("n.notification_id", "n.user_id", "n.timestamp", "n.message", "n.is_active", "u.name"))
			->join(array("u"=>"users"), "n.user_id=u.user_id", array())
			->order("n.timestamp DESC");
			   
		return $select;
	} #end selectAll() function
	
	// return a list of notifications that apply to a given user
	public function buildUserNotifications( $permission_group_ids )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			->from(array("n"=>"notifications"),
				array("n.notification_id", "n.timestamp", "n.message", "u.name"))
			->join(array("u"=>"users"), "n.user_id=u.user_id", array())
			->join(array("gn"=>"group_notifications"), "gn.notification_id=n.notification_id", array())
			->where("n.is_active = ?", 1)
			->where("gn.user_group_id IN (".$permission_group_ids.")")
			->order("n.timestamp DESC");
			
		return $select->query()->fetchAll();
	} #end buildUserNotifications() function
	
	// activate the notification
	public function activateNotification( $notification_id )
	{
		try {
			$notification = $this->find($notification_id);
			$notification->setIs_active(1);
			$this->save($notification);
			
			return true;
		} catch( Exception $e ) {
			return false;
		}
	} #end activateNotification() function
	
	// deactivate the notification
	public function deactivateNotification( $notification_id )
	{
		try {
			$notification = $this->find($notification_id);
			$notification->setIs_active(0);
			$this->save($notification);
			
			return true;
		} catch( Exception $e ) {
			return false;
		}
	} #end deactivateNotification() function
	
	// process data from the unit form
	public function processForm( $form_data )
	{
		if( (int)$form_data['notification_id'] > 0 ) {
			try {
				$notification = $this->find($form_data['notification_id']);
				$notification->setOptions($form_data);
				$notification->setTimestamp(date("Y-m-d H:i:s", time()))
					->setUser_id(Zend_Registry::get('user_id'));
				$notification_id = $this->save($notification);
				
				return $notification_id;
			} catch( Exception $e ) {
				return 0;
			}
		} else {
			$notification = new Atlas_Model_Notifications();
			$notification->setOptions($form_data);
			$notification->setTimestamp(date("Y-m-d H:i:s", time()))
					->setUser_id(Zend_Registry::get('user_id'));
			$notification_id = $this->save($notification);
			
			return $notification_id;
		}
	} #end processForm() function
	
}

?>