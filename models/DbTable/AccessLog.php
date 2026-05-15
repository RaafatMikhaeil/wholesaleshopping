<?php

class Atlas_Model_DbTable_AccessLog extends Zend_Db_Table_Abstract
{

    protected $_name = 'access_log';
	protected $_id   = 'access_log_id';

	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get('atlas')->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function
}

?>