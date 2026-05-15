<?php

class Atlas_Form_Prods extends Zend_Form {

    public function init() {
        $this->setDisableLoadDefaultDecorators(true);

        // ** PROD_ID TEXT FIELD ******************************/
        $prod_id = new Zend_Form_Element_Hidden("prod_id");
        $prod_id->setDecorators(array('ViewHelper'));
        $this->addElement($prod_id);

        // ** PROD_NAME TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("prod_name");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 6))
                ))
                ->setAttribs(array(
                    "maxlength" => "6",
                    "class" => "form-element",
                    "size" => "5"
        ));
        $this->addElement($field);

        // ** PROD_DESC TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("prod_desc");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setAttribs(array(
                    "maxlength" => "255",
                    "class" => "form-element",
                    "size" => "60"
        ));
        $this->addElement($field);

        // ** PROD_NO TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("prod_no");
        $field->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setAttribs(array(
                    "maxlength" => "50",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** PROD_CODE TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("prod_code");
        $field->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setAttribs(array(
                    "maxlength" => "30",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** PROD_COST TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("prod_cost");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 50))
                ))
                ->setAttribs(array(
                    "maxlength" => "10",
                    "class" => "form-element",
                    "size" => "5"
        ));
        $this->addElement($field);

        // ** PROD_SALE TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("prod_sale");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1, 50))
                ))
                ->setAttribs(array(
                    "maxlength" => "10",
                    "class" => "form-element",
                    "size" => "5"
        ));
        $this->addElement($field);

        $field = new Zend_Form_Element_Select("prod_status");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setAttribs(array('class' => 'form-element'))
                ->addMultiOption(1, 'YES')
                ->addMultiOption(0, 'No');
        $this->addElement($field);

        /* FILE FIELD FOR PRODUCT IMAGE ************** */
        $product_image = new Zend_Form_Element_File("prod_image");
        $product_image->setRequired(false)
                ->setDecorators(array('File'))
                ->addValidator('Count', false, 1)
                ->addValidator('Size', false, array('max' => '2MB'))
                ->addValidator('Extension', false, array("png", "jpg", "jpeg", "gif"))
                ->setAttribs(array('class' => 'form-element'));
        $this->addElement($product_image);

        /* FILE FIELD FOR PRODUCT IMAGE ************** */
        $field = new Zend_Form_Element_Select("prod_display");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setAttribs(array('class' => 'form-element'))
                ->addMultiOption(1, 'YES')
                ->addMultiOption(0, 'No');
        $this->addElement($field);
        
        /* FILE FIELD FOR PRODUCT IMAGE ************** */
        $mapper = new Atlas_Model_ProdsCategoriesMapper();
        $categories = $mapper->buildCategoryTree(0,'list');
        $field = new Zend_Form_Element_Select("cat_id");
        $field->setRequired(true)->setDecorators(array('ViewHelper'))->setAttribs(array('class' => 'form-element'));
        $field->addMultiOption(0,' -- ');
        foreach($categories as $list){
            $dd_data = explode('##',$list);
            $field->addMultiOption($dd_data[0],$dd_data[1]);
        }                
        $this->addElement($field);
        
        // ** SUBMIT BUTTON *************************************/
        $submit = new Zend_Form_Element_Submit("submit", "Save");
        $submit->setAttrib("class", "submit");
        $submit->setDecorators(array("ViewHelper"));
        $this->addElement($submit);
    }
}

?>