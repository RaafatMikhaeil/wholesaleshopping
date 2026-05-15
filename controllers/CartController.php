<?php

class CartController extends Zend_Controller_Action {

    public function init() {
        session_start();
        // set the CSS documents for the website
        $this->view->css_docs = array(
            Zend_Registry::get("global_css"),
            "/assets/css/bootstrap.min.css"
        );
        // set the JS documents for the website
        $this->view->js_docs = array(
            "/assets/js/jquery.min.js",
            "/assets/js/bootstrap.min.js"
        );

        // set the default layout
        $this->_helper->layout->setLayout('blank');
        try{
            $uri     = $this->getRequest()->getRequestUri();
            $session = Utility_Session::getInstance(null, Zend_Registry::get("session_length"), 'R', (strtolower(APPLICATION_ENV) == "production") ? Zend_Registry::get("domain") : (Zend_Registry::get("server_domain")), Zend_Registry::get("cur_server"));
            if(isset($_COOKIE['user_sc'])) {
                $user_data  =   unserialize($_COOKIE['user_sc']);
                Zend_Registry::set("user_id", $user_data['user_id']);
                Zend_Registry::set("cust_id", $user_data['cust_id']);
                Zend_Registry::set("username",  $user_data['username']);
                Zend_Registry::set("name",  $user_data['name']);
                Zend_Registry::set("email",  $user_data['email']);
            }else if(Utility_Session::isSession()){
                Utility_Session::extendSession(Zend_Registry::get("session_length"));
                Zend_Registry::set("user_id", $session->get('user_id'));
                Zend_Registry::set("cust_id", $user_data['cust_id']);
                Zend_Registry::set("username", $session->get('username'));
                Zend_Registry::set("name", $session->get('name'));
                Zend_Registry::set("email", $session->get('email'));
            }else{
                Zend_Registry::set("user_id", 0);
                Zend_Registry::set("cust_id", 0);
                Zend_Registry::set("username", '');
                Zend_Registry::set("name", '');
                Zend_Registry::set("email", '');
            }
        }catch(Exception $e){
            Zend_Registry::set("user_id", 0);
            Zend_Registry::set("cust_id", 0);
            Zend_Registry::set("username", '');
            Zend_Registry::set("name", '');
            Zend_Registry::set("email", '');
        }
        // pop all pending messages
        $this->view->messages = Utility_FlashMessenger::popMessage();
        $user_id = Zend_Registry::get('user_id');
        $cust_id = Zend_Registry::get('cust_id');
        $this->view->user_id = $user_id;
        $this->view->cust_id = $cust_id;
    }

    public function indexAction() {
        $prod_mapper = new Atlas_Model_ProdsMapper();
        $result = $prod_mapper->buildDisplayActiveProducts();
        $this->view->rows = $result;
    }

    public function cartAction() {
        $cust_id = Zend_Registry::get('cust_id');
        if($cust_id == 0){  die(); }
        $cart_mapper = new Atlas_Model_CartMapper();
        $rows = $cart_mapper->buildUserCart($cust_id);
        $this->view->rows = $rows;
    }

    public function checkoutAction() {
        $cust_id = Zend_Registry::get('cust_id');
        if(!$cust_id){  die(); }
        $cart_mapper = new Atlas_Model_CartMapper();
        
        $rows = $cart_mapper->buildUserCartCheckOut($cust_id);
        if(count($rows) > 0){
            $this->view->rows = $rows;
        }else{
            return $this->_redirect("/cart/cart");
        }
        
    }

    public function categoryAction() {
        $request = $this->getRequest();
        $cat_id = Utility_Filter_DBSafe::clean($request->getParam("id",0));
        $prod_mapper = new Atlas_Model_ProdsMapper();
        $result = $prod_mapper->buildCatDisplayActiveProducts($cat_id);
        
        $cat_mapper = new Atlas_Model_ProdsCategoriesMapper();
        $result1 = $cat_mapper->buildCategoryInfo($cat_id);
                
        $this->view->rows = $result;
        $this->view->category = $result1;
    }
        
    public function productAction() {
        $request = $this->getRequest();
        $prod_id = Utility_Filter_DBSafe::clean($request->getParam("id",0));
        $prod_mapper = new Atlas_Model_ProdsMapper();
        $product = $prod_mapper->buildProdInfo($prod_id);
        $this->view->product = $product;
        
    }
    
    public function actionAction() {
        $user_id = Zend_Registry::get('user_id');
        $cust_id = Zend_Registry::get('cust_id');
        if(!$user_id){  die(); }
        $request = $this->getRequest();
        $cartItem = Utility_Filter_DBSafe::clean($request->getParam("cartItem", ""));
        $remove = Utility_Filter_DBSafe::clean($request->getParam("remove", 0));
        $clear = Utility_Filter_DBSafe::clean($request->getParam("clear", ""));
        $cart_mapper = new Atlas_Model_CartMapper();
        if ($request->getPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
        }else{
            die();
        }

        // Add products into the cart table
        if (isset($form_data['pid']) && !isset($form_data['action']) && $form_data['action'] != 'update_cart') {
            $pid = $form_data['pid'];
            $pqty = $form_data['pqty'];
            $code = $cart_mapper->CheckItemCart($cust_id, $pid);
            if ($code) {
                $cart_mapper->save(new Atlas_Model_Cart(
                                                        [
                                                            'user_id' => $user_id, 
                                                            'cust_id' => $cust_id, 
                                                            'prod_id' => $pid, 
                                                            'qty' => $pqty
                                                        ]));
                echo '<div class="alert alert-success alert-dismissible mt-2">
                          <button type="button" class="close" data-dismiss="alert">&times;</button>
                          <strong>Item added to your cart!</strong>
                        </div>';
            } else {
                echo '<div class="alert alert-danger alert-dismissible mt-2">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong>Item already added to your cart!</strong>
                        </div>';
            }
        }

        // Get no.of items available in the cart table
        if (isset($cartItem) && $cartItem == 'cart_item') {
            $rows = $cart_mapper->buildUserCartCount($cust_id);
            echo $rows;
        }

        // Remove single items from cart
        if (isset($remove) && $remove != 0) {
            $id = $remove;

            $cart_mapper->remove($id);

            $_SESSION['showAlert'] = 'block';
            $_SESSION['message'] = 'Item removed from the cart!';
            header('location:/cart/cart');
        }

        // Remove all items at once from cart
        if (isset($clear) && !empty($clear)) {
            $cart_mapper->clear($cust_id);
            $_SESSION['showAlert'] = 'block';
            $_SESSION['message'] = 'All Item removed from the cart!';
            header('location:/cart/cart');
        }

        // Set total price of the product in the cart table
        if (isset($form_data['qty'])) {
            $qty = $form_data['qty'];
            $pid = $form_data['pid'];
            $pprice = $form_data['pprice'];
            $cart_mapper->updateCartItem($pid, $cust_id, $qty);
            die();
        }

        // Checkout and save customer info in the orders table
        if (isset($form_data['action']) && isset($form_data['action']) == 'order') {
            $rows       = $cart_mapper->buildUserCartCheckOut($cust_id);
            $inv_total  = array_sum(array_column($rows,'total_price'));
            $inv_qty  = array_sum(array_column($rows,'qty'));
            $inv_notes  = $form_data['notes'];
            $inv_date   =   date('Y-m-d H:i:s');
            $inv_header_mapper  = new Atlas_Model_InvHeaderMapper();
            $inv_header         = new Atlas_Model_InvHeader();
            $inv_header->setInv_id(NULL)
                    ->setInv_cust_id($cust_id)
                    ->setInv_notes($inv_notes)
                    ->setInv_user_id($user_id)
                    ->setInv_total($inv_total)
                    ->setInv_discount(0)
                    ->setInv_paid(0)
                    ->setInv_date($inv_date)
                    ->setInv_status(0);
            $header_id = $inv_header_mapper->save($inv_header);
            
            $lines = [];
            $inv_lines = new Atlas_Model_InvLinesMapper();
            foreach($rows as $row){
                $inv_line = new Atlas_Model_InvLines();
                $inv_line->setInv_id($header_id)
                        ->setProduct_id($row['product_id'])
                        ->setProduct_price($row['product_price'])
                        ->setProduct_qty($row['qty']);
                $inv_lines->save($inv_line);
                $lines[] = [
                    'Product Name' => $row['product_name'],
                    'Product Qty' => $row['qty'],
                    'Product Price' => '$'.$row['product_price'],
                    'Line Total' => '$'.$row['total_price']
                ];
            }

            //Remove Cart Items
            $cart_mapper->clear($cust_id);
            
            //Display success MSG
            $data = '';
            $data .= '<div class="text-center">
                            <h1 class="display-4 mt-2 text-danger">Thank You!</h1>
                            <h2 class="text-success">Your Order Placed Successfully!</h2>
                            <h4 class="bg-primary text-light rounded p-2">Your Order # ' . $header_id . '</h4>
                            <h5 class="bg-info text-light rounded p-2">You\'ll be contacted regarding your order shortly.</h5>
                            <a href="/cart" class="btn btn-success"><i class="fas fa-cart-plus"></i>&nbsp;&nbsp;Continue Shopping</a>
                        </div>';
            $email_info = [
                'lines' => $lines,
                'order_no' => $header_id,
                'cust_id' => $cust_id,
                'user_id' => $user_id,
                'total' => '$'.number_format($inv_total,2),
                'total_qty' => number_format($inv_qty),
            ];
            
            //Email Order Information
            $email = new Utility_Emails_Order($email_info);
            $email->send();
            
            echo $data;
        }
        die();
    }

    

    
    public function loginAction() {
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        $request = $this->getRequest();
        // check the form data and preform the associated action
        if ($request->getPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost()); 
            $user = new Atlas_Model_UsersMapper();
            try {
                $valid_login = $user->isUserValidCart($form_data['username'],$form_data['password']);
                echo $valid_login;
            } catch (Exception $e) {
                Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                echo json_encode(['success' => false, 'msg' => $e->getMessage()]);   
            }
        }
        die();
    }
    
    public function logoutAction() {
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        // destroy user session
        Utility_Session::_unsetSession();
        
        unset($_COOKIE['user_sc']);
        setcookie('user_sc', null, -1, '/'); 
        return $this->_redirect("/");
    }
    
    public function __call($methodName, $args) {
        Utility_FlashMessenger::addMessage(
                '<div class="error">The page you requested doesn\'t exist. This attempt has been logged and if the attempt resembles an intrusion you will be contacted by your supervisors. However, if you feel you got this message in error please contact ' . $admin['email'] . '</div>'
        );
        return $this->_redirect('/cart/index');
    }
}

?>