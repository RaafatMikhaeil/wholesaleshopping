<?php

class Atlas_Model_DbTable_Emails extends Zend_Db_Table_Abstract
{

    protected $_name = 'emails';
	protected $_id   = 'emails_id';

	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get('jarroworders')->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function
}

?>