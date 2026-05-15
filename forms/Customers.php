<?php

class Atlas_Form_Customers extends Zend_Form {

    public function init() {
        $this->setDisableLoadDefaultDecorators(true);

        // ** CUST_ID TEXT FIELD ******************************/
        $cust_id = new Zend_Form_Element_Hidden("cust_id");
        $cust_id->setDecorators(array('ViewHelper'));
        $this->addElement($cust_id);

        // ** CUST_NAME TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("cust_name");
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
        $field = new Zend_Form_Element_Text("cust_address");
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

        // ** CUST_CITY TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("cust_city");
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

        // ** CUST_STATE TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("cust_state");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 50))
                ))
                ->setAttribs(array(
                    "maxlength" => "50",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** CUST_ZIP TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("cust_zip");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 50))
                ))
                ->setAttribs(array(
                    "maxlength" => "50",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** CUST_PHONE TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("cust_phone");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 50))
                ))
                ->setAttribs(array(
                    "maxlength" => "50",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** CUST_EMAIL TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("cust_email");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 50))
                ))
                ->setAttribs(array(
                    "maxlength" => "50",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** CUST_CONTACT TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("cust_contact");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 100))
                ))
                ->setAttribs(array(
                    "maxlength" => "100",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** CUST_LIC_NO TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("cust_lic_no");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 100))
                ))
                ->setAttribs(array(
                    "maxlength" => "100",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** CUST_NAME TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("cust_lic_exp");
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
        
        
        $field = new Zend_Form_Element_Select("cust_status");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setAttribs(array('class' => 'form-element'))
                ->addMultiOption(1, 'YES')
                ->addMultiOption(0, 'No');
        $this->addElement($field);
        
        // ** SUBMIT BUTTON *************************************/
        $submit = new Zend_Form_Element_Submit("submit", "Save");
        $submit->setAttrib("class", "submit");
        $submit->setDecorators(array("ViewHelper"));
        $this->addElement($submit);
    }

}

?>