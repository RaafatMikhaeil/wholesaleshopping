<?php

class Atlas_Form_PasswordReset extends Zend_Form
{

    public function init()
    {
		/* FORM META DATA **************************/
    	$this->setDisableLoadDefaultDecorators(true);
    	$this->addDecorator('FormElements');
    	$this->addDecorator('Form');
		
		/* USER'S PASSWORD TEXT FIELD ****************************/
		$password = new Zend_Form_Element_Password("password");
		$password->setRequired(false)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(
				array('NotEmpty',true),
				array('StringLength', false, array(1, 35))
			))
			->setAttribs(array(
				'maxlength' => '35',
				'class'     => 'form-element',
				'size'      => '25'
			));
		$this->addElement($password);
		
		/* USER'S CONFIRM PASSWORD TEXT FIELD ********************/
		$password = new Zend_Form_Element_Password("confirm");
		$password->setRequired(false)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(
				array('NotEmpty',true),
				array('StringLength', false, array(1, 35)),
				array('identical', false, array('token' => 'password'))
			))
			->setAttribs(array(
				'maxlength' => '35',
				'class'     => 'form-element',
				'size'      => '25'
			));
		$this->addElement($password);
		
    	/* SUBMIT BUTTON *****************************************/
    	$submit = new Zend_Form_Element_Submit('submit', 'Reset');
	    $submit->setAttrib('class', 'form-element')
			->setDecorators(array('ViewHelper'));
		$this->addElement($submit);
    }

}

?>