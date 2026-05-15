<?php

class Atlas_Model_DbTable_Customers extends Zend_Db_Table_Abstract
{

    protected $_name = "customers";
	protected $_id   = "";
	
	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get("atlas")->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function
}

?>