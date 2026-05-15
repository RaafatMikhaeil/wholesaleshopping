<?php

class Atlas_Model_DbTable_InvPayments extends Zend_Db_Table_Abstract
{

    protected $_name = "inv_payments";
	protected $_id   = "payment_id";
	
	protected function _setupDatabaseAdapter()
	{
		$this->_db = Zend_Db::factory(Zend_Registry::get("atlas")->db);
		parent::_setupDatabaseAdapter();
	} #end _setupDatabaseAdapter function
}

?>