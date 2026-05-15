<?php

class Atlas_Form_Exppasschange extends Zend_Form
{

    public function init()
    {
		/* FORM META DATA **************************/
    	$this->setDisableLoadDefaultDecorators(true);
    	$this->addDecorator('FormElements');
    	$this->addDecorator('Form');
		
    	
		
        /* PASSWORD TEXT FIELD *******************************/
    	$password = new Zend_Form_Element_Password('password');
    	$password->setRequired(true)
    		->setDecorators(array('ViewHelper'))
    		->setValidators(array(
    			array('NotEmpty',true),
    			array('StringLength', false, array(1, 65))
    		))
    		->setAttribs(array(
	    		'maxlength' => '65',
	    		'class'     => 'form-element',
	    		'size'      => '30',
                        'placeholder'=> 'Password',
                        'required'  => 'required'
    		));
        
        /*PASSWORD CONFIRM TEXT FIELD *******************************/
    	$password_conf = new Zend_Form_Element_Password('password_conf');
    	$password_conf->setRequired(true)
    		->setDecorators(array('ViewHelper'))
    		->setValidators(array(
    			array('NotEmpty',true),
    			array('StringLength', false, array(1, 65))
    		))
    		->setAttribs(array(
	    		'maxlength' => '65',
	    		'class'     => 'form-element',
	    		'size'      => '30',
                        'placeholder'=> 'Confirm Password',
                        'required'  => 'required'
    		));
    	
    	/* SUBMIT BUTTON ****************************************/
    	$submit = new Zend_Form_Element_Submit('submit', 'Save');
	    $submit->setAttrib('class', 'form-element')
	    	->setDecorators(array('ViewHelper'));
        
	    /* ADD ELEMENTS TO FORM ********************************/
        $this->addElements(array($password, $password_conf, $submit));
    }

}

?>