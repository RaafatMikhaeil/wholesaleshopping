<?php

class Atlas_Model_DbTable_Users extends Zend_Db_Table_Abstract
{
	
    protected $_name = 'users';
	protected $_id   = 'user_id';
	
	// setup the default adapter as jarroworders
	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get('atlas')->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function

}

?>