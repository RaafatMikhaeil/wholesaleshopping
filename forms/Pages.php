<?php

class Atlas_Form_Pages extends Zend_Form
{

    public function init()
    {
		/* FORM META DATA **************************/
    	$this->setDisableLoadDefaultDecorators(true);
    	$this->addDecorator('FormElements');
    	$this->addDecorator('Form');

    	/* HIDDEN FORM ELEMENT ***************************/
		$page_id = new Zend_Form_Element_Hidden("page_id");
		$page_id->setDecorators(array('ViewHelper'));
		
		/* PAGE NAME TEXT FIELD *************************/
		$p_name = new Zend_Form_Element_Text("page_name");
		$p_name->setRequired(true)
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
		
		/* DROP DOWN FOR PAGE GROUP *********************************/
		$p_page_group = new Zend_Form_Element_Select("page_group_id");
		$p_page_group->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(new Zend_Validate_Digits(), array('NotEmpty', true)))
			->setAttribs(array('class'=>'form-element'));
		$page_group_mapper = new Atlas_Model_PageGroupsMapper();
		$page_groups       = $page_group_mapper->fetch($page_group_mapper->getPageGroups());
		foreach( $page_groups as $page_group ) {
			$p_page_group->addMultiOption(
				$page_group['page_group_id'],
				$page_group['page_group_name']
			);
		}
		
		/* DROP DOWN FOR PAGE STATUS **************************/
		$p_status = new Zend_Form_Element_Select("page_active");
		$p_status->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(new Zend_Validate_Digits(), array('NotEmpty', true)))
			->setAttribs(array('class'=>'form-element'))
			->addMultiOption(0, "Inactive")
			->addMultiOption(1, "Active");
			
		/* DROP DOWN FOR DASHBOARD STATUS ******************************/
		$p_dashboard = new Zend_Form_Element_Select("show_in_dashboard");
		$p_dashboard->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(new Zend_Validate_Digits(), array('NotEmpty', true)))
			->setAttribs(array('class'=>'form-element'))
			->addMultiOption(0, "No")
			->addMultiOption(1, "Yes");

        /* HOST TEXT FIELD *************************/
        $p_host = new Zend_Form_Element_Text("host");
        $p_host->setRequired(true)
            ->setDecorators(array('ViewHelper'))
            ->setFilters(array('StringTrim', 'StripTags'))
            ->setValidators(array(
                array('NotEmpty',true),
                array('StringLength', false, array(1, 45))
            ))
            ->setAttribs(array(
                'maxlength' => '45',
                'class'     => 'form-element',
                'size'      => '50'
            ));
		
		/* PATH TEXT FIELD *************************/
		$p_path = new Zend_Form_Element_Text("path");
		$p_path->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setFilters(array('StringTrim', 'StripTags'))
			->setValidators(array(
				array('NotEmpty',true),
				array('StringLength', false, array(1, 45))
			))
			->setAttribs(array(
				'maxlength' => '45',
				'class'     => 'form-element',
				'size'      => '50'
			));
		
    	/* SUBMIT BUTTON *****************************************/
    	$submit = new Zend_Form_Element_Submit('submit', 'Submit');
	    $submit->setAttrib('class', 'form-element')
			->setDecorators(array('ViewHelper'));
        
	    /* ADD ELEMENTS TO FORM ************************/
        $this->addElements(array($page_id, $p_name, $p_page_group, $p_status, $p_dashboard, $p_host, $p_path, $submit));
    }

}

?>