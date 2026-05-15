<?php

class Atlas_Form_ProdsCategories extends Zend_Form {
	protected $_cat_id;
	
	public function __construct($cat_id = 0)
	{
		$this->_cat_id = $cat_id;
		parent::__construct();
	}
	
    public function init() {
        $this->setDisableLoadDefaultDecorators(true);

        // ** PROD_ID TEXT FIELD ******************************/
        $cat_id = new Zend_Form_Element_Hidden("cat_id");
        $cat_id->setDecorators(array('ViewHelper'));
        $this->addElement($cat_id);

        // ** PROD_NAME TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("cat_name");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setValidators(array(
                    array("NotEmpty", true),
                    array("StringLength", false, array(1,100))
                ))
                ->setAttribs(array(
                    "maxlength" => "100",
                    "class" => "form-element",
                    "size" => "30"
        ));
        $this->addElement($field);

        // ** PROD_DESC TEXT FIELD ******************************/
        $field = new Zend_Form_Element_Text("cat_desc");
        $field->setRequired(true)
                ->setDecorators(array("ViewHelper"))
                ->setFilters(array("StringTrim", "StripTags"))
                ->setAttribs(array(
                    "maxlength" => "255",
                    "class" => "form-element",
                    "size" => "70"
        ));
        $this->addElement($field);

        /* FILE FIELD FOR PRODUCT IMAGE ************** */
        $product_image = new Zend_Form_Element_File("cat_image");
        $product_image->setRequired(false)
                ->setDecorators(array('File'))
                ->addValidator('Count', false, 1)
                ->addValidator('Size', false, array('max' => '2MB'))
                ->addValidator('Extension', false, array("png", "jpg", "jpeg", "gif"))
                ->setAttribs(array('class' => 'form-element'));
        $this->addElement($product_image);

        /* FILE FIELD FOR PRODUCT IMAGE ************** */
        $field = new Zend_Form_Element_Select("cat_status");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setAttribs(array('class' => 'form-element'))
                ->addMultiOption(1, 'YES')
                ->addMultiOption(0, 'No');
        $this->addElement($field);


        /* FILE FIELD FOR PRODUCT IMAGE ************** */
        $field = new Zend_Form_Element_Select("cat_display");
        $field->setRequired(true)
                ->setDecorators(array('ViewHelper'))
                ->setAttribs(array('class' => 'form-element'))
                ->addMultiOption(1, 'YES')
                ->addMultiOption(0, 'No');
        $this->addElement($field);
        

        /* FILE FIELD FOR PRODUCT IMAGE ************** */
        $mapper = new Atlas_Model_ProdsCategoriesMapper();
        $categories = $mapper->buildCategoryTree($this->_cat_id,'list');
        $field = new Zend_Form_Element_Select("cat_parent");
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