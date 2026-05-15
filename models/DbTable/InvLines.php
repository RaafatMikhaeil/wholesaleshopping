<?php

class Atlas_Model_DbTable_InvLines extends Zend_Db_Table_Abstract
{

    protected $_name = "inv_lines";
	protected $_id   = "inv_line_id";
	
	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get("atlas")->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function
}

?>