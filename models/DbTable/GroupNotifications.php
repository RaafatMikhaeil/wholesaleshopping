<?php

class Atlas_Model_DbTable_GroupNotifications extends Zend_Db_Table_Abstract
{

    protected $_name = 'group_notifications';
	protected $_id   = 'group_notification_id';

	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get('atlas')->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function
}

?>