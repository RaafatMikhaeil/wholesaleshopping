<?php
class PageController extends Zend_Controller_Action {
    public function init() {
        $this->view->css_docs = array(
            Zend_Registry::get("global_css"),
            "/css/page.css",
            "/css/smoothness/jquery-ui-1.8.17.custom.css",
            "/css/smoothness/jalerts.css",
            "/css/jquery-ui.css"
        );
        // set the JS documents for the website
        $this->view->js_docs = array(
            "/js/jquery.1.6.2.js",
            "/js/jquery.jBreadCrumb.1.1.js",
            "/js/jquery.easing.1.3.js",
            "/js/jquery.dataTables.min.js",
            "/js/global.js",
            "/js/jalerts.js",
            "/js/page.js"
        );

        // set the default layout
        $this->_helper->layout->setLayout('layout');

        // check if user is logged in and if they can access the current page
        $uri = $this->getRequest()->getRequestUri();
        $session = Utility_Session::getInstance(null, Zend_Registry::get("session_length"), 'R', Zend_Registry::get("server_domain"), Zend_Registry::get("cur_server"));
        if (!Utility_Session::isSession()) { // MAKE SURE SESSION IS LIVE
            return $this->_redirect(Zend_Registry::get('full_url') . "/login");
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
            return $this->_redirect(Zend_Registry::get('full_url') . "/login");
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
            return $this->_redirect('/dashboard');
        }

        // pop all pending messages
        $this->view->messages = Utility_FlashMessenger::popMessage();
    }

    public function pagegroupsAction() {
        $this->view->title = "Page Groups List";
        // setup the mapper and get the page group information
        $page_group_mapper = new Atlas_Model_PageGroupsMapper();
        $page_groups = $page_group_mapper->buildPageGroups();

        // pass the data to the view
        $this->view->page_groups = $page_groups;
    }

    public function pagegroupAction() {
        $this->view->title = "Page Group Creation/Modification";

        // get the parameters
        $request = $this->getRequest();
        $page_group_id = (int) $request->getParam("id", 0);

        // setup the mapper and form
        $page_group_mapper = new Atlas_Model_PageGroupsMapper();
        $page_group_form = new Atlas_Form_PageGroups();

        $pagegroups = new Atlas_Model_PageGroupsMapper();
        $this->view->pagegroups = $pagegroups->buildParents();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            if ($page_group_form->isValid($form_data)) {
                try {
                    $page_group_mapper->processForm($form_data);
                } catch (Exception $e) {
                    Utility_FlashMessenger::addMessage('<div class="error">' . $e->getMessage() . '</div>');
                    if ($page_group_id > 0) {
                        return $this->_redirect("/page/pagegroup/id/" . $page_group_id);
                    } else {
                        return $this->_redirect("/page/pagegroup");
                    }
                }
                Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                return $this->_redirect("/page/pagegroups");
            } else {
                $this->view->messages = Utility_Error::buildErrors($page_group_form->getMessages());
            }
        } else if ($page_group_id != 0) {
            // try to get the selected page group data and
            // redirect to the page group list on error 
            try {
                $page_group = $page_group_mapper->find($page_group_id);
                $page_group_form->populate(Utility_Filter_DBSafe::revert($page_group->toArray()));
                $this->view->page_group = $page_group;
            } catch (Exception $e) {
                return $this->_redirect("/page/pagegroups");
            }
        }

        // pass the data to the view
        $this->view->form = $page_group_form;
    }

    public function pagesAction() {
        $this->view->title = "Pages List";
        // setup the mapper and get the page data
        $page_mapper = new Atlas_Model_PagesMapper();
        $pages = $page_mapper->buildPages();

        // pass the data to the view
        $this->view->pages = $pages;
    }

    public function pageAction() {
        $this->view->title = "Page Creation/Modification";

        // get the parameters
        $request = $this->getRequest();
        $page_id = (int) $request->getParam("id", 0);

        $page_mapper = new Atlas_Model_PagesMapper();
        $page_form = new Atlas_Form_Pages();

        $pagegroups = new Atlas_Model_PageGroupsMapper();
        $this->view->pagegroups = $pagegroups->buildParents();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());

            if ($page_form->isValid($form_data)) {
                $newpageId = $page_mapper->processForm($form_data);

                // attempt to give the group access to the page
                $mapper = new Atlas_Model_PagePrivilegesMapper();
                $mapper->addGroupToPage(1, $newpageId);

                Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                return $this->_redirect("/page/pages");
            } else {
                $message = Utility_Error::buildErrors($page_form->getMessages());
                $this->view->messages = $message;
            }
        } else if ($page_id != 0) {
            // attempt to locate the selected page and redirect
            // to the page list on error
            try {
                $page = new Atlas_Model_Pages();
                $page = $page_mapper->find($page_id);
                $page_form->populate(Utility_Filter_DBSafe::revert($page->toArray()));
                $this->view->page = $page;
            } catch (Exception $e) {
                $this->_redirect("/page/pages");
            }
        }

        // pass the data to the view
        $this->view->form = $page_form;
    }

    public function pageactivationAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request    =   $this->getRequest();
        $page_id    =   (int) $request->getParam("id", 0);
        $activate   =   (int) $request->getParam("activate", 0);

        // ensure the proper variables are present
        if ($page_id <= 0) {
            return $this->_redirect("/page/pages");
        }

        // attempt to activate the user's account
        $mapper = new Atlas_Model_PagesMapper();
        $mapper->activatePage($page_id,$activate);
        die();
    }
    
    public function usergroupsAction() {
        $this->view->title = "User Groups List";
        // setup the mapper and get the permission group data
        $permission_group_mapper = new Atlas_Model_PermissionGroupsMapper();
        $permission_groups = $permission_group_mapper->buildAllUsers();
        // pass the data to the view
        $this->view->permission_groups = $permission_groups;
    }

    public function usergroupAction() {
        $this->view->title = "User Group Creation/Modification";
        // get the parameters
        $request = $this->getRequest();
        $permission_group_id = (int) $request->getParam("id", 0);

        // setup the mapper and form
        $permission_group_mapper = new Atlas_Model_PermissionGroupsMapper();
        $permission_group_form = new Atlas_Form_PermissionGroups();

        // process or initialize the form
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            if ($permission_group_form->isValid($form_data)) {
                $permission_group_mapper->processForm($form_data);
                Utility_FlashMessenger::addMessage('<div class="success">Your submission was successfully processed</div>');
                return $this->_redirect("/page/usergroups");
            } else {
                $this->view->messages = Utility_Error::buildErrors($permission_group_form->getMessages());
            }
        } else if ($permission_group_id != 0) {
            // attempt to locate the selected user group and redirect to
            // user group list on error
            try {
                $permission_group = $permission_group_mapper->find($permission_group_id);
                $permission_group_form->populate(Utility_Filter_DBSafe::revert($permission_group->toArray()));
            } catch (Exception $e) {
                return $this->_redirect("/page/usergroups");
            }
        }
        // pass the data to the view
        $this->view->form = $permission_group_form;
    }

    public function usergroupusersAction() {
        $this->view->title = "User Group Assignments";

        // get the parameters
        $request = $this->getRequest();
        $permission_group_id = (int) $request->getParam("id", 0);

        // ensure a valid permission group was selected
        if ($permission_group_id <= 0) {
            return $this->_redirect("/page/usergroups");
        }

        // setup required mappers
        $permission_group_mapper = new Atlas_Model_PermissionGroupsMapper();
        $user_mapper = new Atlas_Model_UsersMapper();

        // ensure the id given was valid
        try {
            $permission_group = $permission_group_mapper->find($permission_group_id);
        } catch (Exception $e) {
            return $this->_redirect("/page/usergroups");
        }

        // pass the user arrays to the view for use in the control console
        $this->view->permission_group   =   $permission_group;
        $this->view->users              =   $user_mapper->buildUserList();
        $this->view->pg_users           =   $permission_group_mapper->buildUserList($permission_group_id);
    }

    public function usergrouppagesAction() {
        $this->view->title = "User Group Permissions";

        // get the parameters
        $request = $this->getRequest();
        $permission_group_id = (int) $request->getParam("id", 0);

        // ensure a valid permission group was selected
        if ($permission_group_id <= 0) {
            return $this->_redirect("/page/usergroups");
        }

        // setup the required mappers
        $permission_group_mapper = new Atlas_Model_PermissionGroupsMapper();
        $page_privilege_mapper = new Atlas_Model_PagePrivilegesMapper();
        $page_mapper = new Atlas_Model_PagesMapper();

        // ensure the id given was valid
        try {
            $permission_group = $permission_group_mapper->find($permission_group_id);
        } catch (Exception $e) {
            return $this->_redirect("/page/usergroups");
        }

        // pass the permission arrays to the view for use in the contol console
        $this->view->permission_group = $permission_group;
        $this->view->pages = $page_mapper->buildPageList();
        $this->view->pg_pages = $page_privilege_mapper->buildPageList($permission_group_id);
    }

    public function usergroupuserassignmentAction() {
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
        $mapper = new Atlas_Model_PermissionGroupUsersMapper();
        $mapper->addUserToGroup($user_id, $permission_group_id);

        echo "success";
        die();
    }

    public function usergroupuserremovalAction() {
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
        $mapper = new Atlas_Model_PermissionGroupUsersMapper();
        $mapper->removeUserFromGroup($user_id, $permission_group_id);

        echo "success";
        die();
    }

    public function usergrouppageassignmentAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $permission_group_id = (int) $request->getParam("group", 0);
        $page_id = (int) $request->getParam("page", 0);

        // ensure the proper variables are present
        if ($permission_group_id <= 0 || $page_id <= 0) {
            echo "failed";
            die();
        }

        // attempt to give the group access to the page
        $mapper = new Atlas_Model_PagePrivilegesMapper();
        $mapper->addGroupToPage($permission_group_id, $page_id);

        echo "success";
        die();
    }

    public function usergrouppageremovalAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();

        // get the parameters
        $request = $this->getRequest();
        $permission_group_id = (int) $request->getParam("group", 0);
        $page_id = (int) $request->getParam("page", 0);

        // ensure the proper variables are present
        if ($permission_group_id <= 0 || $page_id <= 0) {
            echo "failed";
            die();
        }

        // attempt to remove access to the page from the group
        $mapper = new Atlas_Model_PagePrivilegesMapper();
        $mapper->removeGroupFromPage($permission_group_id, $page_id);

        echo "success";
        die();
    }

    public function accessconrepAction() {
        $this->view->title = "Access Conrol Report";
        // get the parameters
        $pagemapper = New Atlas_Model_PagesMapper();
        $groupmapper = New Atlas_Model_PermissionGroupsMapper();
        $privilegemapper = New Atlas_Model_PagePrivilegesMapper();
        $usermapper = New Atlas_Model_UsersMapper();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            switch ($form_data['r_type']) {
                case 'pa':
                    $page_id = $form_data['page_id'];
                    $pageGroups = $privilegemapper->getPageGroups($page_id);
                    $this->view->pageGroups = $pageGroups;
                    break;
                case 'ga':
                    $page_group_id = $form_data['permission_group_id'];
                    $groupUsers = $groupmapper->fetch($groupmapper->getPermissionGroupUsers($page_group_id));
                    $pages = $privilegemapper->fetch($privilegemapper->getGroupPagePrivileges($page_group_id));
                    $this->view->pages = $pages;
                    $this->view->groupUsers = $groupUsers;
                    break;
                case 'ua':
                    $user_id = $form_data['user_id'];
                    $userGroups = $groupmapper->fetch($groupmapper->getUserPermissionGroups($user_id));
                    $userGroupPages = $privilegemapper->fetch($privilegemapper->getUserPagePrivileges($user_id));
                    $this->view->userGroups = $userGroups;
                    $this->view->userGroupPages = $userGroupPages;
                    break;
            }
            $this->view->entries = $form_data;
        }
        $list['pages'] = $pagemapper->fetch($pagemapper->getPages());
        $list['groups'] = $groupmapper->fetch($groupmapper->selectAll());
        $list['users'] = $usermapper->fetch($usermapper->getUserList());
        $this->view->list = $list;
    }

    public function addtofavAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        // get the parameters
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            $user_id  = Zend_Registry::get("user_id");
            
            $userfav = New Atlas_Model_Userfavorites();
            $ufmapper = New Atlas_Model_UserfavoritesMapper();
            $pmapper = New Atlas_Model_PagesMapper();
            
            $uri = $pmapper->filterPartialUri($form_data['uri']);
            $page = $pmapper->getFavPages($uri)->query()->fetch();
            $favs = $ufmapper->checkIfAdded($user_id, $page['page_id']);
            
            if (count($favs) == 0) {
                $userfav->setUser_id($user_id)
                        ->setPage_id($page['page_id']);
                if($ufmapper->save($userfav)){
                    $favorites = $ufmapper->builFavorites($user_id);
                    Zend_Registry::set("favorites", $favorites);
                    return $this->_helper->json($favorites);
                } else {
                    return $this->_helper->json(0);
                }
            }
        }
        die();
    }
    public function deletefavAction() {
        // disable layout as this action is not for viewing
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
        // get the parameters
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form_data = Utility_Filter_DBSafe::clean($request->getPost());
            $user_id  = Zend_Registry::get("user_id");
            $ufmapper = New Atlas_Model_UserfavoritesMapper();
            $ufmapper->remove($form_data['fav_id']);
            return $this->_helper->json(1);
        }else {
            return $this->_helper->json(0);
        }
        die();
    }

    public function newusergrouppagesAction() {
        $this->view->title = "User Group Permissions";

        // get the parameters
        $request = $this->getRequest();
        $permission_group_id = (int) $request->getParam("id", 0);

        // ensure a valid permission group was selected
        if ($permission_group_id <= 0) {
            return $this->_redirect("/page/usergroups");
        }

        // setup the required mappers
        $permission_group_mapper = new Atlas_Model_PermissionGroupsMapper();
        $page_privilege_mapper = new Atlas_Model_PagePrivilegesMapper();
        $page_group_mapper = new Atlas_Model_PageGroupsMapper();
        $page_mapper = new Atlas_Model_PagesMapper();

        // ensure the id given was valid
        try {
            $permission_group = $permission_group_mapper->find($permission_group_id);
        } catch (Exception $e) {
            return $this->_redirect("/page/usergroups");
        }

        // pass the permission arrays to the view for use in the contol console
        $this->view->permission_group = $permission_group;
        $parents = $page_group_mapper->buildParents();
        $pagelist = $page_mapper->getPages()->query()->fetchAll();
        $this->view->page_tree = $page_mapper->createPageListTree($pagelist, $parents);
        $this->view->pg_pages = $page_privilege_mapper->buildPageListByParent($permission_group_id);
    }

    public function sitemapAction() {
        $this->view->title = "Site Map";
        
        $page_group = new Atlas_Model_PageGroupsMapper();
        $this->view->pages = $page_group->getPageNewPaths();
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
        return $this->_redirect('/dashboard');
    }

}

?>