<?php

class Atlas_Model_DbTable_InvLog extends Zend_Db_Table_Abstract {

    protected $_name = "inv_log";
    protected $_id = "";

    protected function _setupDatabaseAdapter() {
        $this->_db = Zend_Db::factory(Zend_Registry::get("atlas")->db);
        parent::_setupDatabaseAdapter();
    }

#end _setupDatabaseAdapter function
}

?>