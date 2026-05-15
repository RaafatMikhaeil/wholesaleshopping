<?php

class Atlas_Model_InvPaymentsMapper
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
			$this->setDbTable("Atlas_Model_DbTable_InvPayments");
		}
		
		return $this->_dbTable;
	} #end getDbTable function
	
	
	public function save( Atlas_Model_InvPayments $entry )
	{
		// push the data into an array
		$data = $entry->toArray();
		
		// if the row in the db doesnt exist create the row
		// otherwise update the existing row
		if( NULL === ($payment_id = $entry->getPayment_id()) || $payment_id == 0 ) {
			unset($data["payment_id"]);
			$payment_id = $this->getDbTable()->insert($data);
			return $payment_id;
		} else {
			$this->getDbTable()->update($data, array("payment_id = ?" => $payment_id));
			return $payment_id;
		}
	} #end save function
	
	
	public function remove( $payment_id )
	{
		$this->getDbTable()->delete("payment_id='$payment_id'");
	} #end remove function
	
        public function removePayments( $inv_id )
	{
		$this->getDbTable()->delete("inv_id='$inv_id'");
	} #end remove function
        
	public function find( $payment_id )
	{
		$entry = new Atlas_Model_InvPayments();
		
		// attempt to locate the row in the database
		// if it doesn"t exist return NULL
		$result = $this->getDbTable()->find($payment_id);
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
			$entry = new Atlas_Model_InvPayments();
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
		$select->from(array("t"=>"inv_payments"), 
				array('t.*'));
		
		// return the select statement	
		return $select;
	} #end selectAll function
	
	public function buildPayments()
	{
		// create a select statement for gathering all of the entries
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
                        ->from(array("p"=>"inv_payments"), 
				array(  'p.*',
                                        'i.*',
                                        'c.*',
                                        't.*',
                                        'u.name'
                                        )
                                )
                        ->joinLeft(array("t"=>"inv_payment_types"), "t.payment_type_id = p.payment_type_id", array())
                        ->join(array("i"=>"inv_header"), "i.inv_id=p.inv_id", array())
                        ->join(array("c"=>"customers"), "c.cust_id=i.inv_cust_id", array())
                        ->join(array("u"=>"users"), "u.user_id=p.user_id", array())
                        //->where('inv_status = 1')
                        ->order(array('p.payment_id DESC'));
		
		// return the select statement	
		return $select->query()->fetchAll();
	} #end selectAll function
        
	public function buildPayment($payment_id)
	{
		// create a select statement for gathering all of the entries
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
                        ->from(array("p"=>"inv_payments"), 
				array(  'p.*' ) )
                        ->where("p.payment_id = $payment_id");
		
		// return the select statement	
		return $select->query()->fetch();
	} #end selectAll function
        
	public function buildInvPayment($inv_id)
	{
		// create a select statement for gathering all of the entries
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
                        ->from(array("p"=>"inv_payments"), 
				array(  'p.*',
                                        'i.*',
                                        'c.*',
                                        't.*',
                                        'u.name'
                                        )
                                )
                        ->join(array("t"=>"inv_payment_types"), "t.payment_type_id = p.payment_type_id", array())
                        ->join(array("i"=>"inv_header"), "i.inv_id=p.inv_id", array())
                        ->join(array("c"=>"customers"), "c.cust_id=i.inv_cust_id", array())
                        ->join(array("u"=>"users"), "u.user_id=p.user_id", array())
                        ->where("p.inv_id = $inv_id")
                        ->order(array('p.payment_id DESC'));
		
		// return the select statement	
		return $select->query()->fetchAll();
	} #end selectAll function
        
	public function buildCustPayment($cust_id)
	{
		// create a select statement for gathering all of the entries
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
                        ->from(array("p"=>"inv_payments"), 
				array(  'p.*',
                                        'i.*',
                                        'c.*',
                                        't.*',
                                        'u.name'
                                        )
                                )
                        ->join(array("t"=>"inv_payment_types"), "t.payment_type_id = p.payment_type_id", array())
                        ->join(array("i"=>"inv_header"), "i.inv_id=p.inv_id", array())
                        ->join(array("c"=>"customers"), "c.cust_id=i.inv_cust_id", array())
                        ->join(array("u"=>"users"), "u.user_id=p.user_id", array())
                        ->where("i.inv_cust_id = $cust_id")
                        ->order(array('p.payment_id DESC'));
		
		// return the select statement	
		return $select->query()->fetchAll();
	} #end selectAll function        
        
	public function buildPendingInvoices()
	{
		// create a select statement for gathering all of the entries
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
                        ->from(array("t"=>"inv_header"), 
				array('t.*','c.*','u.name'))
                        ->join(array("c"=>"customers"), "c.cust_id=t.inv_cust_id", array())
                        ->join(array("u"=>"users"), "u.user_id=t.inv_user_id", array())
                        ->where('inv_status = 0')
                        ->order(array('t.inv_id DESC'));
		
		// return the select statement	
		return $select->query()->fetchAll();
	} #end selectAll function
        
	public function buildInvReport($form_data)
	{
            $start_date = date('Y-m-d', strtotime($form_data['start_date']));
            $end_date = date('Y-m-d', strtotime($form_data['end_date']));
            $customer = $form_data['cust_id_inv'];
            $pay_type = $form_data['pay_type'];
            $cust_cond = ((int)$customer != 0)?" AND c.cust_id = $customer":"";
            $prod_cond = ((int)$pay_type != 0)?" AND t.payment_type_id = $pay_type":"";
            
            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                    ->from(array("p"=>"inv_payments"), 
                            array(  'p.payment_id AS PID',
                                    'i.inv_id as InvNo',
                                    'c.cust_name AS CustName',
                                    'UPPER(CONCAT(c.cust_address,",",c.cust_city,",",c.cust_state," ",c.cust_zip)) as CustAddress',
                                    'DATE_FORMAT(p.payment_datetime,"%m/%d/%Y") AS ReceivedDate',
                                    'p.payment_contact AS Reference',
                                    'u.name AS LoggedBy',
                                    'i.inv_total as InvTotal',
                                    't.payment_type as PayMethod',
                                    'p.payment_amount AS PaidAmount'
                                    )
                            )
                    ->join(array("t"=>"inv_payment_types"), "t.payment_type_id = p.payment_type_id", array())
                    ->join(array("i"=>"inv_header"), "i.inv_id=p.inv_id", array())
                    ->join(array("c"=>"customers"), "c.cust_id=i.inv_cust_id", array())
                    ->join(array("u"=>"users"), "u.user_id=p.user_id", array())
                    ->where("DATE(p.payment_datetime) BETWEEN '$start_date' AND '$end_date' $cust_cond $prod_cond")
                    ->order(array('p.payment_id DESC'));
            // return the select statement	
            return $select->query()->fetchAll();
	} #end selectAll function
        
	public function buildInvoice($id)
	{
		// create a select statement for gathering all of the entries
		$select = $this->getDbTable()->select();
		$select->setIntegrityCheck(false)
                        ->from(array("t"=>"inv_header"), 
				array('t.*','c.*','u.name'))
                        ->join(array("c"=>"customers"), "c.cust_id=t.inv_cust_id", array())
                        ->join(array("u"=>"users"), "u.user_id=t.inv_user_id", array())
                        ->where("t.inv_id = ?", $id)
                        ->order(array('t.inv_id DESC'));
                $header_info = $select->query()->fetch();
                
                $inv_lines = new Atlas_Model_InvLinesMapper();
                $lines_info = $inv_lines->buildLines($id);
                
		// return the select statement	
		return array('header'=>$header_info,'lines'=>$lines_info);
	} #end selectAll function
        
	public function processForm( $form_data = NULL )
	{
		if( $form_data == NULL ) {
			throw new Exception("No data given to the model for processing.");
		}
		
		if( (int)$form_data["payment_id"] > 0 ) {
			$entry = $this->find($form_data["payment_id"]);
			$entry->setOptions($form_data);
			$payment_id = $this->save($entry);
		} else {
			unset($form_data["payment_id"]);
			$entry = new Atlas_Model_InvPayments();
			$entry->setOptions($form_data);
			$payment_id = $this->save($entry);
		}
		
		return $payment_id;
	} #end processForm function
	
}

?>