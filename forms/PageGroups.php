<?php

class Atlas_Form_PageGroups extends Zend_Form
{
    public function init()
    {
		/* FORM META DATA **************************/
    	$this->setDisableLoadDefaultDecorators(true);
    	$this->addDecorator('FormElements');
    	$this->addDecorator('Form');

    	/* HIDDEN FORM ELEMENT ***************************************/
		$page_group_id = new Zend_Form_Element_Hidden("page_group_id");
		$page_group_id->setDecorators(array('ViewHelper'));
		
		/* PAGE GROUP NAME TEXT FIELD **************************/
		$pg_name = new Zend_Form_Element_Text("page_group_name");
		$pg_name->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setFilters(array('StringTrim', 'StripTags'))
			->setValidators(array(
				array('NotEmpty',true),
				array('StringLength', false, array(1, 35))
			))
			->setAttribs(array(
				'maxlength' => '35',
				'class'     => 'form-element',
				'size'      => '50'
			));
		
		/* DEFAULT PAGE DROP DOWN ********************************/
		$pg_default_page = new Zend_Form_Element_Select("page_id");
		$pg_default_page->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(new Zend_Validate_Digits(), array('NotEmpty', true)))
			->setAttribs(array('class'=>'form-element'));
		$page_mapper = new Atlas_Model_PagesMapper();
		$pages       = $page_mapper->fetch($page_mapper->getPages());
		foreach( $pages as $page ) {
			$pg_default_page->addMultiOption(
				$page['page_id'],
				$page['page_name']
			);
		}
		
		/* FILE FIELD FOR PAGE GROUP IMAGE ************/
		$pg_image = new Zend_Form_Element_File("image");
		$pg_image->setRequired(false)
    		->setDecorators(array('File'))
			->addValidator('Count', false, 1)
			->addValidator('Size', false, array('max' => '500000000'))
			->addValidator('Extension', false, array("png", "jpg", "gif"))
			->setAttribs(array(
				'class' => 'form-element',
			));
		
		/* DROP DOWN FOR PAGE GROUP STATUS **********************/
		$pg_status = new Zend_Form_Element_Select("group_active");
		$pg_status->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(new Zend_Validate_Digits(), array('NotEmpty', true)))
			->setAttribs(array('class'=>'form-element'))
			->addMultiOption(0, "Inactive")
			->addMultiOption(1, "Active");
		
    	/* SUBMIT BUTTON *****************************************/
    	$submit = new Zend_Form_Element_Submit('submit', 'Submit');
	    $submit->setAttrib('class', 'form-element')
			->setDecorators(array('ViewHelper'));
        
	    /* ADD ELEMENTS TO FORM ****************************/
        $this->addElements(array($page_group_id, $pg_name, $pg_default_page, $pg_image, $pg_status, $submit));
    }

}

?>