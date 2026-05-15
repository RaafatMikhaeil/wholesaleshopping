<?php

class Atlas_Form_PermissionGroups extends Zend_Form
{
    public function init()
    {
		/* FORM META DATA **************************/
    	$this->setDisableLoadDefaultDecorators(true);
    	$this->addDecorator('FormElements');
    	$this->addDecorator('Form');

    	/* HIDDEN FORM ELEMENT ***************************************************/
		$permission_group_id = new Zend_Form_Element_Hidden("permission_group_id");
		$permission_group_id->setDecorators(array('ViewHelper'));
		
		/* PERMISSION GROUP TITLE TEXT FIELD ***************************/
		$pg_title = new Zend_Form_Element_Text("permission_group_title");
		$pg_title->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setFilters(array('StringTrim', 'StripTags'))
			->setValidators(array(
				array('NotEmpty',true),
				array('StringLength', false, array(1, 75))
			))
			->setAttribs(array(
				'maxlength' => '75',
				'class'     => 'form-element',
				'size'      => '50'
			));
		
    	/* SUBMIT BUTTON *****************************************/
    	$submit = new Zend_Form_Element_Submit('submit', 'Submit');
	    $submit->setAttrib('class', 'form-element')
			->setDecorators(array('ViewHelper'));
        
	    /* ADD ELEMENTS TO FORM ************************/
        $this->addElements(array($permission_group_id, $pg_title, $submit));
    }

}

?>