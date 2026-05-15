<?php

class Atlas_Model_DbTable_UserNotifications extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_notifications';
	protected $_id   = 'user_notification_id';

	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get('atlas')->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function
}

?>