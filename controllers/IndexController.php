<?php
class IndexController extends Zend_Controller_Action {
    public function init() {
        // set the CSS documents for the website
         $this->view->css_docs = array(
            Zend_Registry::get("global_css"), 
            "/css/index.css",
            "/css/smoothness/jquery-ui-1.8.17.custom.css");
        // set the JS documents for the website
        $this->view->js_docs = array(
            "/js/jquery.1.6.2.js", 
            "/js/jquery.dataTables.min.js",
            "/js/jquery.jBreadCrumb.1.1.js",
            "/js/jquery.easing.1.3.js", 
            "/js/global.js", 
            "/js/index.js");

        // set the default layout
        $this->_helper->layout->setLayout('blank');
        try{
            $uri     = $this->getRequest()->getRequestUri();
            $session = Utility_Session::getInstance(null, Zend_Registry::get("session_length"), 'R', (strtolower(APPLICATION_ENV) == "production") ? Zend_Registry::get("domain") : (Zend_Registry::get("server_domain")), Zend_Registry::get("cur_server"));
            if(isset($_COOKIE['user_sc'])) {
                $user_data  =   unserialize($_COOKIE['user_sc']);
                Zend_Registry::set("user_id", $user_data['user_id']);
                Zend_Registry::set("username",  $user_data['username']);
                Zend_Registry::set("name",  $user_data['name']);
                Zend_Registry::set("email",  $user_data['email']);
            }else if(Utility_Session::isSession()){
                Utility_Session::extendSession(Zend_Registry::get("session_length"));
                Zend_Registry::set("user_id", $session->get('user_id'));
                Zend_Registry::set("username", $session->get('username'));
                Zend_Registry::set("name", $session->get('name'));
                Zend_Registry::set("email", $session->get('email'));
            }else{
                Zend_Registry::set("user_id", 0);
                Zend_Registry::set("username", '');
                Zend_Registry::set("name", '');
                Zend_Registry::set("email", '');
            }
        }catch(Exception $e){
            Zend_Registry::set("user_id", 0);
            Zend_Registry::set("username", '');
            Zend_Registry::set("name", '');
            Zend_Registry::set("email", '');
        }

        // pop all pending messages
        $this->view->messages = Utility_FlashMessenger::popMessage();
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    public function indexAction() {
        return $this->_redirect('/cart/index');
    }

    public function dashboardAction() {
        $this->view->title = "Atlas Dashboard";

        // get user information
        $user_id = Zend_Registry::get("user_id");
        $permission_group_ids = Zend_Registry::get("permission_group_ids");
        $this->view->page_groups = Zend_Registry::get("admin_nav");
        $this->view->user_id = $user_id;

        // setup the mappers to be used
        $notification = new Atlas_Model_NotificationsMapper();
        $user_notification = new Atlas_Model_UserNotificationsMapper();

        // determine which notifications to display
        $notifications = $notification->buildUserNotifications($permission_group_ids);
        $this->view->notifications = $user_notification->filterNotifications($notifications, $user_id);
    }

    public function clearspcacheAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the cache handler
        shell_exec("rm -rf " . Zend_Registry::get("root_path") . "/cache/*");

        Utility_FlashMessenger::addMessage('<div class="success">The selected items in cache have been cleared.</div>');
        return $this->_redirect("/dashboard");
    }

    public function clearbmcacheAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the cache handler
        shell_exec("rm -rf " . Zend_Registry::get("root_path") . "/cache/*");

        Utility_FlashMessenger::addMessage('<div class="success">The selected items in cache have been cleared.</div>');
        return $this->_redirect("/dashboard");
    }

    public function spolicyAction()
    {
	$this->view->title = "JFI Security Policy";
    }

    public function __call( $methodName, $args )
    {
            $uri = $this->getRequest()->getRequestUri();
            $admin  =   Zend_Registry::get('admin');
            // log the failed access
            $mapper = new Atlas_Model_AccessLogMapper();
            $log    = new Atlas_Model_AccessLog();
            $log->setTimestamp(date("Y-m-d H:i:s", time()))
                    ->setUser_id(Zend_Registry::get('user_id'))
                    ->setIp_address(Zend_Registry::get("ip_add"))
                    ->setMessage("User attempted to access: ".$uri);
            $mapper->save($log);

            Utility_FlashMessenger::addMessage(
                    '<div class="error">The page you requested doesn\'t exist. This attempt has been logged and if the attempt resembles an intrusion you will be contacted by your supervisors. However, if you feel you got this message in error please contact '.$admin['email'].'</div>'
            );
            return $this->_redirect('/cart/index');
    }

}

?>
