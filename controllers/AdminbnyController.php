<?php
class AdminbnyController extends Zend_Controller_Action {
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
        $this->_helper->layout->setLayout('layout');

        // check if user is logged in and if they can access the current page
        $uri = $this->getRequest()->getRequestUri();
        $session = Utility_Session::getInstance(null, Zend_Registry::get("session_length"), 'R', Zend_Registry::get("server_domain"), Zend_Registry::get("cur_server"));
        if (!Utility_Session::isSession()) { // MAKE SURE SESSION IS LIVE
            return $this->_redirect(Zend_Registry::get('full_url') . "/adminbnylogin");
        }

        try { // TRY TO GET SESSION DATA
            $session = Utility_Session::getInstance($result[0]['user_id'], Zend_Registry::get("session_length"), "W", Zend_Registry::get("server_domain"), Zend_Registry::get("cur_server"));            
            Utility_Session::extendSession(Zend_Registry::get("session_length"));
            Zend_Registry::set("user_id", $session->get('user_id'));
            Zend_Registry::set("username", $session->get('username'));
            Zend_Registry::set("name", $session->get('name'));
            Zend_Registry::set("email", $session->get('email'));
            Zend_Registry::set("permission_group_ids", $session->get('permission_group_ids'));
            Zend_Registry::set("admin_nav", $session->get("admin_nav"));
            //Zend_Registry::set("menu", $session->get("menu"));
        } catch (Exception $e) { // KILL SESSION AND REDIRECT TO LOGIN ON FAILURE
            Utility_Session::_unsetSession();
            Utility_FlashMessenger::addMessage(
                    '<div class="error">Your session has timed out, please log in again. Sorry for the inconvenience.</div>'
            );
            return $this->_redirect(Zend_Registry::get('full_url') . "/adminbnylogin");
        }

        if ($session->get('permission_group_ids') == "0") { // CHECK IF USER IS ASSIGNED TO A GROUP
            // log the failed access
            $mapper = new Atlas_Model_AccessLogMapper();
            $log = new Atlas_Model_AccessLog();
            $log->setTimestamp(date("Y-m-d H:i:s", time()))
                    ->setUser_id($session->get('user_id'))
                    ->setIp_address(Zend_Registry::get("ip_add"))
                    ->setMessage("User has no assigned group and attempted to access site");
            $mapper->save($log);

            Utility_Session::_unsetSession();
            Utility_FlashMessenger::addMessage(
                    '<div class="error">You have not been assigned a role in the system yet, please contact ' .
                    'support to have this resolved. Sorry for the inconvenience.</div>');
            return $this->_redirect(Zend_Registry::get('full_url') . "/adminbnylogin");
        } else if (!Utility_Functions::canUserAccess("/adminbny/dashboard")) { // CHECK IF USER CAN VIEW DASHBOARD
            // log the failed access
            $mapper = new Atlas_Model_AccessLogMapper();
            $log = new Atlas_Model_AccessLog();
            $log->setTimestamp(date("Y-m-d H:i:s", time()))
                    ->setUser_id($session->get('user_id'))
                    ->setIp_address(Zend_Registry::get("ip_add"))
                    ->setMessage("User attempted to access Atlas with a suspended account");
            $mapper->save($log);

            Utility_Session::_unsetSession();
            Utility_FlashMessenger::addMessage('<div class="error">Your account is not permitted to access Atlas.</div>');
            return $this->_redirect(Zend_Registry::get('full_url') . "/adminbnylogin");
        }

        // pop all pending messages
        $this->view->messages = Utility_FlashMessenger::popMessage();
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    public function indexAction() {
        return $this->_redirect('/adminbny/dashboard');
    }

    public function dashboardAction() {
        $this->view->title = "BNY Dashboard";

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
        return $this->_redirect("/adminbny/dashboard");
    }

    public function clearbmcacheAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the cache handler
        shell_exec("rm -rf " . Zend_Registry::get("root_path") . "/cache/*");

        Utility_FlashMessenger::addMessage('<div class="success">The selected items in cache have been cleared.</div>');
        return $this->_redirect("/adminbny/dashboard");
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
            return $this->_redirect('/adminbny/dashboard');
    }

}

?>
