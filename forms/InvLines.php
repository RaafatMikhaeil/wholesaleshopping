<?php

class Atlas_Form_InvLines extends Zend_Form
{

    public function init()
    {
        $this->setDisableLoadDefaultDecorators(true);
				
		// ** INV_LINE_ID TEXT FIELD ******************************/
    	$field = new Zend_Form_Element_Text("inv_line_id");
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
				
		// ** INV_ID TEXT FIELD ******************************/
    	$field = new Zend_Form_Element_Text("inv_id");
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
				
		// ** PRODUCT_ID TEXT FIELD ******************************/
    	$field = new Zend_Form_Element_Text("product_id");
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
				
		// ** PRODUCT_PRICE TEXT FIELD ******************************/
    	$field = new Zend_Form_Element_Text("product_price");
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
				
		// ** PRODUCT_QTY TEXT FIELD ******************************/
    	$field = new Zend_Form_Element_Text("product_qty");
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
		
		// ** SUBMIT BUTTON *************************************/
    	$submit = new Zend_Form_Element_Submit("submit", "Save");
	    $submit->setAttrib("class", "submit");
	    $submit->setDecorators(array("ViewHelper"));
		$this->addElement($submit);
    }

}

?>