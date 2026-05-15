<?php

class Atlas_Model_DbTable_PageGroups extends Zend_Db_Table_Abstract
{
	
    protected $_name = 'page_groups';
	protected $_id   = 'page_group_id';
	
	// setup the default adapter as jarroworders
	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get('atlas')->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function

}

?>