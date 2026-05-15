<?php

class Atlas_Model_DbTable_PermissionGroupUsers extends Zend_Db_Table_Abstract
{
	
    protected $_name = 'permission_group_users';
	protected $_id   = 'permission_group_user_id';
	
	// setup the default adapter as jarroworders
	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get('atlas')->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function

}

?>