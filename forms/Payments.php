<?php

class Atlas_Form_Payments extends Zend_Form {

    protected $_inv_id;

    public function __construct($inv_id = 0) {
        $this->_inv_id = $inv_id;
        parent::__construct();
    }

    public function init() {
        $this->setDisableLoadDefaultDecorators(true);

        // ** PAYMENT_ID TEXT FIELD ******************************/
        $cust_id = new Zend_Form_Element_Hidden("payment_id");
        $cust_id->setDecorators(array('ViewHelper'));
        $this->addElement($cust_id);

        // ** INV_ID TEXT FIELD ******************************/
        $invHeaderMapper = new Atlas_Model_InvHeaderMapper();
        $pending_invoices = $invHeaderMapper->buildPendingInvoices2($this->_inv_id);
        $field = new Zend_Form_Element_Select("inv_id");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setAttribs(array('class' => 'form-element'));
        if(!$this->_inv_id){
            $field->addMultiOption('', ' -SELECT INVOICE-');
        }
        foreach ($pending_invoices as $inv) {
            $field->addMultiOption($inv['inv_id'], htmlspecialchars_decode(html_entity_decode($inv['cust_name'])) . ' - InvNo ' . $inv['inv_id']);
        }
        $this->addElement($field);

        // ** INV_ID TEXT FIELD ******************************/
        $payment_methods_mapper = new Atlas_Model_InvPaymentTypesMapper();
        $moethods = $payment_methods_mapper->buildPaymentTypes();
        $field = new Zend_Form_Element_Select("payment_type_id");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setAttribs(array('class' => 'form-element'));
        $field->addMultiOption('', ' -SELECT Payment Method-');
        foreach ($moethods as $method) {
            $field->addMultiOption($method['payment_type_id'], $method['payment_type']);
        }
        $this->addElement($field);

        // ** CUST_NAME TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("payment_datetime");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setAttribs(array(
                    "maxlength" => "255",
                    "class" => "form-element date-element",
                    "size" => "30",
                    "autocomplete" => "off"
        ));
        $this->addElement($field);

        // ** CUST_NAME TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("payment_amount");
        $field->setRequired(false)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setAttribs(array(
                    "maxlength" => "255",
                    "class" => "form-element",
                    "size" => "30",
                    "step" => "any",
                    "type" => "number"
        ));
        $this->addElement($field);

        // ** CUST_NAME TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("payment_amount");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 255))
                ))
                ->setAttribs(array(
                    "maxlength" => "255",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** CUST_ADDRESS TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("payment_contact");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 255))
                ))
                ->setAttribs(array(
                    "maxlength" => "255",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** SUBMIT BUTTON *************************************/
        $submit = new Zend_Form_Element_Submit("submit", "Save");
        $submit->setAttrib("class", "submit");
        $submit->setDecorators(array("ViewHelper"));
        $this->addElement($submit);
        
        // ** SUBMIT BUTTON *************************************/
        $submit = new Zend_Form_Element_Submit("print", "Save & Print");
        $submit->setAttrib("class", "submit");
        $submit->setAttrib("style", "padding:10px; margin-left:20px;");
        $submit->setDecorators(array("ViewHelper"));
        $this->addElement($submit);
    }
}

?>