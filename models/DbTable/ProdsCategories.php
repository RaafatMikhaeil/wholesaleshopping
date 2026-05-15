<?php

class Atlas_Model_DbTable_ProdsCategories extends Zend_Db_Table_Abstract
{

    protected $_name = "prods_categories";
	protected $_id   = "";
	
	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get("atlas")->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function
}

?>