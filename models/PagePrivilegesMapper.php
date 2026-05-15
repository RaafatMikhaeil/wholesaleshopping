<?php

class Atlas_Model_PagePrivilegesMapper
{
	protected $_dbTable;
	
	// set the default db handle
	public function setDbTable($dbTable)
	{
		// if a string was given return an object
		if( is_string($dbTable) ){
			$dbTable = new $dbTable();
		}
		// ensure the dbTable is of the correct instance
		if( !$dbTable instanceof Zend_Db_Table_Abstract ){
			throw new Exception('Invalid table data object provided');
		}
		
		// set the db table and return the handle
		$this->_dbTable = $dbTable;
		return $this;
	} #end setDbTable() function
	
	// return the default db handle
	public function getDbTable()
	{
		// if the object is not set, set it and return it
		if( NULL === $this->_dbTable ){
			$this->setDbTable('Atlas_Model_DbTable_PagePrivileges');
		}
		return $this->_dbTable;
	} #end getDbTable() function
	
	// save the attributes of a given db object
	public function save(Atlas_Model_PagePrivileges $page_privilege)
	{
		// push the data into an array
		$data = $page_privilege->toArray();
		
		// if the row in the db doesnt exist create the row
		// otherwise update the existing row
		if( NULL === ($page_privilege_id = $page_privilege->getPage_privilege_id()) || $page_privilege_id == 0 ){
			unset($data['page_privilege_id']);
			$page_privilege_id = $this->getDbTable()->insert($data);
			return $page_privilege_id;
		}
		else {
			$this->getDbTable()->update($data, array('page_privilege_id = ?' => $page_privilege_id));
			return $page_privilege_id;
		}
	} #end save() function
	
	// remove a row from the database that matches the id given
	public function remove($page_id, $permission_group_id)
	{
		$this->getDbTable()->delete("page_id='$page_id' AND permission_group_id='$permission_group_id'");
		
	} #end remove() function
	
	// find a row in the database based on the primary key and set the values
	// in the db object given by the user
	public function find($page_id, $permission_group_id)
	{
		$user = new Atlas_Model_PagePrivileges();
		
		// attempt to locate the row in the database
		// if it doesn't exist throw an exception
		$result = $this->getDbTable()->find("page_id='$page_id' AND permission_group_id='$permission_group_id'");
		if( 0 == count($result) ){
			throw new Exception("Given entry doesn't exist");
		}
		
		// get the data and push it to the object
		$row = $result->current();
		$user->setOptions($row->toArray());
		
		return $user;
		
	} #end find() function
	
	// find all entries from the database for the given table
	public function fetchAll()
	{
		// gather all of the entries in the database
		// and push their values into an array
		$resultSet = $this->selectAll()->query()->fetchAll();
		$entries   = array();
		foreach( $resultSet as $row ){
			$entry = new Atlas_Model_PagePrivileges();
			$entry->setOptions($row->toArray());
			
			$entries[] = $entry;
		}
		
		// return the results
		return $entries;
	} #end fetchAll() function
	
	// transform a select statement into a result set
	public function fetch($select = NULL)
	{
		if( $select != NULL ) {
			return $select->query()->fetchAll();
		} else {
			return array();
		}
		
	} #end fetch() function
	
	// return a select statement for the table
	public function selectAll()
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			   ->from(array("pp"=>"page_privileges"),
					  array("pp.page_privilege_id", "pp.page_id", "pp.permission_group_id"));
			   
		return $select;
	} #end selectAll() function
	
	// return a list of all the pages a particular user can use
	public function getGroupPagePrivileges( $permission_group_id )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			   ->from(array("p"=>"pages"),
		              array("pg.page_group_id", "pg.page_group_name", "pg.image",
					        "p.page_id", "p.page_name", "p.path", "p.page_active", "p.show_in_dashboard", 
							"pp.permission_group_id"))
		       ->join(array("pg"=>"page_groups"), "pg.page_group_id=p.page_group_id", array())
			   ->join(array("pp"=>"page_privileges"), "pp.page_id=p.page_id", array())
			   ->where("pp.permission_group_id = ?", $permission_group_id)
			   ->order(array("pg.page_group_name", "p.page_name"));

		return $select;
	} #end getGroupPagePrivileges() function
	
	// return a list of all the pages a particular user can use
	public function getUserPagePrivileges( $user_id )
	{
		$permission_group_mapper = new Atlas_Model_PermissionGroupsMapper();
		$permission_group_ids    = $permission_group_mapper->buildUserPermissionGroups($user_id);
		
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			   ->from(array("p"=>"pages"),
		              array("pg.page_group_id", "pg.page_group_name", "pg.image",
					        "p.page_id", "p.page_name", "p.path", 
							"pp.permission_group_id"))
		       ->join(array("pg"=>"page_groups"), "pg.page_group_id=p.page_group_id", array())
			   ->join(array("pp"=>"page_privileges"), "pp.page_id=p.page_id", array())
			   ->where("pp.permission_group_id IN (".$permission_group_ids.")")
			   ->where("p.page_active = ?", 1)
			   ->where("p.show_in_dashboard = ?", 1)
			   ->order(array("pg.page_group_name", "p.page_name"));
			   
		return $select;
	} #end getUserPagePrivileges() function
	
	// return a page list structure of all the pages in the given group
	public function buildPageList($permission_group_id)
	{
		$select  = $this->getGroupPagePrivileges($permission_group_id);
		$results = $select->query()->fetchAll();

		$final_results = array();
		foreach( $results as $result ) {
			$final_results[$result['page_id']] = $result;
		}
		
		return $final_results;
	} #end buildPageList() function
	
	// add the selected group to the selected page if they're not already there
	public function addGroupToPage($group_id, $page_id)
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			   ->from(array("pp"=>"page_privileges"),
			   		  array("COUNT(*) AS in_page"))
			   ->where("pp.permission_group_id = ?", $group_id)
			   ->where("pp.page_id = ?", $page_id);
		
		$result = $select->query()->fetch();
		if( $result['in_page'] <= 0 ) {
			$page_privilege = new Atlas_Model_PagePrivileges();
			$page_privilege->setPermission_group_id($group_id)
						   ->setPage_id($page_id);
			
			$this->save($page_privilege);
		}
	} #end function addGroupToPage() function
	
	// remove the selected group from the selected page
	public function removeGroupFromPage($group_id, $page_id)
	{
		$this->getDbTable()->delete("permission_group_id=$group_id AND page_id=$page_id");
	} #end function removeGroupFromPage() function
         
         
	// remove the selected group from the selected page
	public function getPageGroups( $page_id)
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			   ->from(array("pp"=>"page_privileges"),
					  array("pp.page_privilege_id", "pp.page_id", "pp.permission_group_id", "pg.permission_group_title"))
                            ->join(array("pg"=>"permission_groups"), "pp.permission_group_id = pg.permission_group_id", array())
//                            ->joinLeft(array("pgu"=>"permission_group_users"), "pp.permission_group_id = pgu.permission_group_id", array())
//                            ->joinLeft(array("u"=>"users"), "u.user_id = pgu.user_id", array())
                            ->where("pp.page_id = ?", $page_id);
                
                $result = $this->fetch($select);
                
                foreach($result as $key => $val) {
                    $select2 = $this->getDbTable()->select();
                    $select2->setIntegrityCheck(false)
                               ->from(array("pgu"=>"permission_group_users"),
                                              array("pgu.permission_group_id", "u.user_id", "u.name"))
                                ->joinLeft(array("u"=>"users"), "u.user_id = pgu.user_id", array())
                                ->where("pgu.permission_group_id = ?", $val['permission_group_id']);
                    $result[$key]['user']= $this->fetch($select2);
                    
                }
//                print_r( '<pre>');
//                print_r( $result);
//                exit;
			   
		return $result;
	} 
	
    // return a list of all the pages a particular user can use by parent
    public function getGroupPagePrivilegesByParent( $permission_group_id )
    {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                   ->from(array("p"=>"pages"),
                      array("pg.page_group_id", "pg.page_group_name", "pg.image","p.page_id", 
                                "p.page_name", "p.path", "p.page_active", "p.show_in_dashboard", "pp.permission_group_id"))
               ->join(array("pg"=>"page_groups"), "pg.page_group_id=p.page_parent_id", array())
                   ->join(array("pp"=>"page_privileges"), "pp.page_id=p.page_id", array())
                   ->where("pp.permission_group_id = ?", $permission_group_id)
                   ->order(array("pg.page_group_name", "p.page_name"));

            return $select;
    } #end getGroupPagePrivilegesByParent() function

    // return a page list structure by of all the pages in the given group by parent
    public function buildPageListByParent($permission_group_id)
    {
        $select  = $this->getGroupPagePrivilegesByParent($permission_group_id);
        $results = $select->query()->fetchAll();

        $final_results = array();
        foreach( $results as $result ) {
                $final_results[$result['page_id']] = $result;
        }

        return $final_results;
    } #end buildPageListByParent() function
}

?>