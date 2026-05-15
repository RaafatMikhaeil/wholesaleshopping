<?php

class Atlas_Model_DbTable_Cart extends Zend_Db_Table_Abstract
{

    protected $_name = "cart";
	protected $_id   = "";
	
	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get("atlas")->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function
}

?>