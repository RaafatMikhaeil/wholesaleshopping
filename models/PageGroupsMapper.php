<?php

class Atlas_Model_PageGroupsMapper
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
			$this->setDbTable('Atlas_Model_DbTable_PageGroups');
		}
		return $this->_dbTable;
	} #end getDbTable() function
	
	// save the attributes of a given db object
	public function save(Atlas_Model_PageGroups $page_group)
	{
		// push the data into an array
		$data = $page_group->toArray();
		
		// if the row in the db doesnt exist create the row
		// otherwise update the existing row
		if( NULL === ($page_group_id = $page_group->getPage_group_id()) || $page_group_id == 0 ){
			unset($data['page_group_id']);
			$page_group_id = $this->getDbTable()->insert($data);
			return $page_group_id;
		}
		else {
			$this->getDbTable()->update($data, array('page_group_id = ?' => $page_group_id));
			return $page_group_id;
		}
	} #end save() function
	
	// remove a row from the database that matches the id given
	public function remove($page_group_id)
	{
		$this->getDbTable()->delete("page_group_id='$page_group_id'");
		
	} #end remove() function
	
	// find a row in the database based on the primary key and set the values
	// in the db object given by the user
	public function find($page_group_id)
	{
		$page_group = new Atlas_Model_PageGroups();
		
		// attempt to locate the row in the database
		// if it doesn't exist throw an exception
		$result = $this->getDbTable()->find($page_group_id);
		if( 0 == count($result) ){
			throw new Exception("Given entry doesn't exist");
		}
		
		// get the data and push it to the object
		$row = $result->current();
		$page_group->setOptions($row->toArray());
		
		return $page_group;
		
	} #end find() function
	
	// find all entries from the database for the given table
	public function fetchAll()
	{
		// gather all of the entries in the database
		// and push their values into an array
		$resultSet = $this->selectAll()->query()->fetchAll();
		$entries   = array();
		foreach( $resultSet as $row ){
			$entry = new Atlas_Model_PageGroups();
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
			   ->from(array("pg"=>"page_groups"),
					  array("pg.page_group_id", "pg.page_group_name", "pg.page_id", "pg.group_active", "pg.image"))
			   ->order("pg.page_group_name ASC");
			   
		return $select;
	} #end selectAll() function
	
	// return a select statement with all of the group pages
	public function getPageGroups( $search = "" )
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			   ->from(array("pg"=>"page_groups"),
		              array("pg.page_group_id", "pg.page_group_name", "pg.page_id AS default_page_id", "pg.group_active", "pg.image",
					        "p.page_name", "p.host", "p.path", "p.page_active"))
		       ->joinLeft(array("p"=>"pages"), "p.page_id=pg.page_id", array());
		if( $search != "" ) {
			$select->where("pg.page_group_name LIKE '%".$search."%' OR p.page_name LIKE '%".$search."%'");
		}
		$select->order(array("pg.page_group_name ASC", "p.page_name ASC"));
		
		return $select;
	} #end getPageGroups() function
	
	// return a select statement with all of the group pages
	public function buildPageGroups()
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			   ->from(array("pg"=>"page_groups"),
		              array("pg.page_group_id", "pg.page_group_name", "pg.page_id AS default_page_id", 
                                    "pg.group_active", "pg.image","p.page_name", "p.host", "p.path", "p.page_active"))
		       ->joinLeft(array("p"=>"pages"), "p.page_id=pg.page_id", array());
		$select->order(array("pg.page_group_name ASC", "p.page_name ASC"));
		
		return $select->query()->fetchAll();
	} #end getPageGroups() function
        
        
	// return the image for the given page group
	public function getGroupImage($page_group_id)
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			   ->from(array("pg"=>"page_groups"),
		              array("pg.page_group_id", "pg.page_group_name", "pg.group_active", "pg.image"))
		       ->where("pg.page_group_id = ?", $page_group_id);
			   
		return $select;
	} #end getGroupImages() function
	
	// return a data structure housing the page groups with related pages a user can view
	public function buildUserPageGroups($permission_group_ids)
	{
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
			   ->distinct()
			   ->from(array("p"=>"pages"),
		              array("pg.page_group_id", "pg.page_group_name", "pg.image",
					        "p.page_name", "p.host", "p.path", "p.page_parent_id"))
                           ->join(array("pg"=>"page_groups"), "pg.page_group_id=p.page_group_id", array())
			   ->join(array("pp"=>"page_privileges"), "pp.page_id=p.page_id", array())
			   ->where("pp.permission_group_id IN (".$permission_group_ids.")")
			   ->where("p.page_active = ?", 1)
			   ->where("p.show_in_dashboard = ?", 1)
			   ->order(array("pg.page_group_name", "p.page_name"));
			   
		$results = $select->query()->fetchAll();
		$page_groups = array();
                $parents = $this->getParents();

		foreach( $results as $result ) {
                    if( !isset($page_groups[$result['page_group_name']]) ) {
                        $page_groups[$result['page_group_name']]['page_group_name'] = $result['page_group_name'];
                        $page_groups[$result['page_group_name']]['page_group_id']   = $result['page_group_id'];
                        $page_groups[$result['page_group_name']]['image']           = $result['image'];
                    }
                    if( isset($page_groups[$result['page_group_name']]['pages']) && is_array($page_groups[$result['page_group_name']]['pages']) ) {
                        $pos = count($page_groups[$result['page_group_name']]['pages']);
                    } else {
                        $pos = 0;
                    }
                    $page_groups[$result['page_group_name']]['pages'][$pos]['page_name'] = $result['page_name'];
                    $page_groups[$result['page_group_name']]['pages'][$pos]['host']      = $result['host'];
                    $page_groups[$result['page_group_name']]['pages'][$pos]['path']      = $result['path'];
                    $page_groups[$result['page_group_name']]['pages'][$pos]['sitemap']   = $this->getPagePath($parents,$result['page_parent_id'],$result['page_name']);
		}
		
		return $page_groups;
	} #end buildUserPageGroups() function
	
	// process the information submitted via form
	public function processForm( $form_data )
	{
        $target_path = Zend_Registry::get("target_path")."/uploads/orders/icons/";
		$page_group  = new Atlas_Model_PageGroups();
		if( (int)$form_data['page_group_id'] > 0 ) {
			$page_group = $this->find($form_data['page_group_id']);
			if( is_uploaded_file($_FILES['image']['tmp_name']) && $_FILES['image']['size'] > 0 ) {
				// attempt to save new image
				$file_name = $this->saveGroupImage($_FILES['image']);
				$form_data['image'] = $file_name;
				
				// delete previous image
				if( file_exists($target_path.$page_group->getImage()) ) {
					unlink($target_path.$page_group->getImage());
				}
			} else {
				unset($form_data['image']);
			}
			
			$page_group->setOptions($form_data);
			$this->save($page_group);
		} else {
			unset($form_data['page_group_id']);
			
			if( is_uploaded_file($_FILES['image']['tmp_name']) && $_FILES['image']['size'] > 0 ) {
				$file_name = $this->saveGroupImage($_FILES['image']);
				$form_data['image'] = $file_name;
			} else {
				unset($form_data['image']);
			}
			
			$page_group->setOptions($form_data);
			$this->save($page_group);	
		}
		
	} #end processForm() function
	
	
	// handle the saving of an uploaded image
	public function saveGroupImage( $image_file )
	{
		// process image upload
		if( is_uploaded_file($image_file['tmp_name']) ) {
            $target_path = Zend_Registry::get("target_path")."/uploads/orders/icons/";

			// Parse file attributes					
			$file_type     = $image_file['type'];
			$file_ext      = substr($image_file['name'],-3,3);
			$file_tmp_name = $image_file['tmp_name'];
			$file_error    = $image_file['error'];
			$file_size     = $image_file['size'];
			
			// create a unique file name
			$iter      = 0;
			$file_name = md5($image_file['name']."_".time()."_".$iter).".".$file_ext;
			while( file_exists($target_path.$file_name) ) {
				++$iter;
				$file_name = md5($image_file['name']."_".time()."_".$iter).".".$file_ext;
			}
			
			if( move_uploaded_file($file_tmp_name, $target_path.$file_name) ) {																				
				return $file_name;
			} else {
				throw new Exception("The file could not be saved.");
			}
		} else {
			throw new Exception("The file was not sent to the server.");
		}
	} #end saveGroupImage() function

        public function getParents() {

            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                        ->distinct()
                        ->from(array("pg"=>"page_groups"),
                           array("pg.page_group_id", "pg.page_group_name", "pg.image", "pg.parent_id"))
                        ->where("pg.group_active = ?" , 1)
                        ->order(array("pg.parent_id", "pg.page_group_name"));

            $results = $select->query()->fetchAll();
            return $results;
        }

        public function createMenuTree($data, $parentId = 0, $all_pages) {

            foreach($data as $menuItem) {
                if ($menuItem['parent_id'] == $parentId) {
                    $results[ $menuItem['page_group_id']]['data'] = $menuItem;
                    $value = $menuItem['page_group_id'];
                    $results[ $menuItem['page_group_id']]['pages'] = array_filter($all_pages, function ($var) use ($value) {
                                                                    return ($var['page_parent_id'] == $value);
                                                                });

                    $children = $this->createMenuTree($data, $menuItem['page_group_id'],$all_pages);
                    if ($children) {
                        $results[ $menuItem['page_group_id']]['children'] = $children;
                        $results[ $menuItem['page_group_id']]['pages'] = array_filter($all_pages, function ($var) use ($value) {
                                                                    return ($var['page_parent_id'] == $value);
                                                                });
                    }
                }

                if ( count($results[ $menuItem['page_group_id']]['pages'])== 0 && count($results[ $menuItem['page_group_id']]['children'])== 0 ) {
                    unset($results[ $menuItem['page_group_id']]);
                }
            }

            return $results;
        }

        public function buildMenu($permission_groups) {

            $parents = $this->getParents();
            $page_mapper = new Atlas_Model_PagesMapper();
            $all_pages = $page_mapper->buildPagesbyPerm($permission_groups);

            $final_results = $this->createMenuTree($parents,0,$all_pages);

            return $final_results ;
        }

        public function createParentsTree($data, $parentId = 0) {

            foreach($data as $menuItem) {
                if ($menuItem['parent_id'] == $parentId) {
                    $results[ $menuItem['page_group_id']]['data'] = $menuItem;
                    $children = $this->createParentsTree($data, $menuItem['page_group_id']);
                    if ($children) {
                        $results[ $menuItem['page_group_id']]['children'] = $children;
                    }
                }
            }

            return $results;
        }

        public function buildParents() {

            $parents = $this->getParents();
            $final_results = $this->createParentsTree($parents,0);

            return $final_results;
        }

        public function getPageNewPaths() {

            $permission_groups = Zend_Registry::get("permission_group_ids");
            $page_groups = $this->buildUserPageGroups($permission_groups);
            return $page_groups;
        }

        public function getPagePath($parents, $parent_id = 0, $page_name = '') {

            $p = array_search($parent_id,array_column($parents,'page_group_id'));
            if ( $parents[$p]['parent_id'] != 0) {
                $path .= $this->getPagePath($parents, $parents[$p]['parent_id'],'');
            }
            $path .= $parents[$p]['page_group_name'].' / '.$page_name;

            return $path;
        }

}

?>