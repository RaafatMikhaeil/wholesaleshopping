<?php

class Atlas_Model_PagesMapper
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
                    $this->setDbTable('Atlas_Model_DbTable_Pages');
            }
            return $this->_dbTable;
    } #end getDbTable() function

    // save the attributes of a given db object
    public function save(Atlas_Model_Pages $page)
    {
            // push the data into an array
            $data = $page->toArray();

            // if the row in the db doesnt exist create the row
            // otherwise update the existing row
            if( NULL === ($page_id = $page->getPage_id()) || (int)$page_id == 0 ){
                    unset($data['page_id']);
                    $page_id = $this->getDbTable()->insert($data);
                    return $page_id;
            }
            else {
                    $this->getDbTable()->update($data, array('page_id = ?' => $page_id));
                    return $page_id;
            }
    } #end save() function

    // remove a row from the database that matches the id given
    public function remove($page_id)
    {
            $this->getDbTable()->delete("page_id='$page_id'");

    } #end remove() function

    // find a row in the database based on the primary key and set the values
    // in the db object given by the user
    public function find($page_id)
    {
            $page = new Atlas_Model_Pages();

            // attempt to locate the row in the database
            // if it doesn't exist throw an exception
            $result = $this->getDbTable()->find($page_id);
            if( 0 == count($result) ){
                    throw new Exception("Given entry doesn't exist");
            }

            // get the data and push it to the object
            $row = $result->current();
            $page->setOptions($row->toArray());

            return $page;

    } #end find() function

    // find all entries from the database for the given table
    public function fetchAll()
    {
            // gather all of the entries in the database
            // and push their values into an array
            $resultSet = $this->selectAll()->query()->fetchAll();
            $entries   = array();
            foreach( $resultSet as $row ){
                    $entry = new Atlas_Model_Pages();
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
                       ->from(array("p"=>"pages"),
                                      array("p.page_id", "p.page_name", "p.page_group_id", "p.page_active", "p.host", "p.path", "p.show_in_dashboard"))
                       ->order("p.page_name ASC");

            return $select;
    } #end selectAll() function

    // return the page info with extra info about it's parent group
    public function getPages( $search="" )
    {
            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                       ->from(array("p"=>"pages"),
                          array("pg.page_group_id", "pg.page_group_name", "pg.image","p.page_parent_id", 
                                            "p.page_id", "p.page_name", "p.host", "p.path", "p.page_active", "p.show_in_dashboard"))
                   ->joinLeft(array("pg"=>"page_groups"), "pg.page_group_id=p.page_group_id", array());
            if( $search != "" ) {
                    $select->where("pg.page_group_name LIKE '%".$search."%' OR p.page_name LIKE '%".$search."%' OR p.path LIKE '%".$search."%' OR pg.page_group_name LIKE '%".$search."%'");
            }
            $select->order(array("pg.page_group_name ASC", "p.page_name ASC"));
            return $select;
    } #end getAllPages() function

    // return the page info with extra info about it's parent group
    public function buildPages()
    {
            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                       ->from(array("p"=>"pages"),
                          array("pg.page_group_id", "pg.page_group_name", "pg.image",
                                            "p.page_id", "p.page_name", "p.host", "p.path", "p.page_active", "p.show_in_dashboard"))
                   ->joinLeft(array("pg"=>"page_groups"), "pg.page_group_id=p.page_group_id", array())
                    ->order(array("p.page_id ASC"));
            return $select->query()->fetchAll();
    } #end buildPages() function

    // return a list of pages for a particular group
    public function getGroupPages($page_group_id)
    {
            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                       ->from(array("p"=>"pages"),
                          array("p.page_id", "p.page_name", "p.host", "p.path",
                                            "pg.page_group_name"))
                   ->join(array("pg"=>"page_groups"), "pg.page_group_id=p.page_group_id", array())
                       ->where("p.page_active = ?", 1)
                       ->where("p.show_in_dashboard = ?", 1)
                       ->where("pg.group_active = ?", 1)
                       ->where("pg.page_group_id = ?", $page_group_id)
                       ->order("p.page_name ASC");
            return $select;
    } #end getGroupPages() function

    // returns all the pages a user has access to
    public function getUserGroupPages($page_group_id, $permission_group_ids)
    {
            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                       ->from(array("p"=>"pages"),
                          array("p.page_id", "p.page_name", "p.host", "p.path",
                                            "pg.page_group_name"))
                   ->join(array("pg"=>"page_groups"), "pg.page_group_id=p.page_group_id", array())
                       ->join(array("pp"=>"page_privileges"), "pp.page_id=p.page_id", array())
                       ->where("pp.permission_group_id IN (".$permission_group_ids.")")
                       ->where("p.page_active = ?", 1)
                       ->where("p.show_in_dashboard = ?", 1)
                       ->where("pg.page_group_active = ?", 1)
                       ->where("pg.page_group_id = ?", $page_group_id)
                       ->order("p.page_name ASC");

            return $select;
    } #end getUserGroupPages() function

    public function updatePageHosts( $host, $path="%" )
    {
        $this->getDbTable()->update(
            array("host" => $host),
            array("path LIKE '".$path."'")
        );
    } #end updatePageHosts function
	
    // return a page list structure of all the pages in the system
    public function buildPageList()
    {
            $select  = $this->getPages();
            $results = $select->query()->fetchAll();

            $final_results = array();
            foreach( $results as $result ) {
                    if( trim($result['page_group_id']) == "" || !isset($result['page_group_id']) ) {
                            $result['page_group_id']   = 0;
                            $result['page_group_name'] = "Not Assigned";
                    }
                    if( !isset($final_results[$result['page_group_id']]) ) {
                            $final_results[$result['page_group_id']]['page_group_name'] = $result['page_group_name'];
                            $final_results[$result['page_group_id']]['page_group_id']   = $result['page_group_id'];
                    }

                    $final_results[$result['page_group_id']]['pages'][] = $result;
            }

            return $final_results;
    } #end buildPageList() function

    // check if a page is in the system
    public function doesPageExist($uri)
    {
            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                       ->from(array("p"=>"pages"),
                          array("COUNT(p.page_id) AS page_total"))
                   ->where("p.path LIKE ?", $uri);

            $result = $select->query()->fetchAll();

            if( $result[0]['page_total'] > 0 ) {
                    return true;
            } else {
                    return false;
            }
    } #end doesPageExist() function

    // check if the user has access to a particular page
    public function doesUserHaveAccess($uri, $permission_group_ids)
    {
            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                       ->from(array("p"=>"pages"),
                          array("COUNT(p.page_id) AS page_total"))
                       ->join(array("pp"=>"page_privileges"), "p.page_id=pp.page_id", array())
                       ->group("p.page_id")
                       ->where("pp.permission_group_id IN (".$permission_group_ids.")")
                   ->where("p.path LIKE ?", $uri);

            $result = $select->query()->fetch();

            if( isset($result['page_total']) && $result['page_total'] > 0 ) {
                    return true;
            } else {
                    return false;
            }
    } #end doesUserHaveAccess() function

    public function activatePage( $page_id,$activation )
    {
            try {
                    $user = $this->find($page_id);
                    $user->setPage_active($activation);
                    $this->save($user);
                    return true;
            } catch( Exception $e ) {
                    return false;
            }
    } #end activatePage() function

    // process the information submitted via form
    public function processForm( $form_data )
    {
            $page = new Atlas_Model_Pages();
            $page->setOptions($form_data);

            $pageid = $this->save($page);

            return $pageid;
    } #end processForm() function

    // filter a partial uri to remove trailing variables
    public function filterPartialUri($page)
    {
            $urlParts = explode("/",$page);

            if( count($urlParts) > 2 ) {
                    $page = "/".$urlParts[1]."/".$urlParts[2];
            }

            return $page;
    } #end filterPartialUri() function

    // filter a full uri to remove trailing variables
    public function filterFullUri($page)
    {
            $urlParts = explode("/",$page);

            if( count($urlParts) > 2 ) {
                    $page = "/".$urlParts[3]."/".$urlParts[4];
            }

            return $page;
    } #end filterFullUri() function

    // return a list of pages for a permission groups
    public function buildPagesbyPerm($permission_group_ids) {

        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("p"=>"pages"),
                       array("p.page_name", "p.host", "p.path" , "p.page_parent_id" ))
                    ->join(array("pp"=>"page_privileges"), "pp.page_id=p.page_id", array())
                    ->where("pp.permission_group_id IN (".$permission_group_ids.")")
                    ->where("p.page_active = ?", 1)
                    ->where("p.show_in_dashboard = ?", 1)
                    ->order(array( "p.page_name"));

        $results = $select->query()->fetchAll();
        return $results;
    } #end buildPagesbyPerm() function

    // return a page list structure of all the pages in the system
    public function createPageListTree($pages,$parents) {

        foreach($parents as $value) {
            if( $value['data']['parent_id']==0 ) {
                $final_results[0]['page_group_id']   = 0;
                $final_results[0]['page_group_name'] = "Not Assigned";
            }
            if( !isset($final_results[$value['data']['page_group_id']]) ) {
                $final_results[$value['data']['page_group_id']]['page_group_name'] = $value['data']['page_group_name'];
                $final_results[$value['data']['page_group_id']]['page_group_id']   = $value['data']['page_group_id'];
            }
            foreach($pages as $page){
                if( $page['page_parent_id'] ==  $value['data']['page_group_id']) {
                    $final_results[$value['data']['page_group_id']]['pages'][$page['page_id']] = $page;
                }
                if ($page['page_parent_id'] == 0 && $value['data']['parent_id']==0 ) {
                    $final_results[0]['pages'][$page['page_id']] = $page;
                }
            }
            if(isset($value['children']) && count($value['children'])>0){
                $final_results[$value['data']['page_group_id']]['children'] = $this->createPageListTree($pages,$value['children']);
            }
        }

        return $final_results;
    } #end buildPageList() function

    // return the page info with extra info about it's parent group
    public function getFavPages( $search="" )
    {
            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                       ->from(array("p"=>"pages"),
                          array("p.*"));
            if( $search != "" ) {
                    $select->where("p.path = ?",$search);
            }
            return $select;
    } #end getAllPages() function
}

?>