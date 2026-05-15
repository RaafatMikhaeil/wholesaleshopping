<?php

class Atlas_Model_DbTable_LoginLogs extends Zend_Db_Table_Abstract
{

    protected $_name = 'login_logs';
	protected $_id   = 'login_id';

	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get('atlas')->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function
}

?>