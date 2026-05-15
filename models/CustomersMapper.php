<?php

class Atlas_Model_CustomersMapper
{
	protected $_dbTable;
	
	public function setDbTable( $dbTable )
	{
		// if a string was given return an object
		if( is_string($dbTable) ) {
			$dbTable = new $dbTable();
		}
		// ensure the dbTable is of the correct instance
		if( !$dbTable instanceof Zend_Db_Table_Abstract ) {
			throw new Exception("Invalid table data object provided");
		}
		
		// set the db table and return the handle
		$this->_dbTable = $dbTable;
		return $this;
	} #end setDbTable function
	
	
	public function getDbTable()
	{
		// if the object is not set, set it and return it
		if( NULL === $this->_dbTable ) {
			$this->setDbTable("Atlas_Model_DbTable_Customers");
		}
		
		return $this->_dbTable;
	} #end getDbTable function
	
	
	public function save( Atlas_Model_Customers $entry )
	{
		// push the data into an array
		$data = $entry->toArray();
		
		// if the row in the db doesnt exist create the row
		// otherwise update the existing row
		if( NULL === ($cust_id = $entry->getCust_id()) || $cust_id == 0 ) {
			unset($data["cust_id"]);
			$cust_id = $this->getDbTable()->insert($data);
			return $cust_id;
		} else {
			$this->getDbTable()->update($data, array("cust_id = ?" => $cust_id));
			return $cust_id;
		}
	} #end save function
	
	
	public function remove( $cust_id )
	{
		$this->getDbTable()->delete("cust_id='$cust_id'");
	} #end remove function
	
	
	public function find( $cust_id )
	{
		$entry = new Atlas_Model_Customers();
		
		// attempt to locate the row in the database
		// if it doesn"t exist return NULL
		$result = $this->getDbTable()->find($cust_id);
		if( 0 == count($result) ) {
    		throw new Exception("Given entry doesn't exist in the system.");
		}
		
		// get the data and push it to the object
		$row = $result->current();
		$entry->setOptions($row->toArray());
		
		return $entry;
	} #end find function
	
	
	public function fetchAll()
	{
		// gather all of the entries in the database
		// and push their values into an array
		$results = $this->selectAll()->query()->fetchAll();
		$entries = array();
		foreach( $results as $row ) {
			$entry = new Atlas_Model_Customers();
			$entry->setOptions($row);
			$entries[] = $entry;
		}
		
		// return the results
		return $entries;
	} #end fetchAll function
	
	
	public function selectAll()
	{
		// create a select statement for gathering all of the entries
		$select = $this->getDbTable()->select();
		$select->from(array("t"=>"customers"), 
				array('t.*'));
		
		// return the select statement	
		return $select;
	} #end selectAll function
	
	public function buildCustomers()
	{
		$select = $this->getDbTable()->select();
		$select->from(array("t"=>"customers"),
			array('*'))
                        ->order(array('cust_name asc'));

		$result = $select->query()->fetchAll();
                return $result;
	} #end checkLabel function
        
	public function buildActiveCustomers()
	{
		$select = $this->getDbTable()->select();
		$select->from(array("t"=>"customers"),
			array('*'))
                        ->where("t.cust_status = 1")
                        ->order(array('cust_name asc'));

		$result = $select->query()->fetchAll();
                return $result;
	} #end checkLabel function
        
	public function buildInactiveCustomers()
	{
		$select = $this->getDbTable()->select();
		$select->from(array("t"=>"customers"),
			array('*'))
                        ->where("t.cust_status = 0")
                        ->order(array('cust_name asc'));

		$result = $select->query()->fetchAll();
                return $result;
	} #end checkLabel function
        
	public function buildCustomersList()
	{
		$select = $this->getDbTable()->select();
		$select->from(array("t"=>"customers"),
			array('t.cust_name','t.cust_id','t.cust_lic_no','t.cust_lic_exp'))
                        ->order(array('cust_name asc'));

		$results = $select->query()->fetchAll();
                $final_results = [];
                foreach($results as $result){
                    $final_results[$result['cust_id']] = $result;
                }
                return $final_results;
	} #end checkLabel function
        
	public function buildCustInfo( $id )
	{
		$select = $this->getDbTable()->select();
		$select->from(array("t"=>"customers"),
			array('*'))
			->where('t.cust_id = ?', $id);

		$result = $select->query()->fetch();
                return $result;
	} #end checkLabel function
        
        public function buildCustomerEmail($id) {
            $select = $this->getDbTable()->select();
            $select->from(array("u" => "customers"), array("u.cust_name as name","u.cust_email as email"))
                    ->where("u.cust_id = $id");
            $result = $select->query()->fetch();
            return ['email'=> $result['email'],'name'=>$result['email']];
        }
        
        
    public function buildCustLiveSearch($search = '', $search_by = '') {
        $cust_list = '';
        if (!empty(trim($search))) {
            $search = strtolower(str_replace('®', '&reg;', strip_tags(str_replace("'","\\'",$search))));
            $select = $this->getDbTable()->select();
            $select->from(array("t" => "customers"),
                            array(
                                    "CONCAT(t.cust_name,' - ',t.cust_address,' - ',t.cust_contact) as cust_name",
                                    "t.cust_id"
                                )
                        );
                    if(empty(trim($search_by))){
                        $select->where("LOWER(t.cust_name) LIKE '%$search%' OR LOWER(t.cust_address) LIKE '%$search%' OR LOWER(t.cust_contact) LIKE '%$search%'");
                    }else if($search_by == 'store_name'){
                        $select->where("LOWER(t.cust_name) LIKE '%$search%' ");
                    }else if($search_by == 'address'){
                        $select->where("LOWER(t.cust_address) LIKE '%$search%'");
                    }else if($search_by == 'contact'){
                        $select->where("LOWER(t.cust_contact) LIKE '%$search%'");
                    }
                    $select->where("t.cust_status = ?",1)
                    ->order(array('t.cust_name asc'));
            // return the resulting item
            $results = $select->query()->fetchAll();
            if (count($results) > 0 && !empty(trim($search))) {
                $cust_list .= "<ul class='country-list' id='country-list_customer'>";
                foreach ($results as $result) {
                    $cust_list .=    '<li class="select_customer_suggest" '
                            . ' text="'. $result["cust_name"] .'"'
                            . ' rel="'.$result["cust_id"].'">' 
                            .$result["cust_name"] 
                            . '</li>';
                }
                $cust_list .= '</ul>';
            }
        }
        return $cust_list;
    }
    
    public function buildCustLiveSearchData($cust_id) {
            $search = strtolower(str_replace('®', '&reg;', strip_tags($search)));
            $select = $this->getDbTable()->select();
            $select->from(array("t" => "customers"),
                            array(
                                    "CONCAT(t.cust_name,' - ',t.cust_address,' - ',t.cust_contact) as cust_name",
                                    "t.cust_id"
                                )
                        )
                    ->where("t.cust_status = ?",1)
                    ->where("t.cust_id = ?",$cust_id)
                    ->order(array('t.cust_name asc'));
            // return the resulting item
            $result = $select->query()->fetch();
            return $result;
    }
	public function processForm( $form_data = NULL )
	{
		if( $form_data == NULL ) {
			throw new Exception("No data given to the model for processing.");
		}
		
		if( (int)$form_data["cust_id"] > 0 ) {
			$entry = $this->find($form_data["cust_id"]);
			$entry->setOptions($form_data);
                        $entry->setCust_lic_exp(date('Y-m-d', strtotime($form_data['cust_lic_exp'])));
			$cust_id = $this->save($entry);
		} else {
			unset($form_data["cust_id"]);
			$entry = new Atlas_Model_Customers();
			$entry->setOptions($form_data);
                        $entry->setCust_lic_exp(date('Y-m-d', strtotime($form_data['cust_lic_exp'])));
			$cust_id = $this->save($entry);
		}
		
		return $cust_id;
	} #end processForm function

    public function changeCustomerStatus($cust_id, $status) {
        try {
            $product = $this->find($cust_id);
            $product->setCust_status($status);
            $this->save($product);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

?>