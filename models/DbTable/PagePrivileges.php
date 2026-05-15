<?php

class Atlas_Model_DbTable_PagePrivileges extends Zend_Db_Table_Abstract
{
	
    protected $_name = 'page_privileges';
	protected $_id   = 'page_privilege_id';
	
	// setup the default adapter as jarroworders
	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get('atlas')->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function

}

?>