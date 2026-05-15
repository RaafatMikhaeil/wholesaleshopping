<?php
class UserController extends Zend_Controller_Action {
    public function init() {
        // set the CSS documents for the website
        $this->view->css_docs = array(
            Zend_Registry::get("global_css"),
            "/css/user.css",
            "/css/smoothness/jquery-ui-1.8.17.custom.css",
            "/css/smoothness/jalerts.css",
            "/css/jquery-ui.css",
            "/css/login.css");

        // set the JS documents for the website
        $this->view->js_docs = array(
            "/js/jquery.1.6.2.js",
            "/js/jquery-ui-1.8.17.custom.min.js",
            "/js/jquery.jBreadCrumb.1.1.js",
            "/js/jquery.easing.1.3.js",
            "/js/jquery.dataTables.min.js",
            "/js/jalerts.js",
            "/js/global.js",    
            "/js/user.js");

        // set the default layout
        $this->_helper->layout->setLayout('layout');
        $uri = $this->getRequest()->getRequestUri();
        
        // check if user is logged in and if they can access the current page
        if ($uri == "/user/usersc" || $uri == "/adminbnylogin" || $uri == "/" || $uri == "/user/forgot" || preg_match("/\/user\/reset\/.*/", $uri) || preg_match("/\/exppasschange\/.*/", $uri)) {
            if ( ($uri == "/adminbnylogin" || $uri == "/adminbnylogin") && Utility_Session::isSession()) {
                return $this->_redirect('/adminbnylogout');
            }
        } else {
            $session = Utility_Session::getInstance(null, Zend_Registry::get("session_length"), 'R', Zend_Registry::get("server_domain"), Zend_Registry::get("cur_server"));
            if (!Utility_Session::isSession()) { // MAKE SURE SESSION IS LIVE
                return $this->_redirect('/adminbnylogin');
            }

            try { // TRY TO GET AND SET SESSION DATA
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

            if( !Utility_Functions::canUserAccess($uri) ) { // MAKE SURE USER HAS PERMISSION
                    // log the failed access
                    $admin  =   Zend_Registry::get('admin');
                    $mapper = new Atlas_Model_AccessLogMapper();
                    $log    = new Atlas_Model_AccessLog();
                    $log->setTimestamp(date("Y-m-d H:i:s", time()))
                            ->setUser_id($session->get('user_id'))
                            ->setIp_address(Zend_Registry::get("ip_add"))
                            ->setMessage("User attempted to access: ".$uri);
                    $mapper->save($log);

                    Utility_FlashMessenger::addMessage(
                            '<div class="error">You don\'t have permission to view this page. This attempt has been logged and if the attempt resembles an intrusion you will be contacted by your supervisors. However, if you feel you got this message in error please contact '.$admin['email'].'</div>'
                    );
                    return $this->_redirect('/adminbny/dashboard');
            }
        }

        // pop all pending messages
        $this->view->messages = Utility_FlashMessenger::popMessage();
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    public function indexAction() {
        return $this->_redirect('/adminbnylogin');
    }

    public function forgotAction() {
        $this->view->title = "Forgot Password?";
        $this->view->js_docs = array();

        $u_mapper = new Atlas_Model_UsersMapper();
        $mapper = new Atlas_Model_ForgotPasswordMapper();
        $form = new Atlas_Form_Login();
        $this->view->form = $form;
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form_data = $request->getPost();
            $result = $u_mapper->checkUsernameemail($form_data['username'],$form_data['email']);
            if ((int) $result['user_id'] > 0) {
                // attempt to reset the user's password
                $mapper = new Atlas_Model_UsersMapper();
                $user = $mapper->find($result['user_id']);
                $password = $mapper->resetUserPassword($result['user_id']);

                $recepients[] = array("email" => $result['email'], "name" => $result['name']);
                $email = new Utility_Emails_UserResetPassword($recepients,$result['username'], $password, $result['name']);
                $email->send();

                Utility_FlashMessenger::addMessage('<div class="success">An email was sent with your new password. If you don\'t receive the email please contact the administrator to update your email address. </div>');
                return $this->_redirect(Zend_Registry::get('full_url') . "/adminbnylogin");
            } else {
                Utility_FlashMessenger::addMessage('<div class="error">The given username or email was not found in the system.</div>');
                return $this->_redirect(Zend_Registry::get('full_url') . "/adminbnylogin");
            }
        }
    }

    public function resetAction() {
        $this->view->title = "Reset Password";
        $this->view->js_docs = array();

        $fp_mapper = new Atlas_Model_ForgotPasswordMapper();
        $mapper = new Atlas_Model_UsersMapper();
        $form = new Atlas_Form_PasswordReset();
        $this->view->form = $form;
        $request = $this->getRequest();

        $keys = explode("_", $request->getParam("key"));
        if (!$fp_mapper->checkKeyPair($keys[0], $keys[1], Zend_Registry::get("ip_add"), Zend_Registry::get("http_agent"))) {
            Utility_FlashMessenger::addMessage('<div class="error">Invalid Key Paired Used.</div>');
            $this->_redirect("/adminbnylogin");
        }

        if ($request->isPost()) {
            $form_data = $request->getPost();

            $u_mapper = new Atlas_Model_UsersMapper();
            $user = $u_mapper->find($keys[0]);

            $values = explode(" ", $user->getName());
            $first_name = $values[0];
            $last_name = $values[1];
            $values = explode("@", $user->getEmail());
            $email_section = $values[0];
            $black_list = array(
                "password", "p@ssword", "p@ssw0rd", "passw0rd",
                "jarrow", "j@rrow", "j@rr0w", "jarr0w",
                $user->getUsername(),
                $email_section, $first_name, $last_name
            );

            $bl_password = false;
            foreach ($black_list as $entry) {
                if (preg_match("/" . $entry . "/", strtolower($form_data['password']))) {
                    $bl_password = true;
                }
            }

            $pr_test = new Atlas_Model_PasswordResetMapper();
            $pc_password = $pr_test->buildPasswordCheck($keys[0], md5($form_data['password']));

            if ($bl_password || $pc_password || strlen($form_data['password']) < 6) {
                Utility_FlashMessenger::addMessage('<div class="error">You entered an invalid password</div>');
                return $this->_redirect("/user/reset/key/" . $request->getParam("key"));
            }

            $user->setPassword(md5($form_data['password']));
            $u_mapper->save($user);

            Utility_FlashMessenger::addMessage('<div class="success">Your password was updated.</div>');
            return $this->_redirect(Zend_Registry::get('full_url') . "/adminbnylogin");
        }
    }

    public function loginAction() {
        $uri = $this->getRequest()->getRequestUri();
        $session = Utility_Session::getInstance(null, Zend_Registry::get("session_length"), 'R', Zend_Registry::get("server_domain"), Zend_Registry::get("cur_server"));
        try { // TRY TO GET AND SET SESSION DATA
            Utility_Session::extendSession(Zend_Registry::get("session_length"));
            Zend_Registry::set("user_id", $session->get('user_id'));
            return $this->_redirect("/adminbny/dashboard");
        } catch (Exception $e) { // KILL SESSION AND REDIRECT TO LOGIN ON FAILURE
            //echo $e->getMessage();die();
            Utility_Session::_unsetSession();
        }
        $this->view->title = "User log in";
        $this->view->js_docs = array();
        
        //check IP adress attempts
        $mapper = new Atlas_Model_AccessLogMapper();
        $this->view->attempts = $mapper->getFailedAttempts(filter_input(INPUT_SERVER, "REMOTE_ADDR"));

        // setup the login form
        $form = new Atlas_Form_Login();
        $this->view->form = $form;
        
        // check the form data and preform the associated action
        if ($this->_request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($this->_request->getPost());
            if ($form->isValid($form_data)) {
                $valid_login = false;
                $user = new Atlas_Model_UsersMapper();
                try {
                    $valid_login = $user->isUserValid($form_data['username'], $form_data['password']);
                } catch (Exception $e) {
                    // log the failed access
                    $log = new Atlas_Model_AccessLog();
                    $log->setTimestamp(date("Y-m-d H:i:s", time()))
                            ->setUser_id(98) // guest user, no login available
                            ->setIp_address(Zend_Registry::get("ip_add"))
                            ->setMessage("Failed login attempt, user: " . $form_data['username']);
                    $mapper->save($log);
                                        
                    $this->view->messages = array(
                        "<div class='error'>" . $e->getMessage() . "</div>"
                    );
                    
                    if($e->getCode() == 401)
                        {Utility_FlashMessenger::addMessage(
                            '<div class="error">It has been 6 months or more since your last password change, please change your password.</div>'
                        );   
                        return $this->_redirect($e->getMessage());
                    };
                }

                if ($valid_login) {
                    return $this->_redirect('/adminbny/dashboard');
                }
            } else {
                // log the failed access
                $mapper = new Atlas_Model_AccessLogMapper();
                $log = new Atlas_Model_AccessLog();
                $log->setTimestamp(date("Y-m-d H:i:s", time()))
                        ->setUser_id(98) // guest user, no login available
                        ->setIp_address(Zend_Registry::get("ip_add"))
                        ->setMessage("Failed login attempt, user: " . $form_data['username']);
                $mapper->save($log);

                $this->view->messages = array(
                    "<div class='error'>Invalid login. Please re-enter your username and password.</div>"
                );
            }
        }
    }

    public function userscAction() {
        $request = $this->getRequest();
        $logout = $request->getParam("logout", 0);
        $this->view->title = "User log in";
        $this->view->js_docs = array();
        if($logout==1){
            unset($_COOKIE['user_sc']);
            setcookie('user_sc', null, -1, '/'); 
            return $this->_redirect("/smartcart/index");
        }
        if (isset($_COOKIE['user_sc'])) {
            return $this->_redirect("/smartcart/index");
        }

        // check the form data and preform the associated action
        if ($this->_request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($this->_request->getPost());
            $user = new Atlas_Model_UsersMapper();
            try {
                $str = $form_data['user_id'];
                $arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i', $str);
                if(!isset($arr[0]) || !isset($arr[1])){
                    Utility_FlashMessenger::addMessage('<div class="error">User is invalid</div>');
                    return $this->_redirect('/user/usersc');
                }else{
                    $valid_login = $user->isUserValidScUser((int) $arr[0], $arr[1]);
                }
            } catch (Exception $e) {
                Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                return $this->_redirect('/user/usersc');
            }

            if ($valid_login) {
                return $this->_redirect('/smartcart/index');
            }
        }
    }

    public function emailBlastAction() {
        // set the default layout
        $this->_helper->layout->setLayout('tinymce_layout');

        $this->view->title = "Email Blaster";

        $request = $this->getRequest();
        $mapper = new Atlas_Model_EmailBlastMapper();

        if ($request->isPost()) {
            $form_data = $request->getPost();
            try {
                $mapper->processForm($form_data);
            } catch (Exception $e) {
                Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                return $this->_redirect('/user/email-blast');
            }

            Utility_FlashMessenger::addMessage('<div class="success">Your request was successfully processed.</div>');
            return $this->_redirect('/user/email-blast');
        }
    }

    public function unlockAction() {
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        $request = $this->getRequest();
        $user_id = (int) $request->getParam("id", 0);

        if ($user_id <= 0)
            return $this->_redirect('/user/users');

        $mapper = new Atlas_Model_ForgotPasswordMapper();
        $mapper->resetFailedAttempts($user_id);
        die();
    }

    public function logoutAction() {
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        $user_id = Zend_Registry::get("user_id");
        // destroy user session
        Utility_Session::_unsetSession();

        // store the logout in the logs
        $login_mapper = new Atlas_Model_LoginLogsMapper();
        $record = $login_mapper->getlastRecord($user_id);
        if (count($record)>0 && $record['logout_date'] == "" && $record['user_id']!='') {
            $logout = New Atlas_Model_LoginLogs($record);
            $logout->setLogout_date(date("Y-m-d H:i:s", time()));
            $login_mapper->save($logout);
        }
        return $this->_redirect('/adminbnylogin');
    }

    public function killAction() {
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        $user_id = Zend_Registry::get("user_id");
        try {
            Utility_Session::_unsetSession();
            // store the logout in the logs
            $login_mapper = new Atlas_Model_LoginLogsMapper();
            $record = $login_mapper->getlastRecord($user_id);
            if (count($record)>0 && $record['logout_date'] == "" && $record['user_id']!='') {
                $logout = New Atlas_Model_LoginLogs($record);
                $logout->setLogout_date(date("Y-m-d H:i:s", time()));
                $login_mapper->save($logout);
            }
            echo "User Session Terminated";
        } catch (Exception $e) {
            echo "User Session Termination Failed";
        }
        die();
    }

    public function keepaliveAction() {
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        try {
            Utility_Session::extendSession(Zend_Registry::get("session_length"));
            echo "User Session Extended";
        } catch (Exception $e) {
            echo "User Session Extension Failed";
        }
        die();
    }

    public function previewAction() {
        $request = $this->getRequest();
        $data = $request->getPost();
        $token = Zend_Registry::get("user_id") . "_" . time();
        $filename = Zend_Registry::get("target_path") . "/uploads/orders/signatures/" . $token . ".png";
        if (new Atlas_Model_DrawMapper($filename, $data)) {
            echo "/uploads/orders/signatures/" . $token . ".png";
        } else {
            echo "FAILED";
        }
        die();
    }

    public function profileAction() {
        $this->view->title = "Modify Your Profile";

        // get user information
        $request = $this->getRequest();
        $user_id = Zend_Registry::get("user_id");

        // setup the mappers and forms
        $user_mapper = new Atlas_Model_UsersMapper();
        $profile_form = new Atlas_Form_Profile();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($user_id != $form_data['user_id']) {
                return $this->_redirect("/user/profile");
            } else if ($profile_form->isValid($form_data)) {
                try {
                    $user_id = $user_mapper->processProfileForm($form_data);

                    Utility_FlashMessenger::addMessage('<div class="success">Your profile has been updated</div>');
                    return $this->_redirect("/user/profile");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/user/profile");
                }
            } else {
                $message = Utility_Error::buildErrors($profile_form->getMessages());
                $this->view->messages = $message;
            }
        } else {
            // try to get the selected user data and
            // redirect to the profile view on error 
            try {
                $user = new Atlas_Model_Users();
                $user = $user_mapper->find($user_id);
                $this->view->user = $user;
                $profile_form->populate(Utility_Filter_DBSafe::revert($user->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/user/profile");
            }
        }

        // pass the data to the view
        $this->view->form = $profile_form;
    }

    public function userAction() {
        $this->view->title = "Modify/Create System User";

        // get user information
        $request = $this->getRequest();
        $user_id = (int) $request->getParam("id", 0);

        // set up the mappers and forms
        $user_mapper = new Atlas_Model_UsersMapper();
        $user_form = new Atlas_Form_Users();

        // process or initialize the form
        if ($request->isPost()) {
            Utility_Filter_DBSafe::clean($form_data = $request->getPost());

            if ($user_form->isValid($form_data)) {
                $result = $user_mapper->processUserForm($form_data);
                
                
                if ((int) $form_data['user_id'] <= 0) {
                    // send the user an email confirmation
                    $recipients[] = array(
                        "email" => $form_data['email'],
                        "name" => $form_data['name']
                    );
                    $email = new Utility_Emails_UserCreation(
                                    $recipients,
                                    $form_data['username'],
                                    $result['password'],
                                    $form_data['name']
                    );
                    $email->send();
                }

                Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                return $this->_redirect("/user/users");
            } else {
                $message = Utility_Error::buildErrors($user_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($user_id != 0) {
            // try to get the selected user data and
            // redirect to the user list on error 
            try {
                $user = new Atlas_Model_Users();
                $user = $user_mapper->find($user_id);
                $user_form->populate(Utility_Filter_DBSafe::revert($user->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/user/users");
            }
        }

        // pass the data to the view
        $this->view->form = $user_form;
    }

    public function usersAction() {
        $this->view->title = "System User List";
        // setup the request object
        $request = $this->getRequest();
        $user_mapper = new Atlas_Model_UsersMapper();
        $users = $user_mapper->buildUserFullList();
        $this->view->users = $users;
    }

    public function usergroupsAction() {
        $this->view->title = "User Group Assignments";

        // get the parameters
        $request = $this->getRequest();
        $user_id = (int) $request->getParam("id", 0);

        // ensure a valid permission group was selected
        if ($user_id <= 0) {
            return $this->_redirect("/user/users");
        }

        // setup required mappers
        $permission_group_mapper = new Atlas_Model_PermissionGroupsMapper();
        $user_mapper = new Atlas_Model_UsersMapper();
        $user_list = $user_mapper->buildActiveUsers();
        // ensure the id given was valid
        try {
            $user = $user_mapper->find($user_id);
        } catch (Exception $e) {
            return $this->_redirect("/user/users");
        }

        // pass the user arrays to the view for use in the control console
        $this->view->user = $user;
        $this->view->user_list = $user_list;
        $this->view->user_groups = $permission_group_mapper->buildUserGroupList($user_id);
        $this->view->permission_groups = $permission_group_mapper->buildGroupList();
    }

    public function accountkeysAction() {
        $this->view->title = "Account Keys";
        $mapper = new Atlas_Model_AccountsKeyMapper();
        $users = $mapper->buildAllSalesUsers();
        $mapper = new Atlas_Model_UsersMapper();
        $users = $mapper->expandUsers($users);
        $mapper = new Atlas_Model_CalUsersMapper();
        $users = $mapper->expandUsers($users);
        $this->view->users  =   $users;
    }

    public function accountkeyAction() {
        $this->view->title = "Modify/Create Account Key";

        $request = $this->getRequest();
        $user_id = (int) $request->getParam("id", 0);
        $form = new Atlas_Form_AccountKey();
        $this->view->form = $form;

        if ($request->isPost()) {
            try {
                $form_data = $request->getPost();
                $mapper = new Atlas_Model_AccountsKeyMapper();
                $mapper->processForm($form_data);
            } catch (Exception $e) {
                Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                return $this->_redirect("/user/accountkeys");
            }

            // clear the current UNITS cache
            $cache = Zend_Registry::get('cache_handler');
            $cache->remove("SALES_USERS");

            Utility_FlashMessenger::addMessage('<div class="success">The selected User was updated</div>');
            return $this->_redirect("/user/accountkeys");
        } else if ($user_id != 0) {
            $mapper = new Atlas_Model_CalUsersMapper();
            $user = $mapper->find($user_id);
            $mapper = new Atlas_Model_AccountsKeyMapper();
            $user = $mapper->buildDetailByJCUsername($user->getEmail());
            $this->view->form->populate($user);
        }
    }

    public function useractivateAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $user_id = (int) $request->getParam("id", 0);

        // ensure the proper variables are present
        if ($user_id <= 0) {
            return $this->_redirect("/user/users");
        }

        // attempt to activate the user's account
        $mapper = new Atlas_Model_UsersMapper();
        $mapper->activateUser($user_id);
        die();
    }

    public function userdeactivateAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $user_id = (int) $request->getParam("id", 0);

        // ensure the proper variables are present
        if ($user_id <= 0) {
            return $this->_redirect("/user/users");
        }

        // attempt to deactivate the user's account
        $mapper = new Atlas_Model_UsersMapper();
        $mapper->deactivateUser($user_id);
        die();
    }

    public function userresetAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $user_id = (int) $request->getParam("id", 0);

        // ensure the proper variables are present
        if ($user_id <= 0) {
            return $this->_redirect("/user/users");
        }

        // attempt to reset the user's password
        $mapper = new Atlas_Model_UsersMapper();
        $user = $mapper->find($user_id);
        $password = $mapper->resetUserPassword($user_id);

        // send the user an email confirmation
        $recipients[] = array(
            "email" => $user->getEmail(),
            "name" => $user->getName()
        );

        $email = new Utility_Emails_UserResetPassword(
                        $recipients,
                        $user->getUsername(),
                        $password,
                        $user->getName()
        );
        $send = $email->send();
        echo $send['message'];
        die();
    }

    public function usercopyAction() {
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        $request = $this->getRequest();
        $form_data = Utility_Filter_DBSafe::clean($request->getPost());
        if ($request->isPost()) {
            $user_group_mapper = new Atlas_Model_PermissionGroupUsersMapper();
            $user_group = new Atlas_Model_PermissionGroupUsers();
            $user_group_mapper->removeUserPermissions($form_data['user_to']);
            $groups = $user_group_mapper->buildUserGroups($form_data['user_from']);

            foreach ($groups as $group) {
                $user_group->setPermission_group_id($group['group_id'])
                        ->setUser_id($form_data['user_to']);
                $user_group_mapper->save($user_group);
            }

            Utility_FlashMessenger::addMessage('<div class="success">Your request has been successfully processed.</div>');
            return $this->_redirect("/user/usergroups/id/" . $form_data['user_to']);
        }
        die();
    }

    public function ticketsAction()
    {
        $this->view->title = "Tickets";
        $tickets = new Atlas_Model_TicketMapper();
        $this->view->reocrds = $tickets->buildAllTickets();
    }

    public function ticketAction()
    {
        $this->view->title = "Modify/Create Ticket";
        $request    =   $this->getRequest();
        $id         =   $request->getParam('id',null);
        $form       =   new Atlas_Form_Tcket();        
        $ticketMapper  = new Atlas_Model_TicketMapper();
        if ($id != null) {
            $ticket = $ticketMapper->find($id); 
            $form->populate($ticket->toArray());
        }
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                $form_data      =   Utility_Filter_DBSafe::clean($request->getPost());
                $form_data['requestedDate'] =   date('Y-m-d',strtotime($form_data['requestedDate']));
                $form_data['updatedDate']   =   date('Y-m-d H:i:s');
                $form_data['userId']        =   Zend_Registry::get('name');
                if ($id == null) {
                    $form_data['createdDate']   =   date('Y-m-d H:i:s');;
                }
                $ticket     =   new Atlas_Model_Ticket($form_data);
                $mapper     =   new Atlas_Model_TicketMapper();
                $mapper->save($ticket);
                Utility_FlashMessenger::addMessage('<div class="success">Ticket has been updated</div>');
                return $this->_redirect("/user/tickets");
            }else{
                $message = Utility_Error::buildErrors($form->getMessages());
                $this->view->messages = $message;
            }
        }
        $this->view->form = $form;
    }

    public function inventoriesAction()
    {
        $this->view->title = "Inventory";
        $items = new Atlas_Model_InventoryMapper();
        $this->view->entries = $items->buildAllInventoryItems();
    }

    public function inventoryAction()
    {
        $this->view->title = "Modify/Create Inventory Item";
        $request       = $this->getRequest();        
        $id            = $request->getParam('id',null);
        $form          = new Atlas_Form_Inventory();
        $ticketMapper  = new Atlas_Model_InventoryMapper;
        if ($id != null) {
            $item = $ticketMapper->find($id);         
            $form->populate($item->toArray());
        }
        if ($this->getRequest()->isPost()) {              
            if ($form->isValid($request->getPost())) {
                $form_data                  =   Utility_Filter_DBSafe::clean($request->getPost());
                $form_data['support_date']  =   date('Y-m-d',strtotime($form_data['support_date']));
                $inventory  = new Atlas_Model_Inventory($form_data);
                $mapper  = new Atlas_Model_InventoryMapper();
                $mapper->save($inventory);
                Utility_FlashMessenger::addMessage('<div class="success">Ticket has been updated</div>');
                return $this->_redirect("/user/inventories");
            }else{
                $message = Utility_Error::buildErrors($form->getMessages());
                $this->view->messages = $message;
            }
        } 
        $this->view->form = $form;
    }
    
    public function exppasschangeAction() {
        $this->view->title = "Expired password change";
        $request       = $this->getRequest();
        $fp_mapper = New Atlas_Model_ForgotPasswordMapper();
        $key = $request->getParam("key");
        $id = $fp_mapper->checkKey($key, Zend_Registry::get("ip_add"), Zend_Registry::get("http_agent"));  
        
        if (!$id) {
            Utility_FlashMessenger::addMessage('<div class="error">Invalid Key Used.</div>');
            $this->_redirect("/adminbnylogin");
        }
        $user_mapper = new Atlas_Model_UsersMapper();
        $fp_mapper = new Atlas_Model_ForgotPasswordMapper();
        // setup the change password form
        $form = new Atlas_Form_Exppasschange();
        $this->view->form = $form;
        if ($request->isPost()) {
            $form_data      = Utility_Filter_DBSafe::clean($request->getPost());
            $form_data['id']= $id;
            if ($form->isValid($form_data)) {
                try {
                    $user_id = $user_mapper->processExppassForm($form_data);
                    Utility_FlashMessenger::addMessage('<div class="success">Your profile has been updated</div>');
                    return $this->_redirect("/adminbnylogin");
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    return $this->_redirect("/exppasschange/key/".$key);
                }
            } else {
                $message = Utility_Error::buildErrors($form->getMessages());
                $this->view->messages = $message;
            }
        }
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