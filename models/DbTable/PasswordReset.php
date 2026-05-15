<?php

class Atlas_Model_DbTable_PasswordReset extends Zend_Db_Table_Abstract
{

    protected $_name = 'password_reset';
	protected $_id   = 'reset_id';

	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get('atlas')->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function
}

?>