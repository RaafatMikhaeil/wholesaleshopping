<?php

class Atlas_Form_EmailUser extends Zend_Form
{

    public function init()
    {
		/* FORM META DATA ***************************/
    	$this->setDisableLoadDefaultDecorators(true);
    	$this->addDecorator('FormElements');
    	$this->addDecorator('Form');

		/* EMAIL ADDRESS ******************/
		$email_address = new Zend_Form_Element_Text("email_address");
		$email_address->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setFilters(array('StringTrim', 'StripTags'))
			->setValidators(array(
				array('NotEmpty',true),
				array('StringLength', false, array(1, 200))
			))
			->setAttribs(array(
				'maxlength' => '200',
				'class'     => 'form-element',
				'size'      => '50'
			));
		$this->addElement($email_address);
		
		/* COMMENTS ********************************************/
		$comments = new Zend_Form_Element_Textarea("comments");
		$comments->setRequired(false)
			->setDecorators(array('ViewHelper'))
			->setFilters(array('StringTrim'))
    		->setAttribs(array(
				'cols'      => '60',
				'rows'      => '10',
			));
		$this->addElement($comments);
			
    	/* SUBMIT BUTTON *****************************************/
    	$submit = new Zend_Form_Element_Submit('submit', 'Send');
	    $submit->setAttrib('class', 'form-element')
			->setDecorators(array('ViewHelper'));
        $this->addElement($submit);
    }

}

?>