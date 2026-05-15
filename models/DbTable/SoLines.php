<?php

class Atlas_Model_DbTable_SoLines extends Zend_Db_Table_Abstract
{

    protected $_name = "so_lines";
	protected $_id   = "inv_line_id";
	
	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get("atlas")->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function
}

?>