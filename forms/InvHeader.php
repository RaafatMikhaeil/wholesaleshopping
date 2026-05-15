<?php

class Atlas_Form_InvHeader extends Zend_Form
{

    public function init()
    {
        $this->setDisableLoadDefaultDecorators(true);
				
		// ** HIDDEN FIELD FOR INV_ID ***********************/
		$field = new Zend_Form_Element_Hidden("inv_id");
		$field->setDecorators(array("ViewHelper"));
		$this->addElement($field);
				
		// ** INV_CUST_ID TEXT FIELD ******************************/
    	$field = new Zend_Form_Element_Text("inv_cust_id");
    	$field->setRequired(true)
    		->setDecorators(array("ViewHelper"))
			->setFilters(array("StringTrim", "StripTags"))
    		->setValidators(array(
    			array("NotEmpty",true),
    			array("StringLength", false, array(1, 50))
    		))
    		->setAttribs(array(
	    		"maxlength" => "50",
	    		"class"     => "form-element",
	    		"size"      => "30"
    		));
		$this->addElement($field);
				
		// ** INV_DATE TEXT FIELD ******************************/
    	$field = new Zend_Form_Element_Text("inv_date");
    	$field->setRequired(true)
    		->setDecorators(array("ViewHelper"))
			->setFilters(array("StringTrim", "StripTags"))
    		->setValidators(array(
    			array("NotEmpty",true),
    			array("StringLength", false, array(1, 50))
    		))
    		->setAttribs(array(
	    		"maxlength" => "50",
	    		"class"     => "form-element",
	    		"size"      => "30"
    		));
		$this->addElement($field);
				
		// ** INV_TOTAL TEXT FIELD ******************************/
    	$field = new Zend_Form_Element_Text("inv_total");
    	$field->setRequired(true)
    		->setDecorators(array("ViewHelper"))
			->setFilters(array("StringTrim", "StripTags"))
    		->setValidators(array(
    			array("NotEmpty",true),
    			array("StringLength", false, array(1, 50))
    		))
    		->setAttribs(array(
	    		"maxlength" => "50",
	    		"class"     => "form-element",
	    		"size"      => "30"
    		));
		$this->addElement($field);
				
		// ** INV_USER_ID TEXT FIELD ******************************/
    	$field = new Zend_Form_Element_Text("inv_user_id");
    	$field->setRequired(true)
    		->setDecorators(array("ViewHelper"))
			->setFilters(array("StringTrim", "StripTags"))
    		->setValidators(array(
    			array("NotEmpty",true),
    			array("StringLength", false, array(1, 50))
    		))
    		->setAttribs(array(
	    		"maxlength" => "50",
	    		"class"     => "form-element",
	    		"size"      => "30"
    		));
		$this->addElement($field);
				
		// ** INV_NOTES TEXT AREA ************************************/
		$field = new Zend_Form_Element_TextArea("inv_notes");
    	$field->setRequired(true)
    		->setDecorators(array("ViewHelper"))
			->setFilters(array("StringTrim"))
    		->setValidators(array(
    			array("NotEmpty",true),
    			array("StringLength", false, array(1, 1000))
    		))
    		->setAttribs(array(
				"cols" => "45",
				"rows" => "2",
			));
		$this->addElement($field);
		
		// ** SUBMIT BUTTON *************************************/
    	$submit = new Zend_Form_Element_Submit("submit", "Save");
	    $submit->setAttrib("class", "submit");
	    $submit->setDecorators(array("ViewHelper"));
		$this->addElement($submit);
    }

}

?>