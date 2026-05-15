<?php

class Atlas_Form_CallStatus extends Zend_Form
{

    public function init()
    {
		/* FORM META DATA **************************/
    	$this->setDisableLoadDefaultDecorators(true);
    	$this->addDecorator('FormElements');
    	$this->addDecorator('Form');

    	/* HIDDEN FORM ELEMENTS **********************************/
		$status_id = new Zend_Form_Element_Hidden("callstatusid");
		$status_id->setDecorators(array('ViewHelper'));
		$this->addElement($status_id);
			
		/* CALL STATUS ****************************************/
		$callstatus = new Zend_Form_Element_Text("callstatus");
		$callstatus->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setFilters(array('StringTrim', 'StripTags'))
			->setValidators(array(
				array('NotEmpty',true),
				array('StringLength', false, array(1, 170))
			))
			->setAttribs(array(
				'maxlength' => '170',
				'class'     => 'form-element',
				'size'      => '50'
			));
		$this->addElement($callstatus);
		
    	/* SUBMIT BUTTON *****************************************/
    	$submit = new Zend_Form_Element_Submit('submit', 'Submit');
	    $submit->setAttrib('class', 'form-element')
			->setDecorators(array('ViewHelper'));
        $this->addElement($submit);
    }

}

?>