<?php

class Atlas_Model_InvHeaderMapper {

    protected $_dbTable;

    public function setDbTable($dbTable) {
        // if a string was given return an object
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        // ensure the dbTable is of the correct instance
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception("Invalid table data object provided");
        }

        // set the db table and return the handle
        $this->_dbTable = $dbTable;
        return $this;
    }

#end setDbTable function

    public function getDbTable() {
        // if the object is not set, set it and return it
        if (NULL === $this->_dbTable) {
            $this->setDbTable("Atlas_Model_DbTable_InvHeader");
        }

        return $this->_dbTable;
    }

#end getDbTable function

    public function save(Atlas_Model_InvHeader $entry) {
        // push the data into an array
        $data = $entry->toArray();

        // if the row in the db doesnt exist create the row
        // otherwise update the existing row
        if (NULL === ($inv_id = $entry->getInv_id()) || $inv_id == 0) {
            unset($data["inv_id"]);
            $inv_id = $this->getDbTable()->insert($data);
            return $inv_id;
        } else {
            $this->getDbTable()->update($data, array("inv_id = ?" => $inv_id));
            return $inv_id;
        }
    }

#end save function

    public function insert(Atlas_Model_InvHeader $entry) {
        $data = $entry->toArray();
        $inv_id = $this->getDbTable()->insert($data);
        return $inv_id;
    }
    
    public function remove($inv_id) {
        $this->getDbTable()->delete("inv_id='$inv_id'");
    }

#end remove function

    public function find($inv_id) {
        $entry = new Atlas_Model_InvHeader();

        // attempt to locate the row in the database
        // if it doesn"t exist return NULL
        $result = $this->getDbTable()->find($inv_id);
        if (0 == count($result)) {
            throw new Exception("Given entry doesn't exist in the system.");
        }

        // get the data and push it to the object
        $row = $result->current();
        $entry->setOptions($row->toArray());

        return $entry;
    }

#end find function

    public function fetchAll() {
        // gather all of the entries in the database
        // and push their values into an array
        $results = $this->selectAll()->query()->fetchAll();
        $entries = array();
        foreach ($results as $row) {
            $entry = new Atlas_Model_InvHeader();
            $entry->setOptions($row);
            $entries[] = $entry;
        }

        // return the results
        return $entries;
    }

#end fetchAll function

    public function selectAll() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->from(array("t" => "inv_header"),
                array('t.*'));

        // return the select statement	
        return $select;
    }

#end selectAll function

    public function buildSalesOrdersList() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "inv_header"),
                        array(
                            't.*',
                            'u.name'
                        )
                )
                ->join(array("u" => "users"), "u.user_id=t.inv_user_id", array())
                ->where('inv_cust_id = 0')
                ->order(array('t.inv_id DESC'));

        // return the select statement	
        return $select->query()->fetchAll();
    }

#end selectAll function

    public function buildDraftInvoices() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "inv_header"),
                        array('t.*', 'c.*', 'u.name'))
                ->join(array("c" => "customers"), "c.cust_id=t.inv_cust_id", array())
                ->join(array("u" => "users"), "u.user_id=t.inv_user_id", array())
                ->where('inv_status = 3')
                ->where("t.inv_cust_id != ?", 0)
                ->order(array('t.inv_id DESC'));

        // return the select statement	
        return $select->query()->fetchAll();
    }

#end selectAll function

    public function buildInvoices() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "inv_header"),
                        array('t.*', 'c.*', 'u.name'))
                ->join(array("c" => "customers"), "c.cust_id=t.inv_cust_id", array())
                ->join(array("u" => "users"), "u.user_id=t.inv_user_id", array())
                ->where('inv_paid = 1')
                ->where("CAST(t.inv_status AS INT) != 3 OR t.inv_status IS NULL")  //Avoid Drafted Invoiced
                ->where("t.inv_cust_id != ?", 0)
                ->order(array('t.inv_id DESC'));

        // return the select statement	
        return $select->query()->fetchAll();
    }

#end selectAll function

    public function buildPendingInvoices() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "inv_header"),
                        array(
                            't.*',
                            'c.*',
                            'u.name',
                            'IFNULL((SELECT SUM(payment_amount) from inv_payments ipp JOIN inv_header ihh ON ipp.inv_id = ihh.inv_id where ihh.inv_id = t.inv_id and ihh.inv_paid =0 and ihh.inv_cust_id = c.cust_id), 0) as InvPayments',
                            new Zend_Db_Expr('t.inv_total - IFNULL((SELECT SUM(payment_amount) from inv_payments ipp JOIN inv_header ihh ON ipp.inv_id = ihh.inv_id where ihh.inv_id = t.inv_id and ihh.inv_paid =0 and ihh.inv_cust_id = c.cust_id),0) as PendingAmount')
                        )
                )
                ->join(array("c" => "customers"), "c.cust_id=t.inv_cust_id", array())
                ->join(array("u" => "users"), "u.user_id=t.inv_user_id", array())
                ->where('t.inv_paid = 0')
                ->where("CAST(t.inv_status AS INT) != 3 OR t.inv_status IS NULL")  //Avoid Drafted Invoiced
                ->where("t.inv_cust_id != ?", 0)
                ->order(array('t.inv_id DESC'));

        // return the select statement	
        return $select->query()->fetchAll();
    }

#end selectAll function

    public function buildPendingInvoices2($inv_id = 0) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "inv_header"),
                        array(
                            't.inv_id',
                            'c.cust_name'
                        )
                )
                ->join(array("c" => "customers"), "c.cust_id=t.inv_cust_id", array())
                ->where('t.inv_status != 3  OR t.inv_status IS NULL') //Avoid Drafted Invoiced
                ->where("t.inv_cust_id != ?", 0);
        if ($inv_id) {
            $select->where("t.inv_id = $inv_id");
        } else {
            $select->where("t.inv_paid = 0");
        }
        $select->order(array('t.inv_id DESC'));

        // return the select statement	
        return $select->query()->fetchAll();
    }

#end selectAll function

    public function buildLastPurchasePrice($cust_id, $prod_id, $inv_id) {
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "inv_header"),
                        array("t.inv_id", "l.product_price"))
                ->joinLeft(array("l" => "inv_lines"), "l.inv_id=t.inv_id", array())
                ->where("t.inv_cust_id = $cust_id")
                ->where("l.product_id = $prod_id")
                ->where("l.inv_line_type = 1");
        if ($inv_id != 0) {
            $select->where("t.inv_id != $inv_id");
        }
        $select->order(array('t.inv_id DESC'))
                ->limit(1);
        // return the select statement	
        return $select->query()->fetch();
    }

#end selectAll function

    public function buildInvReport($form_data) {
        $start_date = date('Y-m-d', strtotime($form_data['start_date']));
        $end_date = date('Y-m-d', strtotime($form_data['end_date']));
        $customer = $form_data['cust_id_inv'];
        $report_type = $form_data['report_type'];
        $paid = (int) $form_data['paid'];
        $product = $form_data['product_1'];
        $cust_cond = ((int) $customer != 0) ? " AND c.cust_id = $customer" : "";
        $prod_cond = ((int) $product != 0) ? " AND l.product_id = $product" : "";
        $paid_cond = ($paid == 0 || $paid == 1) ? " AND t.inv_paid = $paid" : "";

        if ((int) $product == 0 && $report_type == 'summary') {
            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                    ->from(array("t" => "inv_header"),
                            array(      't.inv_id as InvID',
                                        'DATE_FORMAT(t.inv_date, "%m/%d/%Y") as InvDate',
                                        'c.cust_name as CustName',
                                        'CONCAT(c.cust_address,",",c.cust_city,",",c.cust_state," ",c.cust_zip) as CustAddress',
                                        new Zend_Db_Expr('(SELECT SUM(product_price*product_qty) from inv_lines where inv_id = t.inv_id) as LineTotal'),
                                        't.inv_total as InvTotal',
                                        't.inv_discount as Discount',
                                        't.inv_credits as Credits',
                                        't.inv_shipping as Shipping',
                                        't.inv_cca as ccCharges',
                                        new Zend_Db_Expr('(
                                            SELECT  SUM((i.product_price-p.prod_cost)*i.product_qty)
                                            from inv_lines i
                                            JOIN prods p ON (p.prod_id = i.product_id)
                                            where i.inv_id = t.inv_id
                                        ) as Profit'),
                                        'REPLACE(REPLACE(t.inv_paid,0,"No"),1,"Yes") as Paid',
                                        'CONCAT("<a target=_blank href=/wholesale/invoice/id/",t.inv_id,">View</a>") as Invoice'
                                       ))
                    //new Zend_Db_Expr(
                    ->join(array("c" => "customers"), "c.cust_id=t.inv_cust_id", array())
                    ->join(array("u" => "users"), "u.user_id=t.inv_user_id", array())
                    ->where("DATE(t.inv_date) BETWEEN '$start_date' AND '$end_date' $cust_cond $paid_cond")
                    ->where("t.inv_cust_id != ?", 0)
                    ->order(array('t.inv_id DESC'));
        } else {
            $select = $this->getDbTable()->select();
            $select->setIntegrityCheck(false)
                    ->from(array("t" => "inv_header"),
                            array(      't.inv_id as InvID',
                                        'DATE_FORMAT(t.inv_date, "%m/%d/%Y") as InvDate',
                                        'c.cust_name as CustName',
                                        'CONCAT(c.cust_address,",",c.cust_city,",",c.cust_state," ",c.cust_zip) as CustAddress',
                                        'p.prod_desc as Product',
                                        'l.product_qty as Qty',
                                        'l.product_price as Price',
                                        new Zend_Db_Expr('l.product_price*l.product_qty as LineTotal'),
                                        't.inv_total as InvTotal',
                                        't.inv_discount as Discount',
                                        new Zend_Db_Expr('(l.product_price - p.prod_cost )* l.product_qty AS Profit'),
                                        'REPLACE(REPLACE(t.inv_paid,0,"No"),1,"Yes") as Paid',
                                        new Zend_Db_Expr('CONCAT("<a target=_blank href=/wholesale/invoice/id/",t.inv_id,">View</a>") as Invoice')
                                       ))
                    ->join(array("c" => "customers"), "c.cust_id=t.inv_cust_id", array())
                    ->join(array("u" => "users"), "u.user_id=t.inv_user_id", array())
                    ->joinLeft(array("l" => "inv_lines"), "l.inv_id=t.inv_id", array())
                    ->joinLeft(array("p" => "prods"), "l.product_id=p.prod_id", array())
                    ->where("DATE(t.inv_date) BETWEEN '$start_date' AND '$end_date' $cust_cond $prod_cond $paid_cond")
                    ->where("t.inv_cust_id != ?", 0)
                    ->order(array('t.inv_id DESC'));
           // echo $select; die();
        }
        // return the select statement	
        return $select->query()->fetchAll();
    }

#end selectAll function

    public function buildCustomerBalanceReport($form_data) {
        $start_date = date('Y-m-d', strtotime($form_data['start_date']));
        $end_date = date('Y-m-d', strtotime($form_data['end_date']));
        $customer = $form_data['cust_id_inv'];
        $report_type = $form_data['report_type'];
        if ((int) $customer != 0)
            $cust_cond = " AND c.cust_id = $customer";
        else
            $cust_cond = '';

        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "inv_header"),
                        array('c.cust_id,
                                    c.cust_name as CustName,
                                    CONCAT(c.cust_address,",",c.cust_city,",",c.cust_state," ",c.cust_zip) as CustAddress,
                                    count(distinct t.inv_id) as InvCount,
                                    SUM(inv_total) as InvTotals,
                                    IFNULL((SELECT SUM(payment_amount) from inv_payments ipp JOIN inv_header ihh ON ipp.inv_id = ihh.inv_id where ihh.inv_paid =0 and ihh.inv_cust_id = c.cust_id), 0) as InvPayments,
                                    SUM(inv_total) - IFNULL((SELECT SUM(payment_amount) from inv_payments ipp JOIN inv_header ihh ON ipp.inv_id = ihh.inv_id where ihh.inv_paid =0 and ihh.inv_cust_id = c.cust_id),0) as PendingAmount'
                        ))
                ->join(array("c" => "customers"), "c.cust_id=t.inv_cust_id", array())
                ->where("t.inv_paid = 0 $cust_cond")
                ->where("t.inv_cust_id != ?", 0)
                ->group(array("c.cust_id"))
                ->order(array('t.inv_id DESC'));
        $results = $select->query()->fetchAll();

        //GET INVOICES
        $select2 = $this->getDbTable()->select();
        $select2->setIntegrityCheck(false)
                ->from(array("t" => "inv_header"),
                        array("
                                    t.inv_cust_id,
                                    'INVOICE' as type,
                                    t.inv_id as No,
                                    DATE_FORMAT(t.inv_date, '%m/%d/%Y') as Date,
                                    DATEDIFF(CURDATE(),DATE(t.inv_date)) as Days,
                                    t.inv_total as Amount,
                                    CONCAT('<a target=_blank href=/wholesale/invoice/id/',t.inv_id,'>View</a>') as View
                                    "
                        ))
                ->where("t.inv_paid = 0")
                ->where("t.inv_cust_id != ?", 0)
                ->order(array('t.inv_id DESC'));
        $results2 = $select2->query()->fetchAll();

        //GET Payments
        $select3 = $this->getDbTable()->select();
        $select3->setIntegrityCheck(false)
                ->from(array("t" => "inv_header"),
                        array("
                                    t.inv_cust_id,
                                    'PAYMENT' as type,
                                    p.payment_id as No,
                                    DATE_FORMAT(p.payment_datetime, '%m/%d/%Y') as Date,
                                    DATEDIFF(CURDATE(),DATE(p.payment_datetime)) as Days,
                                    p.payment_amount as Amount,
                                    CONCAT('<a target=_blank href=/wholesale/payment/id/',p.payment_id,'>View</a>') as View
                                    "
                        ))
                ->join(array("p" => "inv_payments"), "p.inv_id=t.inv_id", array())
                ->where("t.inv_paid = 0")
                ->where("t.inv_cust_id != ?", 0)
                ->order(array('p.payment_id DESC'));
        $results3 = $select3->query()->fetchAll();

        $trasactions = array_merge(array_values($results2), array_values($results3));

        // return the select statement	
        return ['summary' => $results, 'details' => $trasactions];
    }

    public function buildInvoice($id) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "inv_header"),
                        array('t.*', 'c.*', 'u.name'))
                ->joinLeft(array("c" => "customers"), "c.cust_id=t.inv_cust_id", array())
                ->join(array("u" => "users"), "u.user_id=t.inv_user_id", array())
                ->where("t.inv_id = ?", $id)
                ->order(array('t.inv_id DESC'));
        $header_info = $select->query()->fetch();

        $inv_lines = new Atlas_Model_InvLinesMapper();
        $lines_info = $inv_lines->buildLines($id);

        // return the select statement	
        return array('header' => $header_info, 'lines' => $lines_info);
    }
    
    public function buildInvoiceIDs() {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "inv_header"),
                        array('t.inv_id'))
                ->order(array('t.inv_id ASC'));
        $results = $select->query()->fetchAll();
        
        
        $array =  array_values(array_column($results,'inv_id'));
        $empty = [];
        if($array[0] != 1){
            for( $y=1 ; $y < $array[0]; $y++){
                $empty[] = $y;
            }
        }
        
        foreach($array as $key => $value){
            $current = $value;
            if(array_key_exists($key+1, $array)){
                $next = $array[$key+1];
                $diff = $next-$current;
                if($diff > 1){
                    for($i=1;$i<$diff;$i++){
                        $empty[] = $current+$i;
                    }
                }
            }
        }
        
        if(count($empty) > 0){
            $inv_seq_mapper = new Atlas_Model_InvSeqMapper();
            foreach($empty as $InvId){
                try{
                    $id = $inv_seq_mapper->insert(new Atlas_Model_InvSeq(['inv_id' => $InvId]));
                } catch (Exception $e){
                    //DO NOTHING
                }
            }
        }
        
        return $empty;
    }

    public function buildInvoiceTotal($id) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "inv_header"),
                        array('t.inv_total'))
                ->where("t.inv_id = ?", $id)
                ->order(array('t.inv_id DESC'));
        $header_info = $select->query()->fetch();
        // return the select statement	
        return $header_info;
    }

#end selectAll function

    public function buildSalesOrder($id) {
        // create a select statement for gathering all of the entries
        $select = $this->getDbTable()->select();
        $select->setIntegrityCheck(false)
                ->from(array("t" => "inv_header"),
                        array('t.*', 'u.name'))
                ->join(array("u" => "users"), "u.user_id=t.inv_user_id", array())
                ->where("t.inv_id = ?", $id)
                ->where("t.inv_cust_id = ?", 0)
                ->order(array('t.inv_id DESC'));
        $header_info = $select->query()->fetch();

        $inv_lines = new Atlas_Model_InvLinesMapper();
        $lines_info = $inv_lines->buildLines($id);

        // return the select statement	
        return array('header' => $header_info, 'lines' => $lines_info);
    }

#end selectAll function

    public function processForm($form_data = NULL) {
        if ($form_data == NULL) {
            throw new Exception("No data given to the model for processing.");
        }

        if ((int) $form_data["inv_id"] > 0) {
            $entry = $this->find($form_data["inv_id"]);
            $entry->setOptions($form_data);
            $inv_id = $this->save($entry);
        } else {
            unset($form_data["inv_id"]);
            $entry = new Atlas_Model_InvHeader();
            $entry->setOptions($form_data);
            $inv_id = $this->save($entry);
        }

        return $inv_id;
    }

#end processForm function
}

?>