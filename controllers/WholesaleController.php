<?php

class WholesaleController extends Zend_Controller_Action {

    protected $_categories_list;
    public function init() {
        // set the CSS documents for the website
        $this->view->css_docs = array(
            Zend_Registry::get("global_css"),
            "/css/formula.css",
            "/css/page.css",
            "/css/datepicker.css",
            "/css/smoothness/jquery-ui-1.8.17.custom.css",
            "/css/smoothness/jalerts.css",
            "/css/jquery.toastmessage.css",
            "/css/jquery-ui.css");
        // set the JS documents for the website
        $this->view->js_docs = array(
            "/js/jquery.1.6.2.js",
            "/js/jquery-ui-1.8.17.custom.min.js",
            "/js/jquery.jBreadCrumb.1.1.js",
            "/js/jquery.easing.1.3.js",
            "/js/jquery.dataTables.min.js",
            "/js/jquery.toastmessage.js",
            "/js/jalerts.js",
            "/js/jquery.scannerdetection.compatibility.js",
            "/js/jquery.scannerdetection.js",
            "/js/global.js",
            "/js/wholesale.js"
        );

        // set the default layout
        $this->_helper->layout->setLayout('tinymce_layouta');

        // check if user is logged in and if they can access the current page
        $uri = $this->getRequest()->getRequestUri();
        $session = Utility_Session::getInstance(null, Zend_Registry::get("session_length"), 'R', Zend_Registry::get("server_domain"), Zend_Registry::get("cur_server"));
        if (!Utility_Session::isSession()) { // MAKE SURE SESSION IS LIVE
            return $this->_redirect(Zend_Registry::get('full_url') . "/adminbnylogin");
        }

        try { // TRY TO GET SESSION DATA
            Utility_Session::extendSession(Zend_Registry::get("session_length"));
            Zend_Registry::set("user_id", $session->get('user_id'));
            Zend_Registry::set("username", $session->get('username'));
            Zend_Registry::set("name", $session->get('name'));
            Zend_Registry::set("email", $session->get('email'));
            Zend_Registry::set("permission_group_ids", $session->get('permission_group_ids'));
            Zend_Registry::set("admin_nav", $session->get("admin_nav"));
        } catch (Exception $e) { // KILL SESSION AND REDIRECT TO LOGIN ON FAILURE
            Utility_Session::_unsetSession();
            Utility_FlashMessenger::addMessage(
                    '<div class="error">Your session has timed out, please log in again. Sorry for the inconvenience.</div>'
            );
            return $this->_redirect(Zend_Registry::get('full_url') . "/adminbnylogin");
        }

        if (!Utility_Functions::canUserAccess($uri)) { // MAKE SURE USER HAS PERMISSION
            // log the failed access
            $admin = Zend_Registry::get('admin');
            $mapper = new Atlas_Model_AccessLogMapper();
            $log = new Atlas_Model_AccessLog();
            $log->setTimestamp(date("Y-m-d H:i:s", time()))
                    ->setUser_id($session->get('user_id'))
                    ->setIp_address(Zend_Registry::get("ip_add"))
                    ->setMessage("User attempted to access: " . $uri);
            $mapper->save($log);

            Utility_FlashMessenger::addMessage(
                    '<div class="error">You don\'t have permission to view this page. This attempt has been logged and if the attempt resembles an intrusion you will be contacted by your supervisors. However, if you feel you got this message in error please contact ' . $admin['email'] . '</div>'
            );
            return $this->_redirect('/adminbny/dashboard');
        }

        // pop all pending messages
        $this->view->messages = Utility_FlashMessenger::popMessage();
        
        //test
        //$inv_header = new Atlas_Model_InvHeaderMapper();
        //$inv_ids = $inv_header->buildInvoiceIDs();
        // initialize a cache object
        $frontend_options = array(
            'lifetime' => 86400, // cache lifetime of 24 hours
            'automatic_serialization' => false, // manual serialization for optimization
            'cache_id_prefix' => "ATLAS_", // atlas system prefix
            'ignore_user_abort' => true      // attempt to prevent corruption
        );
        $backend_options = array(
            'cache_dir' => '../cache/' // Directory where to put the cache files
        );
        $cache = Zend_Cache::factory(
                        'Core', 'File', $frontend_options, $backend_options
        );
        Zend_Registry::set("cache_handler", $cache);
        
        $categories = $cache->load("CATEGORY_LIST");
        if ($categories == false || empty($categories) || !is_array(unserialize($categories)) ) {
            $cache->remove("CATEGORY_LIST");
            $cat_mapper = new Atlas_Model_ProdsCategoriesMapper();
            $this->_categories_list = $cat_mapper->buildCategoriesList();
            $cache->save(serialize($this->_categories_list), "CATEGORY_LIST", array("CATEGORIES"));
        } else {
            $this->_categories_list = unserialize($categories);
        }
    }

    public function customerAction() {
        $this->view->title = "Customer Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $cust_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $cust_mapper = new Atlas_Model_CustomersMapper();
        $cust_form = new Atlas_Form_Customers();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            if ($cust_form->isValid($form_data)) {
                try {
                    $cust_mapper->processForm($form_data);
                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/wholesale/customers");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/wholesale/customers");
                }
            } else {
                $message = Utility_Error::buildErrors($cust_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($cust_id != 0) {
            try {
                $customer = $cust_mapper->find($cust_id);
                $values = $customer->toArray();
                $cust_form->populate($values);
            } catch (Exception $e) {
                return $this->_redirect("/wholesale/customer");
            }
        }
        $this->view->values = $values;
        $this->view->form = $cust_form;
    }

    public function customersAction() {
        $this->view->title = "Customers List";
        $cust_mapper = new Atlas_Model_CustomersMapper();
        $customers = $cust_mapper->buildActiveCustomers();
        $this->view->entries = $customers;
    }
    
    public function customersinactiveAction() {
        $this->view->title = "Customers List";
        $cust_mapper = new Atlas_Model_CustomersMapper();
        $customers = $cust_mapper->buildInactiveCustomers();
        $this->view->entries = $customers;
    }

    public function customerstatusAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request        =   $this->getRequest();
        $product_id     =   (int) $request->getParam("id", 0);
        $status         =   (int) $request->getParam("val", 1);

        // ensure the proper variables are present
        if ($product_id <= 0) {
            return $this->_redirect("/wholesale/customers");
        }

        // attempt to activate the unit
        $mapper = new Atlas_Model_CustomersMapper();
        $mapper->changeCustomerStatus($product_id,$status);
        
        die();
//        Utility_FlashMessenger::addMessage('<div class="success">Product Status has been updated.</div>');
//        return $this->_redirect("/wholesale/products");
    }
    
    public function paymentAction() {
        $request = $this->getRequest();
        $payment_id = (int) $request->getParam("id", 0);
        $inv_id = (int) $request->getParam("invid", 0);
        $src = $request->getParam("src", "html");
        
        if($src == "html"){
            $this->view->title = "Payment Creation/Modification";
        }else{
            $this->_helper->_layout->disableLayout();
            $this->getResponse()->clearBody();
        }

        $this->view->payment_id = $payment_id;
        $this->view->inv_id = $inv_id;
        // set up the mappers and forms
        $inv_header = new Atlas_Model_InvHeaderMapper();
        $cust_mapper = new Atlas_Model_InvPaymentsMapper();
        $cust_form = new Atlas_Form_Payments($inv_id);

        $total_payments = 0;
        $inv_total = 0;
        if($inv_id){
            $inv_data = $inv_header->buildInvoiceTotal($inv_id);
            $inv_total = $inv_data['inv_total'];
            
            $payments = $cust_mapper->buildInvPayment($inv_id);
            $total_payments = ($payments)?array_sum(array_column($payments,'payment_amount')):0;
        }
        $this->view->src = $src;
        $this->view->inv_total = $inv_total;
        $this->view->payments = $total_payments;
        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            if ($cust_form->isValid($form_data)) {
                try {
                    //CHECK PAYMENTS
                    $payment_inv_id = $form_data['inv_id'];
                    $payments_current = $cust_mapper->buildInvPayment($payment_inv_id);
                    $total_payments_current = ($payments)?(float)array_sum(array_column($payments_current,'payment_amount')):0;

                    $inv_total_current = $inv_header->buildInvoiceTotal($payment_inv_id);
                    $total_amount_current = (float)$inv_total_current['inv_total'];
                    $curr_prev_payments = (float)$total_payments_current + (float)$form_data['payment_amount'];
                    $diff = number_format(($curr_prev_payments-$total_amount_current) ,2);
                    if($total_payments_current >= $total_amount_current){
                        Utility_FlashMessenger::addMessage('<div class="error">This invoice has been fully paid.</div>');
                        return $this->_redirect("/wholesale/payment/invid/$payment_inv_id/id/$payment_id");
                    }else if($diff > number_format(0,2)){
                        Utility_FlashMessenger::addMessage("<div class='error'>Total Payments is greater than invoice total.</div>");
                        return $this->_redirect("/wholesale/payment/invid/$payment_inv_id/id/$payment_id");
                        
                    }else{
                        
                        $form_data['user_id'] = Zend_Registry::get("user_id");
                        $form_data['payment_datetime'] = date('Y-m-d H:i:s', strtotime($form_data['payment_datetime']));
                        $cust_mapper->processForm($form_data);

                        $payments = $cust_mapper->buildInvPayment($payment_inv_id);
                        $total_payments = 0;
                        if(is_array($payments) && count($payments) > 0){
                            foreach($payments as $inv_payment_amt){
                                $total_payments += $inv_payment_amt['payment_amount'];
                            }
                        }
                        $inv_total = $inv_header->buildInvoiceTotal($payment_inv_id);
                        $total_amount = $inv_total['inv_total'];

                        $amt_diff  = number_format($total_payments-$total_amount , 2);
                        $is_paid = ($amt_diff == number_format(0,2))?true:false;
                        
                        //Check if payments are complete to close the invoice
                        $inv_header_model = new Atlas_Model_InvHeader();
                        if($is_paid){
                            $inv_header_model = $inv_header->find($payment_inv_id);
                            $inv_header_model->setInv_paid(1)->setInv_status(2);
                            $inv_header->save($inv_header_model);
                        }else if($total_payments <= $total_amount){
                            $inv_header_model = $inv_header->find($payment_inv_id);
                            $inv_header_model->setInv_paid(0)->setInv_status(1);
                            $inv_header->save($inv_header_model);
                        }
                        
                        if(isset($form_data['print'])){
                            //Barcode
                            $inv_details = $inv_header->buildInvoice($payment_inv_id);
                            $file_name = "barcode_$payment_inv_id.jpg";
                            $file_path = Zend_Registry::get("root_path") . "/public/pdf/" . $file_name;
                            $barcode_text = $payment_inv_id;
                            $options = array('barHeight' => 30, 'barThinWidth' => 2, 'text' => $barcode_text, 'drawText' => FALSE, 'imageType' => 'jpeg');
                            $barcode = new Zend_Barcode_Object_Code128();
                            $barcode->setOptions($options);
                            $barcodeOBj = Zend_Barcode::factory($barcode);
                            $imageResource = $barcodeOBj->draw();
                            imagejpeg($imageResource, $file_path);

                            //Populate PDF file
                            $mapper = new Atlas_Model_PDFMapper('utf-8', 'A4', "fullpage", 0,0, 1);
                            $mapper->addCSSFile(Zend_Registry::get("root_path") . "/public/css/picklist_pdf.css");
                            if($inv_details['header']['inv_paid']==1){
                                $mapper->setWatermarkText('PAID');
                            }
                            $mapper->addContent(
                                    $this->view->partial('/wholesale/orderpicklist.phtml', array(
                                        "picklist" => $inv_details,
                                        "orderNumber" => $payment_inv_id,
                                        "barcode" => $barcode_text,
                                        "payments" => $total_payments
                            )));
                            $this->_helper->_layout->disableLayout();
                            $this->getResponse()->clearBody();
                            $mapper->outputPDF();
                            die();
                        }else{
                            Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed.</div>');
                            return $this->_redirect("/wholesale/payments");
                        }
                    }
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/wholesale/payments");
                }
            } else {
                $message = Utility_Error::buildErrors($cust_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($payment_id != 0) {
            try {
                $customer = $cust_mapper->find($payment_id);
                $values = $customer->toArray();
                $cust_form->populate($values);
            } catch (Exception $e) {
                return $this->_redirect("/wholesale/payment");
            }
        }
        $this->view->values = $values;
        $this->view->form = $cust_form;
    }
    
    public function submitpaymentAction() {
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $submit_data = Utility_Filter_DBSafe::clean($request->getPost());
            $form_values =  json_decode(html_entity_decode($submit_data['formdata']));
            $form_data = [];
            foreach($form_values as $element) $form_data[$element->name] = $element->value;
            
            $inv_id = (int) $form_data['inv_id'];
            $payment_form = new Atlas_Form_Payments($inv_id);
            $payment_id = (int) $form_data['payment_id'];

            if ($payment_form->isValid($form_data)) {
                try {
                    //CHECK PAYMENTS
                    $total_payments = $inv_total = 0;
                    $cust_mapper = new Atlas_Model_InvPaymentsMapper();
                    $inv_header = new Atlas_Model_InvHeaderMapper();
                    
                    if($inv_id){
                        $inv_data = $inv_header->buildInvoiceTotal($inv_id);
                        $inv_total = $inv_data['inv_total'];

                        $payments = $cust_mapper->buildInvPayment($inv_id);
                        $total_payments = ($payments)?array_sum(array_column($payments,'payment_amount')):0;
                    }
                    
                    $payment_inv_id = $inv_id;
                    $payments_current = $cust_mapper->buildInvPayment($payment_inv_id);
                    $total_payments_current = ($payments)?(float)array_sum(array_column($payments_current,'payment_amount')):0;
                    $inv_total_current = $inv_header->buildInvoiceTotal($payment_inv_id);
                    $total_amount_current = (float)$inv_total_current['inv_total'];
                    $curr_prev_payments = (float)$total_payments_current + (float)$form_data['payment_amount'];
                    $diff = number_format(($curr_prev_payments-$total_amount_current) ,2);
                    
                    if($total_payments_current >= $total_amount_current){
                        echo json_encode(
                                [
                                    'success' => false,
                                    'msg' => "This invoice has been fully paid."
                                    ]
                        );
                        die();
                        

                    }else if($diff > number_format(0,2)){
                        echo json_encode(
                                [
                                    'success' => false,
                                    'msg' => "Total Payments is greater than invoice total."
                                    ]
                        );
                        die();
                    }else{
                        
                        $form_data['user_id'] = Zend_Registry::get("user_id");
                        $form_data['payment_datetime'] = date('Y-m-d H:i:s', strtotime($form_data['payment_datetime']));
                        $cust_mapper->processForm($form_data);

                        $payments = $cust_mapper->buildInvPayment($payment_inv_id);
                        $total_payments = 0;
                        if(is_array($payments) && count($payments) > 0){
                            foreach($payments as $inv_payment_amt){
                                $total_payments += $inv_payment_amt['payment_amount'];
                            }
                        }
                        $inv_total = $inv_header->buildInvoiceTotal($payment_inv_id);
                        $total_amount = $inv_total['inv_total'];

                        $amt_diff  = number_format($total_payments-$total_amount , 2);
                        $is_paid = ($amt_diff == number_format(0,2))?true:false;
                        
                        //Check if payments are complete to close the invoice
                        $inv_header_model = new Atlas_Model_InvHeader();
                        if($is_paid){
                            $inv_header_model = $inv_header->find($payment_inv_id);
                            $inv_header_model->setInv_paid(1)->setInv_status(2);
                            $inv_header->save($inv_header_model);
                        }else if($total_payments <= $total_amount){
                            $inv_header_model = $inv_header->find($payment_inv_id);
                            $inv_header_model->setInv_paid(0)->setInv_status(1);
                            $inv_header->save($inv_header_model);
                        }
                        
                        $msg = "Payment submission was successfully processed.";
                        Utility_FlashMessenger::addMessage("<div class='success'>$msg</div>");
                        if($form_data['button_clicked'] == 'print'){
                            //Barcode
                            $inv_details = $inv_header->buildInvoice($payment_inv_id);
                            $file_name = "barcode_$payment_inv_id.jpg";
                            $file_path = Zend_Registry::get("root_path") . "/public/pdf/" . $file_name;
                            $barcode_text = $payment_inv_id;
                            $options = array('barHeight' => 30, 'barThinWidth' => 2, 'text' => $barcode_text, 'drawText' => FALSE, 'imageType' => 'jpeg');
                            $barcode = new Zend_Barcode_Object_Code128();
                            $barcode->setOptions($options);
                            $barcodeOBj = Zend_Barcode::factory($barcode);
                            $imageResource = $barcodeOBj->draw();
                            imagejpeg($imageResource, $file_path);

                            //Populate PDF file
                            $pdf_file_path = Zend_Registry::get("root_path") . "/public/pdf/" . $payment_inv_id . '.pdf';
                            $mapper = new Atlas_Model_PDFMapper('utf-8', 'A4', "fullpage", 0,0, 1);
                            $mapper->addCSSFile(Zend_Registry::get("root_path") . "/public/css/picklist_pdf.css");
                            if($inv_details['header']['inv_paid']==1){
                                $mapper->setWatermarkText('PAID');
                            }
                            $mapper->addContent(
                                    $this->view->partial('/wholesale/orderpicklist.phtml', array(
                                        "picklist" => $inv_details,
                                        "orderNumber" => $payment_inv_id,
                                        "barcode" => $barcode_text,
                                        "payments" => $total_payments
                            )));
                            $this->_helper->_layout->disableLayout();
                            $this->getResponse()->clearBody();
                            $mapper->outputPDFtoFile($pdf_file_path);
                            echo json_encode([
                                                'success' => true,
                                                'msg' => "Payment and print submissions were successfully processed",
                                                'file' => "/pdf/" . $payment_inv_id . '.pdf',
                                                'redirect' => "/wholesale/payments"
                                            ]);
                            die();
                        }else{
                            echo json_encode(
                                    [
                                        'success' => true,
                                        'msg' => $msg,
                                        'redirect' => "/wholesale/payments"
                                        ]
                            );
                            die();
                        }
                    }
                } catch (Exception $e) {
                    echo json_encode(
                            [
                                'success' => false,
                                'msg' => $e->getMessage(),
                                'redirect' => "/wholesale/payments"
                            ]
                    );
                    die();
                }
            } else {
                $message = Utility_Error::buildErrors($payment_form->getMessages());
                echo json_encode(
                        [
                            'success' => false,
                            'msg' => implode(' <br> ', $message),
                            'redirect' => "/wholesale/payments"
                        ]
                );
                die();
            }
        }
    }
    
    public function paymentsAction() {
        $this->view->title = "payments List";
        $payments_mapper = new Atlas_Model_InvPaymentsMapper();
        $payments = $payments_mapper->buildPayments();
        $this->view->entries = $payments;
    }
    
    public function paymenttypeAction() {
        $this->view->title = "Payment Methods Creation";

        // get user information
        $request = $this->getRequest();
        //$cust_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $PaymentTypes = new Atlas_Model_InvPaymentTypesMapper();
        //$cust_form = new Atlas_Form_Customers();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            //if ($cust_form->isValid($form_data)) {
                try {
                    $PaymentTypes->processForm($form_data);
                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/wholesale/paymenttypes");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/wholesale/paymenttype");
                }
//            } else {
//                $message = Utility_Error::buildErrors($cust_form->getMessages());
//                $this->view->messages = $message;
//            }
        } 
//        else if ($cust_id != 0) {
//            try {
//                $customer = $cust_mapper->find($cust_id);
//                $values = Utility_Filter_DBSafe::revert($customer->toArray());
//                $cust_form->populate($values);
//            } catch (Exception $e) {
//                return $this->_redirect("/wholesale/payment");
//            }
//        }
        //$this->view->values = $values;
        //$this->view->form = $cust_form;
    }

    public function paymenttypesAction() {
        $this->view->title = "payment Methods List";
        $payments_mapper = new Atlas_Model_InvPaymentTypesMapper();
        $payments = $payments_mapper->buildPaymentTypes();
        $this->view->entries = $payments;
    }
    
    public function voidpaymentAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request        =   $this->getRequest();
        $payment_id     =   (int) $request->getParam("id", 0);
        $inv_id         =   (int) $request->getParam("invid", 0);
        // ensure the proper variables are present
        if ($payment_id <= 0) {
            return $this->_redirect("/wholesale/payments");
        }

        // attempt to activate the unit
        $mapper = new Atlas_Model_InvPaymentsMapper();
        $pymt = $mapper->buildPayment($payment_id);
        
        //RESET INVOICE
        $inv_header = new Atlas_Model_InvHeaderMapper();
        $inv_header_model = new Atlas_Model_InvHeader();
        $inv_header_model = $inv_header->find($pymt['inv_id']);
        $inv_header_model->setInv_paid(0)->setInv_status(1);
        $inv_header->save($inv_header_model);
        
        $mapper->remove($payment_id);
        
        Utility_FlashMessenger::addMessage('<div class="success">Payment has been voided.</div>');
        if($inv_id)
            return $this->_redirect("/wholesale/invoice/id/".$inv_id);
        else
            return $this->_redirect("/wholesale/payments");
    }
    
    public function catlivesearchAction() {
        // clear the layout information
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form_data = $request->getPost();
            $q = strtolower(trim($form_data["q"]));
            $cat_id = (int)$form_data["cat_id"];
            $cat_list = '';
            if($q){
                $cat_list .= "<ul class='country-list' id='country-list_category'>";
                $categories = $this->_categories_list;
                foreach($categories as $key => $value)
                {
                    if(stripos(strtolower($value),$q) !== false && $key != $cat_id){
                        $cat_list .=    '<li class="select_category_suggest" '
                                . ' text="'. $value .'"'
                                . ' rel="'.$key.'">' 
                                .$value
                                . '</li>';
                    }
                }
                $cat_list .= '</ul>';
            }
            echo $cat_list;
        }
        die();
    }
    
    public function productAction() {
        $this->view->title = "Product Creation/Modification";
        
        // get user information
        $request = $this->getRequest();
        $prod_id = (int) $request->getParam("id", 0);
        $cat_id = (int) $request->getParam("cat_id", 0);

        // set up the mappers and forms
        $prod_mapper = new Atlas_Model_ProdsMapper();
        $inv_log_mapper = new Atlas_Model_InvLogMapper();
        $prod_form = new Atlas_Form_Prods();        
        
        // get categories list
        $this->view->categories = $this->_categories_list;
        $redirect_url = ($prod_id)?"/wholesale/product/id/$prod_id":"/wholesale/product";
        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            if ($prod_form->isValid($form_data)) {
                try {
                    if (is_uploaded_file($_FILES['prod_image']['tmp_name']) && $_FILES['prod_image']['size'] > 0) {
                        $prod_image = $prod_mapper->uploadFile($form_data, $_FILES['prod_image']);
                        $form_data['prod_image'] = $prod_image;
                    }
                    $prod_data = ($prod_id)?$prod_mapper->buildProdData($prod_id):[];
                    $prod_id = $prod_mapper->processForm($form_data);
                    $map_array = [
                        'category_id' => 'cat_id',
                        'inventory' => 'prod_name',
                        'description' => 'prod_desc',
                        'upc_code' => 'prod_code',
                        'cost_price' => 'prod_cost',
                        'sale_price' => 'prod_sale',
                        'product_status' => 'prod_status'
                    ];
                    //LOG CHANGES
                    $data_updates = '';
                    foreach($prod_data as $key => $value){
                        if($form_data[$map_array[$key]] != $value)
                            $data_updates .= $key .' has changed from '.$value.' to '.$form_data[$map_array[$key]]."<br>";
                    }
                    
                    $log_data = [
                        'log_type' =>  'PROD',
                        'log_text'  => $form_data['notes'],
                        'log_update'  => ($data_updates)?$data_updates:'No Data Changes',
                        'log_userid'  => Zend_Registry::get("user_id"),
                        'log_datetime'  => date('Y-m-d H:i:s'),
                        'log_record_id'  => $prod_id
                    ];
                    $inv_log_mapper = new Atlas_Model_InvLogMapper();
                    $inv_log_mapper->save(new Atlas_Model_InvLog($log_data));
                    
                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect($redirect_url);
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect($redirect_url);
                }
            } else {
                $message = Utility_Error::buildErrors($prod_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($prod_id != 0) {
            try {
                $product = $prod_mapper->find($prod_id);
                $values = $product->toArray();
                $prod_form->populate($values);
                $log_records = $inv_log_mapper->buildRecordLogs($prod_id, 'PROD');
            } catch (Exception $e) {
                return $this->_redirect($redirect_url);
            }
        }
        $this->view->values = $values;
        $this->view->form = $prod_form;
        $this->view->logs = $log_records;
    }

    public function categoryAction() {
        $this->view->title = "Categories Creation/Modification";

        // get user information
        $request = $this->getRequest();
        $cat_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $cat_mapper = new Atlas_Model_ProdsCategoriesMapper();
        $cat_form = new Atlas_Form_ProdsCategories($cat_id);
        
        // get categories list
        $this->view->categories = $this->_categories_list;
        
        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            if ($cat_form->isValid($form_data)) {
                try {
                    if (is_uploaded_file($_FILES['cat_image']['tmp_name']) && $_FILES['cat_image']['size'] > 0) {
                        $cat_image = $cat_mapper->uploadFile($form_data, $_FILES['cat_image']);
                        $form_data['cat_image'] = $cat_image;
                    }
                    $cat_mapper->processForm($form_data);
                    
                    //REMOVE CACHE
                    $cache = Zend_Registry::get('cache_handler');
                    $cache->remove("CATEGORY_LIST");
                    
                    //RELOAD CACHE
                    $cat_mapper = new Atlas_Model_ProdsCategoriesMapper();
                    $categories = $cat_mapper->buildCategoriesList();
                    $cache->save(serialize($categories), "CATEGORY_LIST", array("CATEGORIES"));
                    
                    Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                    return $this->_redirect("/wholesale/categories");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/wholesale/category");
                }
            } else {
                $message = Utility_Error::buildErrors($cat_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($cat_id != 0) {
            try {
                $catgory = $cat_mapper->find($cat_id);
                $values = $catgory->toArray();
                $cat_form->populate($values);
            } catch (Exception $e) {
                return $this->_redirect("/wholesale/category");
            }
        }
        $this->view->values = $values;
        $this->view->form = $cat_form;
    }
    
    public function categoriesAction() {
        $this->view->title = "Categories List";
        $cat_mapper = new Atlas_Model_ProdsCategoriesMapper();
        $active = $cat_mapper->buildActiveCategories();
        $inactive = $cat_mapper->buildInActiveCategories();        
        $cats = $cat_mapper->buildAllCategories();
        $this->view->cats = $cats;
        $this->view->entries = $active;
        $this->view->entries1 = $inactive;
    }
    
    public function productsAction() {
        $request = $this->getRequest();
        $action = $request->getParam("act", "hidecost");
        $this->view->action = $action;
        
        $this->view->title = "Products List";
        $prod_mapper = new Atlas_Model_ProdsMapper();
        $active = $prod_mapper->buildActiveProducts();
        
        $prodcat_mapper = new Atlas_Model_ProdsCategoriesMapper();
        $cats = $prodcat_mapper->buildAllCategories();
        
        $this->view->entries = $active;
        $this->view->cats = $cats;
    }
    
    public function catproductAction() {
        $request = $this->getRequest();
        $action = $request->getParam("act", "hidecost");
        $cat_id = $request->getParam("id", 0);
        
        $prod_mapper = new Atlas_Model_ProdsMapper();
        $active = $prod_mapper->buildCatActiveProducts($cat_id);
        
        $prodcat_mapper = new Atlas_Model_ProdsCategoriesMapper();
        $cats = $prodcat_mapper->buildAllCategories();
        
        $this->view->cat_id = $cat_id;
        $this->view->action = $action;
        $this->view->title = "Category ".$cats[$cat_id];
        $this->view->entries = $active;
        $this->view->cats = $cats;
    }
    
    public function inactiveproductsAction() {
        $this->view->title = "Products List";
        $prod_mapper = new Atlas_Model_ProdsMapper();
        $active = $prod_mapper->buildInActiveProducts();
        $this->view->entries = $active;
    }
    
    
    public function productstatusAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request        =   $this->getRequest();
        $product_id     =   (int) $request->getParam("id", 0);
        $status         =   (int) $request->getParam("val", 1);

        // ensure the proper variables are present
        if ($product_id <= 0) {
            return $this->_redirect("/wholesale/products");
        }

        // attempt to activate the unit
        $mapper = new Atlas_Model_ProdsMapper();
        $mapper->changeProductStatus($product_id,$status);
        
        die();
//        Utility_FlashMessenger::addMessage('<div class="success">Product Status has been updated.</div>');
//        return $this->_redirect("/wholesale/products");
    }

    public function catstatusAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request        =   $this->getRequest();
        $cat_id     =   (int) $request->getParam("id", 0);
        $status         =   (int) $request->getParam("val", 1);

        // ensure the proper variables are present
        if ($cat_id <= 0) {
            return $this->_redirect("/wholesale/categories");
        }

        // attempt to activate the unit
        $mapper = new Atlas_Model_ProdsCategoriesMapper();
        $mapper->changeCatStatus($cat_id,$status);

        Utility_FlashMessenger::addMessage('<div class="success">Category Status has been updated.</div>');
        return $this->_redirect("/wholesale/categories");
    }
    
    public function deactivateinvoiceAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request        =   $this->getRequest();
        $inv_id     =   (int) $request->getParam("id", 0);
        $location   =   $request->getParam("location", '');
        // ensure the proper variables are present
        if ($inv_id <= 0) {
            return $this->_redirect("/wholesale/$location");
        }

        // attempt to activate the unit
        $inv_header = new Atlas_Model_InvHeader();
        $inv_header_mapper = new Atlas_Model_InvHeaderMapper();
        $inv_header = $inv_header_mapper->find($inv_id);
        $inv_header->setInv_status(3);
        $inv_header_mapper->save($inv_header);
        
        Utility_FlashMessenger::addMessage('<div class="success">Inovice has been Drafted.</div>');
        return $this->_redirect("/wholesale/$location");
    }
    
    
    public function voidinvoiceAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request    =   $this->getRequest();
        $inv_id     =   (int) $request->getParam("id", 0);
        $location   =   $request->getParam("location", '');
        // ensure the proper variables are present
        if ($inv_id <= 0) {
            return $this->_redirect("/wholesale/$location");
        }

        if($location == 'sales-orders'){
            // REMOVE LINES
            $mapper = new Atlas_Model_SoLinesMapper();
            $mapper->removeInvoice($inv_id);

            // REMOVE HEADER
            $mapper1 = new Atlas_Model_SoHeaderMapper();
            $mapper1->remove($inv_id);
        }else{
            // REMOVE LINES
            $mapper = new Atlas_Model_InvLinesMapper();
            $mapper->removeInvoice($inv_id);

            // REMOVE PAYMENTS
            $mapper2 = new Atlas_Model_InvPaymentsMapper();
            $mapper2->removePayments($inv_id);

            // REMOVE HEADER
            $mapper1 = new Atlas_Model_InvHeaderMapper();
            $mapper1->remove($inv_id);

            // Add to Invoice Sequenece to be used later
            $inv_seq_mapper = new Atlas_Model_InvSeqMapper();
            $id = $inv_seq_mapper->insert(new Atlas_Model_InvSeq(['inv_id' => $inv_id]));
        }
        Utility_FlashMessenger::addMessage('<div class="success">Inovice has been voided.</div>');
        return $this->_redirect("/wholesale/$location");
    }

    public function invoiceAction() {
        $this->view->title = "Invoice Creation";
        $request = $this->getRequest();
        $inv_id = (int) $request->getParam("id", 0);
        $action = $request->getParam("act", '');            
        
        $customers_mapper = new Atlas_Model_CustomersMapper();
        $customers = $customers_mapper->buildCustomersList();
        $this->view->customers = $customers;
        
        $products_mapper = new Atlas_Model_ProdsMapper();
        $products = $products_mapper->buildProductData();
        $this->view->products = $products;
        
        if($action == 'convertso'){
            $inv_header = new Atlas_Model_SoHeaderMapper();
            $inv_details = $inv_header->buildInvoice($inv_id);
            $this->view->invoice_data = $inv_details;
            $this->view->inv_id = $inv_id;
            $this->view->payments = [];
        }else{
            $inv_header = new Atlas_Model_InvHeaderMapper();
            $inv_details = $inv_header->buildInvoice($inv_id);

            $this->view->invoice_data = $inv_details;
            $this->view->inv_id = $inv_id;

            $payment_mapper = new Atlas_Model_InvPaymentsMapper();
            $payments = $payment_mapper->buildInvPayment($inv_id);
            $this->view->payments = $payments;
        }
        // process or initialize the form
        if ($request->isPost()) {
            
            
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            try {
                $inv_date   =   date('Y-m-d',strtotime($form_data['start_date'])).date(' H:i:s');
                $inv_header_mapper = new Atlas_Model_InvHeaderMapper();
                $inv_header = new Atlas_Model_InvHeader();
                if($action == 'convertso'){
                    $so_id = $inv_id;
                    $inv_id = 0;
                }

                if($inv_id != 0){
                    $inv_header = $inv_header_mapper->find($inv_id);
                    if($inv_header->getInv_cust_id() != $form_data['cust_id_inv'] && $inv_header->getInv_cust_id() != 0){
                        $inv_header->setInv_id(NULL)->setInv_paid(0)->setInv_status(1);
                    }
                }else{
                    $inv_seq_mapper = new Atlas_Model_InvSeqMapper();
                    $new_inv_id = $inv_seq_mapper->buildInvId();
                    if($new_inv_id != 0){
                        $inv_header_mapper->insert(new Atlas_Model_InvHeader(['inv_id'=>$new_inv_id]));
                        $inv_seq_mapper->remove($new_inv_id);
                        $inv_header = $inv_header_mapper->find($new_inv_id);
                    }
                    $inv_header->setInv_paid(0)->setInv_status(1);
                }
                
                $inv_header->setInv_cust_id($form_data['cust_id_inv'])
                        ->setInv_notes($form_data['notes'])
                        ->setInv_user_id(Zend_Registry::get("user_id"))
                        ->setInv_total($form_data['invoice_total_input'])
                        ->setInv_discount((float)$form_data['discount'])
                        ->setInv_date($inv_date)
                        ->setInv_shipping($form_data['inv_shipping'])
                        ->setInv_payments($form_data['inv_payments'])
                        ->setInv_outstanding($form_data['inv_outstanding'])
                        ->setInv_payment($form_data['inv_payment'])
                        ->setInv_ccp($form_data['inv_ccp'])
                        ->setInv_cca($form_data['inv_cca'])
                        ->setInv_qty($form_data['invoice_qty_input'])
                        ->setInv_sub_total($form_data['invoice_sub_total_input'])
                        ->setInv_credits($form_data['invoice_credit_input']);
                //$inv_header->setInv_status($form_data['inv_status']);
                $header_id = $inv_header_mapper->save($inv_header);
                
                //Check Invoice Lines
                $line_error = false;
                foreach ($form_data['line_count'] as $line_id_check) {
                    if ((int) $form_data["product_$line_id_check"] == 0) {
                        $line_error = true;
                    }
                }
                if ($line_error) {
                    Utility_FlashMessenger::addMessage("<div class='warning'>Empty Invoice Lines not allowed</div>");
                }
                
                //Update Inventory
                $old_lines = (is_array($inv_details) && array_key_exists('lines', $inv_details) && is_array($inv_details['lines']) && count($inv_details['lines']) > 0)?$inv_details['lines']:[];
                if($inv_id == 22900){ 
                    $old_lines_data = $old_lines;
                    $curr_prod_qty = [];
                    if(is_array($old_lines_data) && count($old_lines_data) > 0){
                        foreach ($old_lines_data as $curr_line) {
                            $curr_prod_qty[$curr_line['product_id']] += $curr_line['product_qty'];
                        }
                    }

                    $new_prod_qty = [];
                    foreach ($form_data['line_count'] as $line_no) {
                        $new_prod_qty[$form_data["product_$line_no"]] += $form_data["qty_$line_no"];
                    }
                    $new_inventory = [];
                    foreach($new_prod_qty as $key => $val){
                        if(is_array($curr_prod_qty) && count($curr_prod_qty) > 0){
                            if(array_key_exists($key, $curr_prod_qty)){
                                $new_inventory[$key] = $curr_prod_qty[$key] - $val;
                                unset($curr_prod_qty[$key]);
                            }else{
                                $new_inventory[$key] = -$val;
                            }
                        }else{
                            $new_inventory[$key] = -$val;
                        }
                    }
                    if(is_array($curr_prod_qty) && count($curr_prod_qty) > 0){
                        foreach($curr_prod_qty as $key1 => $val1){
                            $new_inventory[$key1] = $val1;
                        }    
                    }
                    
                    $adjusted_inventory = [];
                    foreach($new_inventory as $prod_id => $qty){
                        if($qty != 0){
                            $adjusted_inventory[$prod_id] = $qty;
                            $prod_info = $products_mapper->find($prod_id);
                            $inventory = (float) $prod_info->getProd_name();
                            $qty_update = $inventory + $qty;
                            $data = ["prod_name" => $qty_update];
                            $products_mapper->getDbTable()->update($data, array('prod_id = ?' => $prod_id));
                        }
                    }
                    
                }
                $form_data['new_inv_id'] = $header_id;
                $products_mapper->adjustInventory($old_lines , $form_data);
                
                //Check Invoice Lines
                $inv_lines = new Atlas_Model_InvLinesMapper();
                if($header_id == $inv_id){
                    $inv_lines->removeInvoice($inv_id);
                }
                foreach ($form_data['line_count'] as $line_no) {
                    $inv_line = new Atlas_Model_InvLines();
                    $inv_line->setInv_id($header_id)
                            ->setProduct_id($form_data["product_$line_no"])
                            ->setProduct_price($form_data["price_$line_no"])
                            ->setProduct_qty($form_data["qty_$line_no"])
                            ->setInv_line_type($form_data["linetype_$line_no"])
                            ->setInv_last_pp($form_data["line_Last_input_$line_no"]);
                    $inv_lines->save($inv_line);
                }
                
                if($action == 'convertso'){
                    // REMOVE LINES
                    $mapper = new Atlas_Model_SoLinesMapper();
                    $mapper->removeInvoice($so_id);

                    // REMOVE HEADER
                    $mapper1 = new Atlas_Model_SoHeaderMapper();
                    $mapper1->remove($so_id);
                }

                Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed.</div>');
                //Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed'. json_encode($adjusted_inventory) .'</div>');
                return $this->_redirect("/wholesale/invoice/id/$header_id");
            } catch (Exception $e) {
                Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                return $this->_redirect("/wholesale/invoices");
            }
        }
    }

    public function invoiceajaxAction() {
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        $request = $this->getRequest();
        $form_data = Utility_Filter_DBSafe::clean($request->getPost());
        $type = $form_data['type'];
        $this->view->type = $type;
        try {
            if ($request->isPost()) {
                if ($type == "cust") {
                    $customers_mapper = new Atlas_Model_CustomersMapper();
                    $cust_info = $customers_mapper->buildCustInfo($form_data['code']);
                    $this->view->info = $cust_info;
                } else if ($type == "prod") {
                    $customers_mapper = new Atlas_Model_ProdsMapper();
                    $cust_info = $customers_mapper->buildProdInfo($form_data['code']);
                    $prod_id = ($cust_info['prod_id'])?$cust_info['prod_id']:0;
                    $cust_id = $form_data['cust'];
                    $inv_header_mapper = new Atlas_Model_InvHeaderMapper();
                    $last_pp = $inv_header_mapper->buildLastPurchasePrice($cust_id,$prod_id,$form_data['inv']);
                    echo $cust_info['prod_code'] . '##' . $cust_info['prod_sale'] . '##' . $cust_info['prod_name'] . '##' .$last_pp['product_price'];
                } else if ($type == "code") {
                    $customers_mapper = new Atlas_Model_ProdsMapper();
                    $cust_info = $customers_mapper->buildProdCodeInfo($form_data['code']);
                    $prod_id = ($cust_info['prod_id'])?$cust_info['prod_id']:0;
                    $cust_id = $form_data['cust'];
                    $inv_header_mapper = new Atlas_Model_InvHeaderMapper();
                    $last_pp = $inv_header_mapper->buildLastPurchasePrice($cust_id,$prod_id,$form_data['inv']);
                    echo $cust_info['prod_code'] . '##' . $cust_info['prod_sale'] . '##' . $cust_info['prod_name'] . '##' . $cust_info['prod_id'] . '##' .$last_pp['product_price'];
                } else if ($type == "add_row") {
                    $this->view->row_no = $form_data['code'];
                } else if ($type == "add_row_pop") {
                    $products_mapper = new Atlas_Model_ProdsMapper();
                    $product_info = $products_mapper->buildProdCodeInfo($form_data['pcode']);
                    $prod_id = ($product_info['prod_id'])?$product_info['prod_id']:0;
                    $cust_id = $form_data['cust'];
                    $inv_header_mapper = new Atlas_Model_InvHeaderMapper();
                    $last_pp = $inv_header_mapper->buildLastPurchasePrice($cust_id,$prod_id,$form_data['inv']);
                    //$this->view->products = $products;
                    $this->view->prod_info = $product_info;
                    $this->view->row_no = $form_data['code'];
                    $this->view->last_pp = $last_pp;
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function invoiceinfoAction() {
        $request = $this->getRequest();
        $action = $request->getParam("act", "");
        $id = (int) $request->getParam("id", 0);
        $inv_header = new Atlas_Model_InvHeaderMapper();
        $so_header = new Atlas_Model_SoHeaderMapper();
        $total_payments = 0;
        if ( in_array($action, ['printso','downloadso'])) {
            $inv_details = $so_header->buildSalesOrder($id);
        }else{
            $inv_details = $inv_header->buildInvoice($id);
            $payment_mapper = new Atlas_Model_InvPaymentsMapper();
            $payments = $payment_mapper->buildInvPayment($id);
            $total_payments = ($payments)?array_sum(array_column($payments,'payment_amount')):0;
        }
        $orderNumber = $id;
        $pdf_file_path = Zend_Registry::get("root_path") . "/public/pdf/" . $orderNumber . '.pdf';
        if(in_array($action, ['download','print','email'])){
            //Barcode
            $file_name = "barcode_$orderNumber.jpg";
            $file_path = Zend_Registry::get("root_path") . "/public/pdf/" . $file_name;
            $barcode_text = $orderNumber;
            $options = array('barHeight' => 30, 'barThinWidth' => 2, 'text' => $barcode_text, 'drawText' => FALSE, 'imageType' => 'jpeg');
            $barcode = new Zend_Barcode_Object_Code128();
            $barcode->setOptions($options);
            $barcodeOBj = Zend_Barcode::factory($barcode);
            $imageResource = $barcodeOBj->draw();
            imagejpeg($imageResource, $file_path);

            //Populate PDF file
            $mapper = new Atlas_Model_PDFMapper('utf-8', 'A4', "fullpage", 0,0, 1);
            $mapper->addCSSFile(Zend_Registry::get("root_path") . "/public/css/picklist_pdf.css");
            if($inv_details['header']['inv_paid']==1){
                $mapper->setWatermarkText('PAID');
            }
            $mapper->addContent(
                    $this->view->partial('/wholesale/orderpicklist.phtml', array(
                        "picklist" => $inv_details,
                        "orderNumber" => $orderNumber,
                        "barcode" => $barcode_text,
                        "payments" => $total_payments
            )));
            $mapper->outputPDFtoFile($pdf_file_path);
        }else if( in_array($action, ['printso','invoiceso','downloadso']) ){
            //Populate PDF file
            $mapper = new Atlas_Model_PDFMapper('utf-8', 'A4', "fullpage", 0,0, 1);
            $mapper->addCSSFile(Zend_Registry::get("root_path") . "/public/css/picklist_pdf.css");
            $mapper->addContent(
                    $this->view->partial('/wholesale/orderpicklist_2.phtml', array(
                        "picklist" => $inv_details,
                        "orderNumber" => $orderNumber,
            )));
            $mapper->outputPDFtoFile($pdf_file_path);
        }
        if( in_array($action, ['download','downloadso'])){
            if($action == 'download'){
                $download_file_name = "InvNo_" . basename($pdf_file_path);
            }else if($action == 'downloadso'){
                $download_file_name = "SoNo_" . basename($pdf_file_path);
            }
            header("Content-type:application/pdf");
            header("Content-Disposition:attachment;filename=$download_file_name");
            readfile($pdf_file_path);    
            die();
        }
        if ($action == 'email') {
            $subject = "DSD Wholesale Invoice No. " . $orderNumber;
            $to_recipients = array();
            $to_recipients[] = array('email' => $inv_details['header']['cust_email'], 'name' => $inv_details['header']['cust_name']);
            $email = new Utility_Emails_Invoice($subject, $inv_details, $to_recipients, $orderNumber . '.pdf');
            $email->send();
            
            Utility_FlashMessenger::addMessage('<div class="success">Invoice No '.$inv_details['header']['inv_id'].' has been successfully emailed.</div>');
            $redirect = ($inv_details['header']['inv_paid'] == 1)?'invoices':'pending-invoices';
            return $this->_redirect("/wholesale/$redirect");
        } else if (in_array($action, ['print','printso','invoiceso'])) {
            $this->_helper->_layout->disableLayout();
            $this->getResponse()->clearBody();
            $mapper->outputPDF();
            die();
        }
    }

    public function draftInvoicesAction() {
        $this->view->title = "Draft Invoices List";
        $cust_mapper = new Atlas_Model_InvHeaderMapper();
        $customers = $cust_mapper->buildDraftInvoices();
        $this->view->entries = $customers;
    }
    
    public function invoicesAction() {
        $this->view->title = "Invoices List";
        $cust_mapper = new Atlas_Model_InvHeaderMapper();
        $customers = $cust_mapper->buildInvoices();
        $this->view->entries = $customers;
    }
    
    public function pendingInvoicesAction() {
        $this->view->title = "Invoices List";
        $cust_mapper = new Atlas_Model_InvHeaderMapper();
        $customers = $cust_mapper->buildPendingInvoices();
        $this->view->entries = $customers;
    }

    public function productlivesearchAction() {
        // clear the layout information
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form_data = $request->getPost();
            $q = $form_data["q"];
            $row = $form_data["row"];
            $mapper = new Atlas_Model_ProdsMapper();
            $prod_list = $mapper->buildProductLiveSearch($q,$row);
            echo $prod_list;
        }
        die();
    }
    
    public function custlivesearchAction() {
        // clear the layout information
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form_data = $request->getPost();
            $q = strtolower(trim($form_data["q"]));
            $search_by = strtolower(trim($form_data["search_by"]));
            $mapper = new Atlas_Model_CustomersMapper();
            $cust_list = $mapper->buildCustLiveSearch($q,$search_by);
            echo $cust_list;
        }
        die();
    }

    public function reportAction() {
        $this->view->title = "Invoicing Report";
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            if($form_data['type'] == 'inv'){
                $infomapper = new Atlas_Model_InvHeaderMapper();
                $results = $infomapper->buildInvReport($form_data);
            }else{
                $infomapper = new Atlas_Model_SoHeaderMapper();
                $results = $infomapper->buildInvReport($form_data);
            }
            if($form_data['cust_id_inv']){
                $customers_mapper = new Atlas_Model_CustomersMapper();
                $form_data['cust_data'] = $customers_mapper->buildCustLiveSearchData($form_data['cust_id_inv']);
            }
            if($form_data['product_1']){
                $products_mapper = new Atlas_Model_ProdsMapper();
                $form_data['prod_data'] = $products_mapper->buildProdInfo($form_data['product_1']);
            }
            if (is_array($results) && count($results) > 0) {
                if (isset($form_data['export'])) {
                    $this->view->output = 1;
                    $this->_helper->_layout->disableLayout();
                    $this->getResponse()->clearBody();
                    header("Content-type: application/x-msdownload");
                    header("Content-Disposition: attachment; filename=\"InvoicingReport.csv\"");
                    header("Pragma: no-cache");
                    header("Expires: 0");
                } else {
                    $this->view->output = 0;
                }
                $this->view->records = $results;
                
            } else {
                Utility_FlashMessenger::addMessage('<div class="error">No Results Found</div>');
                return $this->_redirect("/wholesale/report");
            }
        }
        $this->view->entries = $form_data;
    }
    
    public function paymentsReportAction() {
        $this->view->title = "Payments Report";
        $request = $this->getRequest();
        $py_mapper = new Atlas_Model_InvPaymentTypesMapper();
        $pay_types = $py_mapper->buildPaymentTypes();
        $this->view->pay_types = $pay_types;
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            $infomapper = new Atlas_Model_InvPaymentsMapper();
            $results = $infomapper->buildInvReport($form_data);
            if($form_data['cust_id_inv']){
                $customers_mapper = new Atlas_Model_CustomersMapper();
                $form_data['cust_data'] = $customers_mapper->buildCustLiveSearchData($form_data['cust_id_inv']);
            }
            if (is_array($results) && count($results) > 0) {
                if (isset($form_data['export'])) {
                    $this->view->output = 1;
                    $this->_helper->_layout->disableLayout();
                    $this->getResponse()->clearBody();
                    header("Content-type: application/x-msdownload");
                    header("Content-Disposition: attachment; filename=\"PaymentsReport.csv\"");
                    header("Pragma: no-cache");
                    header("Expires: 0");
                } else {
                    $this->view->output = 0;
                }
                $this->view->records = $results;
            } else {
                Utility_FlashMessenger::addMessage('<div class="error">No Results Found</div>');
                return $this->_redirect("/wholesale/payments-report");
            }
        }
        $this->view->entries = $form_data;
    }
    
    public function balanceReportAction() {
        $this->view->title = "Customer Balance Report";
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            $infomapper = new Atlas_Model_InvHeaderMapper();
            $data = $infomapper->buildCustomerBalanceReport($form_data);
            if($form_data['cust_id_inv']){
                $customers_mapper = new Atlas_Model_CustomersMapper();
                $form_data['cust_data'] = $customers_mapper->buildCustLiveSearchData($form_data['cust_id_inv']);
            }
            $results = $data['summary'];
            $details = $data['details'];
            if (is_array($results) && count($results) > 0) {
                if (isset($form_data['export'])) {
                    $this->view->output = 1;
                    $this->_helper->_layout->disableLayout();
                    $this->getResponse()->clearBody();
                    header("Content-type: application/x-msdownload");
                    header("Content-Disposition: attachment; filename=\"CustomerBalanceReport.csv\"");
                    header("Pragma: no-cache");
                    header("Expires: 0");
                } else {
                    $this->view->output = 0;
                }
                $this->view->records = $results;
                $this->view->details = $details;
            } else {
                Utility_FlashMessenger::addMessage('<div class="error">No Results Found</div>');
                return $this->_redirect("/wholesale/balance-report");
            }
        }
        $this->view->entries = $form_data;
    }
    
    public function salesOrderAction() {
        $this->view->title = "Sales Order Creation";
        $request = $this->getRequest();
        $products_mapper = new Atlas_Model_ProdsMapper();
        $products = $products_mapper->buildProductData();
        $this->view->products = $products;
        $inv_id = (int) $request->getParam("id", 0);
        $inv_header = new Atlas_Model_SoHeaderMapper();
        $inv_details = $inv_header->buildSalesOrder($inv_id);
        $this->view->invoice_data = $inv_details;
        $this->view->inv_id = $inv_id;

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            try {
                $inv_date   =   date('Y-m-d',strtotime($form_data['start_date'])).date(' H:i:s');
                $inv_header_mapper = new Atlas_Model_SoHeaderMapper();
                $inv_header = new Atlas_Model_SoHeader();
                if($inv_id != 0){
                    $inv_header = $inv_header_mapper->find($inv_id);
                }
                $inv_header->setInv_cust_id($form_data['cust_id_inv'])
                            ->setInv_notes($form_data['notes'])
                            ->setInv_user_id(Zend_Registry::get("user_id"))
                            ->setInv_total($form_data['invoice_total_input'])
                            ->setInv_discount((float)$form_data['discount'])
                            ->setInv_date($inv_date)
                            ->setInv_shipping($form_data['inv_shipping'])
                            ->setInv_payment($form_data['inv_payment'])
                            ->setInv_payments($form_data['inv_payments'])
                            ->setInv_outstanding($form_data['inv_outstanding'])
                            ->setInv_ccp($form_data['inv_ccp'])
                            ->setInv_cca($form_data['inv_cca'])
                            ->setInv_qty($form_data['invoice_qty_input'])
                            ->setInv_sub_total($form_data['invoice_sub_total_input'])
                            ->setInv_credits($form_data['invoice_credit_input'])
                            ->setInv_paid(0)->setInv_status(1);
                $header_id = $inv_header_mapper->save($inv_header);
                
                $old_lines = (is_array($inv_details) && array_key_exists('lines', $inv_details) && is_array($inv_details['lines']) && count($inv_details['lines']) > 0)?$inv_details['lines']:[];
                $adjusted_inventory = $products_mapper->adjustInventory($old_lines , $form_data);
                
                $inv_lines = new Atlas_Model_SoLinesMapper();
                if($header_id == $inv_id){
                    $inv_lines->removeInvoice($inv_id);
                }
                foreach ($form_data['line_count'] as $line_no) {
                    $inv_line = new Atlas_Model_SoLines();
                    $inv_line->setInv_id($header_id)
                            ->setProduct_id($form_data["product_$line_no"])
                            ->setProduct_price($form_data["price_$line_no"])
                            ->setProduct_qty($form_data["qty_$line_no"])
                            ->setInv_line_type($form_data["linetype_$line_no"])
                            ->setInv_last_pp($form_data["line_Last_input_$line_no"]);
                    $inv_lines->save($inv_line);
                }
                Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                //Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed'. json_encode($adjusted_inventory).'</div>');
                return $this->_redirect("/wholesale/sales-order/id/$header_id");
            } catch (Exception $e) {
                Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                return $this->_redirect("/wholesale/sales-order");
            }
        }
    }
    
    public function salesOrdersAction() {
        $this->view->title = "Sales Orders List";
        $cust_mapper = new Atlas_Model_SoHeaderMapper();
        $customers = $cust_mapper->buildSalesOrdersList();
        $this->view->entries = $customers;
    }
    
    public function updatecustomerAction() {
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        $request = $this->getRequest();
        try {
            if ($request->isPost()) {
                $form_data = Utility_Filter_DBSafe::clean($request->getPost());
                $cust_id = $form_data['cust_id'];
                $phone = $form_data['cust_phone_update'];
                $email = $form_data['cust_email_update'];
                $lic_exp = (!empty(trim($form_data['cust_lic_exp_update'])))?date('Y-m-d', strtotime($form_data['cust_lic_exp_update'])):'';
                $contact = $form_data['cust_contact_update'];
                
                $customers_mapper = new Atlas_Model_CustomersMapper();
                $customer = new Atlas_Model_Customers();
                $customer = $customers_mapper->find($cust_id);
                $customer->setCust_contact($contact)
                        ->setCust_phone($phone)
                        ->setCust_email($email)
                        ->setCust_lic_exp($lic_exp);
                $customers_mapper->save($customer);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        die();
    }
    
    public function categoryproductsAction() {
        $this->view->title = "Product Category Assignments";

        // get the parameters
        $request = $this->getRequest();
        $cat_id = (int) $request->getParam("id", 0);

        // ensure a valid permission group was selected
        if ($cat_id <= 0) {
            return $this->_redirect("/wholesale/categoryproducts");
        }

        // setup required mappers
        $cateories_mapper = new Atlas_Model_ProdsCategoriesMapper();
        $prods_mapper = new Atlas_Model_ProdsMapper();

        // ensure the id given was valid
        try {
            $category_data = $cateories_mapper->find($cat_id);
        } catch (Exception $e) {
            Utility_FlashMessenger::addMessage('<div class="error">'.$e->getMessage().'</div>');
            return $this->_redirect("/wholesale/categoryproducts");
        }

        // pass the user arrays to the view for use in the control console
        $this->view->category   =   $category_data;
        $this->view->products   =   $prods_mapper->buildAllProductsList();
        $this->view->assigned   =   $prods_mapper->buildAssignedProducts($cat_id);
    }
    
    public function productcategoryassignmentAction() { 
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $permission_group_id = (int) $request->getParam("group", 0);
        $user_id = (int) $request->getParam("user", 0);

        // ensure the proper variables are present
        if ($permission_group_id <= 0 || $user_id <= 0) {
            echo "failed";
            die();
        }

        // attempt to add the user to the group
        $mapper = new Atlas_Model_ProdsMapper();
        $mapper->addProdToCategory($user_id, $permission_group_id);

        echo "success";
        die();
    }

    public function productcategoryremovalAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $permission_group_id = (int) $request->getParam("group", 0);
        $user_id = (int) $request->getParam("user", 0);

        // ensure the proper variables are present
        if ($permission_group_id <= 0 || $user_id <= 0) {
            echo "failed";
            die();
        }

        // attempt to remove the user from the group
        $mapper = new Atlas_Model_ProdsMapper();
        $mapper->removeProdFromoCategory($user_id);

        echo "success";
        die();
    }
    
    public function voidlogAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request    =   $this->getRequest();
        $log_id     =   (int) $request->getParam("id", 0);
        $rec_id     =   (int) $request->getParam("recid", 0);
        $location   =   $request->getParam("location", '');
        // ensure the proper variables are present
        if ($log_id <= 0) {
            return $this->_redirect("/wholesale/$location/id/$rec_id");
        }

        $mapper1 = new Atlas_Model_InvLogMapper();
        $mapper1->remove($log_id);

        Utility_FlashMessenger::addMessage('<div class="success">Log entry has been voided.</div>');
        return $this->_redirect("/wholesale/$location/id/$rec_id");
    }
    
    public function __call($methodName, $args) {
        $uri = $this->getRequest()->getRequestUri();
        $admin = Zend_Registry::get('admin');
        // log the failed access
        $mapper = new Atlas_Model_AccessLogMapper();
        $log = new Atlas_Model_AccessLog();
        $log->setTimestamp(date("Y-m-d H:i:s", time()))
                ->setUser_id(Zend_Registry::get('user_id'))
                ->setIp_address(Zend_Registry::get("ip_add"))
                ->setMessage("User attempted to access: " . $uri);
        $mapper->save($log);

        Utility_FlashMessenger::addMessage(
                '<div class="error">The page you requested doesn\'t exist. This attempt has been logged and if the attempt resembles an intrusion you will be contacted by your supervisors. However, if you feel you got this message in error please contact ' . $admin['email'] . '</div>'
        );
        return $this->_redirect('/adminbny/dashboard');
    }


    
}

?>