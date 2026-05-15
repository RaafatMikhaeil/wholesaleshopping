<?php

class CurlController extends Zend_Controller_Action {

    public function init() {
        // nothing needed as this will simply process requests
        $this->_helper->_layout->disableLayout();
        $this->getResponse()->clearBody();
    }

    public function clearAction() {
        try {
            if (APPLICATION_ENV == "production") {
                // make sure only connections from the Atlas server are allowed
                if ($_SERVER['REMOTE_ADDR'] != "74.62.220.98" &&
                        $_SERVER['REMOTE_ADDR'] != "74.62.220.99" &&
                        $_SERVER['REMOTE_ADDR'] != "74.62.220.102" &&
                        $_SERVER['REMOTE_ADDR'] != "74.62.220.105" &&
                        $_SERVER['REMOTE_ADDR'] != "74.62.220.107" &&
                        $_SERVER['REMOTE_ADDR'] != "66.85.108.106" &&
                        $_SERVER['REMOTE_ADDR'] != "47.176.131.107" &&
                        $_SERVER['REMOTE_ADDR'] != "47.148.82.124") {
                    echo "FAILURE: [0] " . $_SERVER['REMOTE_ADDR'];
                    die();
                }
            }

            $request = $this->getRequest();
            $password = $request->getParam("pass", "");
            $action = $request->getParam("type", "");

            // make sure the timestamp is within 10 minutes of the current time
            list($timestamp, $key) = explode(".", $password);
            if (md5($timestamp . "_09q87543_SALTY_9q8er-") != $key &&
                    (time() - $timestamp) / 60 > 10) {
                echo "FAILURE: [1]";
                die();
            }

            // retreive the cache handler from the registry
            $cache = Zend_Registry::get('cache_handler');

            switch ($action) {
                case "beltway":
                    $cache->remove("JF_BELTWAY");
                    break;
                case "scrollingbanner":
                    $cache->remove("JF_SCROLLINGBANNER");
                    break;
                case "staff":
                    $mapper = new Jarrow_Model_StaffMapper();
                    $ids = $mapper->buildStaffIds();
                    $cache->remove("JF_STAFF");
                    foreach ($ids as $id) {
                        $cache->remove("JF_STAFF_" . $id['autoid']);
                    }
                    break;
                case "jobs":
                    $cache->remove("JF_JOBS");
                    break;
                case "sciencepanel":
                    $cache->remove("JF_SCIENCEPANEL");
                    break;
                case "pressreleases":
                    $cache->remove("JF_MINI_PRESSRELEASES");
                    $cache->remove("JF_PRESSRELEASES");
                    break;
                case "industrynews":
                    $cache->remove("JF_MINI_INDUSTRYNEWS");
                    $cache->remove("JF_INDUSTRYNEWS");
                    break;
                case "sciencenews":
                    $cache->remove("JF_MINI_SCIENCENEWS");
                    $cache->remove("JF_SCIENCENEWS");
                    break;
                case "product":
                    $cache->remove("JF_PRODUCT_LISTINGS");
                    $cache->remove("JF_PRODUCT_LIST");
                    $cache->remove("JF_PRODUCT_DICT");
                    break;
                case "category":
                    $cache->remove("JF_C_PRODUCT");
                    $cache->remove("JF_C_FUNCTION");
                    break;
                case "awards":
                    $cache->remove("JF_LIST_AWARDS");
                    $cache->remove("JF_IMAGE_AWARDS");
                    break;
                case "onlineretailers":
                    $cache->remove("JF_ONLINERETAILERS");
                    break;
                case "retailers":
                    $cache->remove("JF_RETAILERS");
                    break;
                case "intretailers":
                    $cache->remove("JF_INTRETAILERS");
                    break;
                case "ticker":
                    $cache->remove("JF_TICKER");
                    break;
                default:
                    echo "invalid case provided: " . $action;
                    die();
            }

            echo "SUCCESS";
            die();
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
        }
    }

    public function uploadAction() {
        try {
            if (APPLICATION_ENV == "production") {
                // make sure only connections from the Atlas server are allowed
                if ($_SERVER['REMOTE_ADDR'] != "74.62.220.98" &&
                        $_SERVER['REMOTE_ADDR'] != "74.62.220.99" &&
                        $_SERVER['REMOTE_ADDR'] != "74.62.220.102" &&
                        $_SERVER['REMOTE_ADDR'] != "74.62.220.105" &&
                        $_SERVER['REMOTE_ADDR'] != "74.62.220.107" &&
                        $_SERVER['REMOTE_ADDR'] != "66.85.108.106" &&
                        $_SERVER['REMOTE_ADDR'] != "47.176.131.107" &&
                        $_SERVER['REMOTE_ADDR'] != "47.28.120.182" &&
                        $_SERVER['REMOTE_ADDR'] != "47.148.82.124") {
                    echo "FAILURE: [0] " . $_SERVER['REMOTE_ADDR'];
                    die();
                }
            }

            $request = $this->getRequest();
            $password = $request->getParam("pass", "");
            $action = $request->getParam("type", "");
            $filename = $request->getParam("filename", "");

            // make sure the timestamp is within 10 minutes of the current time
            list($timestamp, $key) = explode(".", $password);
            if (md5($timestamp . "_09q87543_SALTY_9q8er-") != $key &&
                    (time() - $timestamp) / 60 > 30) {
                echo "FAILURE: [1]";
                die();
            }

            // make sure the file is uploaded
            if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
                echo "FAILURE: [2]";
                die();
            }

            switch ($action) {
                case "beltway":
                    $target_path = "/var/www/html/vendor/public/uploads/beltway/";
                    break;
                case "scrollingbanner":
                    $target_path = "/var/www/html/vendor/public/uploads/promo/";
                    break;
                case "staff":
                    $target_path = "/var/www/html/vendor/public/uploads/staff/";
                    break;
                case "jobs":
                    $target_path = "/var/www/html/vendor/public/uploads/jobs/";
                    break;
                case "sciencepanel":
                    $target_path = "/var/www/html/vendor/public/uploads/sciencepanel/";
                    break;
                case "industrynews":
                    $target_path = "/var/www/html/vendor/public/uploads/industrynews/";
                    break;
                case "sciencenews":
                    $target_path = "/var/www/html/vendor/public/uploads/sciencenews/";
                    break;
                case "product":
                    $target_path = "/var/www/html/vendor/public/productImg2/";
                    break;
                case "suppfacts":
                    $target_path = "/var/www/html/vendor/public/supplimentImg/";
                    break;
                case "category":
                    $target_path = "/var/www/html/vendor/public/categoryImg/";
                    break;
                case "awards":
                    $target_path = "/var/www/html/vendor/public/uploads/awards/";
                    break;
                case "onlineretailers":
                    $target_path = "/var/www/html/vendor/public/uploads/onlineretailers/";
                    break;
                case "pressreleases":
                    $target_path = "/var/www/html/vendor/public/uploads/pressreleases/";
                    break;
                case "sales-reports":
                    $target_path = "/var/www/html/vendor/public/calendar/salesReports/";
                    break;
                case "labeling":
                    $target_path = "/var/www/html/vendor/public/uploads/orders/labeling/";
                    break;
                case "productassets":
                    $target_path = "/var/www/html/vendor/public/images/";
                    break;
                case "poapproval":
                    $target_path = "/var/www/html/vendor/public/uploads/orders/poapproval/";
                    break;
                case "categorizations":
                    $target_path = "/var/www/html/vendor/public/uploads/orders/categorizations/";
                    break;
                case "complaints":
                    $target_path = "/var/www/html/vendor/public/uploads/orders/complaints/";
                    break;
                case "lot":
                    $target_path = "/var/www/html/vendor/public/uploads/orders/pdf/lot/";
                    break;
                case "material":
                    $target_path = "/var/www/html/vendor/public/uploads/orders/pdf/material/";
                    break;
                case "reports":
                    $target_path = "/var/www/html/vendor/public/uploads/orders/pdf/reports/";
                    break;
                case "tests":
                    $target_path = "/var/www/html/vendor/public/uploads/orders/pdf/tests/";
                    break;
                case "clinicalstudies":
                    $target_path = "/var/www/html/vendor/public/uploads/orders/clinicalstudies/";
                    break;
                case "arms":
                    $target_path = "/var/www/html/vendor/public/uploads/orders/arms/";
                    break;
                case "formulas":
                    $target_path = "/var/www/html/vendor/public/uploads/orders/formulas/";
                    break;
                case "pdfimages":
                    $target_path = "/var/www/html/vendor/public/uploads/orders/pdfimages/";
                    break;
                case "jfresource":
                    $target_path = "/var/www/html/vendor/public/uploads/jf_files/";
                    break;
                case "jfresource_misc":
                    $target_path = "/var/www/html/vendor/public/uploads/jf_files/misc/";
                    break;
                case "jfresource_article":
                    $target_path = "/var/www/html/vendor/public/uploads/jf_files/science_articles/";
                    break;
                case "jfresource_media":
                    $target_path = "/var/www/html/vendor/public/uploads/jf_files/media_files/";
                    break;
                case "jfresource_guide":
                    $target_path = "/var/www/html/vendor/public/uploads/jf_files/product_reference_guides/";
                    break;
                case "jfresource_ads":
                    $target_path = "/var/www/html/vendor/public/uploads/jf_files/adscreative/";
                    break;
                case "jfresource_datasheet":
                    $target_path = "/var/www/html/vendor/public/uploads/jf_files/product_data_sheets/";
                    break;
                case "product_extra":
                    $target_path = "/var/www/html/vendor/public/marketing_images/";
                    break;
                default:
                    echo "invalid case provided: " . $action;
                    die();
            }

            $file_type = $_FILES['file']['type'];
            $file_ext = substr($_FILES['file']['name'], -3, 3);
            $file_tmp_name = $_FILES['file']['tmp_name'];
            $file_error = $_FILES['file']['error'];
            $file_size = $_FILES['file']['size'];

            if (move_uploaded_file($file_tmp_name, $target_path . $filename)) {
                echo "SUCCESS";
                die();
            } else {
                echo "FAILURE: [3]";
                die();
            }
        } catch (Exception $e) {
            echo $e->getMessage;
            die();
        }
    }

    public function deleteAction() {
        if (APPLICATION_ENV == "production") {
            // make sure only connections from the Atlas server are allowed
            if ($_SERVER['REMOTE_ADDR'] != "74.62.220.98" &&
                    $_SERVER['REMOTE_ADDR'] != "74.62.220.99" &&
                    $_SERVER['REMOTE_ADDR'] != "74.62.220.102" &&
                    $_SERVER['REMOTE_ADDR'] != "74.62.220.105" &&
                    $_SERVER['REMOTE_ADDR'] != "74.62.220.107" &&
                    $_SERVER['REMOTE_ADDR'] != "47.176.131.107" &&
                    $_SERVER['REMOTE_ADDR'] != "66.85.108.106" &&
                    $_SERVER['REMOTE_ADDR'] != "47.28.120.182" &&
                    $_SERVER['REMOTE_ADDR'] != "47.148.82.124") {
                echo "FAILURE: [0] " . $_SERVER['REMOTE_ADDR'];
                die();
            }
        }

        $request = $this->getRequest();
        $password = $request->getParam("pass", "");
        $action = $request->getParam("type", "");
        $filename = $request->getParam("filename", "");

        // make sure the timestamp is within 10 minutes of the current time
        list($timestamp, $key) = explode(".", $password);
        if (md5($timestamp . "_09q87543_SALTY_9q8er-") != $key &&
                (time() - $timestamp) / 60 > 30) {
            echo "FAILURE: [1]";
            die();
        }

        switch ($action) {
            case "beltway":
                $target_path = "/var/www/html/vendor/public/uploads/beltway/";
                break;
            case "scrollingbanner":
                $target_path = "/var/www/html/vendor/public/uploads/promo/";
                break;
            case "staff":
                $target_path = "/var/www/html/vendor/public/uploads/staff/";
                break;
            case "jobs":
                $target_path = "/var/www/html/vendor/public/uploads/jobs/";
                break;
            case "sciencepanel":
                $target_path = "/var/www/html/vendor/public/uploads/sciencepanel/";
                break;
            case "industrynews":
                $target_path = "/var/www/html/vendor/public/uploads/industrynews/";
                break;
            case "sciencenews":
                $target_path = "/var/www/html/vendor/public/uploads/sciencenews/";
                break;
            case "product":
                $target_path = "/var/www/html/vendor/public/productImg2/";
                break;
            case "suppfacts":
                $target_path = "/var/www/html/vendor/public/supplimentImg/";
                break;
            case "category":
                $target_path = "/var/www/html/vendor/public/categoryImg/";
                break;
            case "awards":
                $target_path = "/var/www/html/vendor/public/uploads/awards/";
                break;
            case "onlineretailers":
                $target_path = "/var/www/html/vendor/public/uploads/onlineretailers/";
                break;
            case "pressreleases":
                $target_path = "/var/www/html/vendor/public/uploads/pressreleases/";
                break;
            case "sales-reports":
                $target_path = "/var/www/html/vendor/public/calendar/salesReports/";
                break;
            case "labeling":
                $target_path = "/var/www/html/vendor/public/uploads/orders/labeling/";
                break;
            case "productassets":
                $target_path = "/var/www/html/vendor/public/uploads/orders/productassets/";
                break;
            case "poapproval":
                $target_path = "/var/www/html/vendor/public/uploads/orders/poapproval/";
                break;
            case "categorizations":
                $target_path = "/var/www/html/vendor/public/uploads/orders/categorizations/";
                break;
            case "complaints":
                $target_path = "/var/www/html/vendor/public/uploads/orders/complaints/";
                break;
            case "lot":
                $target_path = "/var/www/html/vendor/public/uploads/orders/pdf/lot/";
                break;
            case "material":
                $target_path = "/var/www/html/vendor/public/uploads/orders/pdf/material/";
                break;
            case "reports":
                $target_path = "/var/www/html/vendor/public/uploads/orders/pdf/reports/";
                break;
            case "tests":
                $target_path = "/var/www/html/vendor/public/uploads/orders/pdf/tests/";
                break;
            case "clinicalstudies":
                $target_path = "/var/www/html/vendor/public/uploads/orders/clinicalstudies/";
                break;
            case "arms":
                $target_path = "/var/www/html/vendor/public/uploads/orders/arms/";
                break;
            case "formulas":
                $target_path = "/var/www/html/vendor/public/uploads/orders/formulas/";
                break;
            case "jfresource":
                $target_path = "/var/www/html/vendor/public/uploads/jf_files/";
                break;
            case "jfresource_misc":
                $target_path = "/var/www/html/vendor/public/uploads/jf_files/misc/";
                break;
            case "jfresource_article":
                $target_path = "/var/www/html/vendor/public/uploads/jf_files/science_articles/";
                break;
            case "jfresource_media":
                $target_path = "/var/www/html/vendor/public/uploads/jf_files/media_files/";
                break;
            case "jfresource_guide":
                $target_path = "/var/www/html/vendor/public/uploads/jf_files/product_reference_guides/";
                break;
            case "jfresource_ads":
                $target_path = "/var/www/html/vendor/public/uploads/jf_files/adscreative/";
                break;
            case "jfresource_datasheet":
                $target_path = "/var/www/html/vendor/public/uploads/jf_files/product_data_sheets/";
                break;
            case "product_extra":
                $target_path = "/var/www/html/vendor/public/marketing_images/";
                break;
        }

        // delete file if exists
        if (file_exists($target_path . $filename)) {
            unlink($target_path . $filename);
        }

        echo "SUCCESS";
        die();
    }

    public function __call($methodName, $args) {
        return $this->_redirect('/index');
    }

}

?>