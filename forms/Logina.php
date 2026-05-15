<?php

class Atlas_Form_Login extends Zend_Form
{

    public function init()
    {
		/* FORM META DATA **************************/
    	$this->setDisableLoadDefaultDecorators(true);
    	$this->addDecorator('FormElements');
    	$this->addDecorator('Form');
		
    	/* USERNAME TEXT FIELD ***************************/
    	$username = new Zend_Form_Element_Text('username');
    	$username->setRequired(true)
    		->setDecorators(array('ViewHelper'))
			->setFilters(array('StringTrim', 'StripTags'))
    		->setValidators(array(
    			array('NotEmpty',true),
    			array('StringLength', false, array(1, 65))
    		))
    		->setAttribs(array(
	    		'maxlength' => '65',
	    		'class'     => 'form-element',
                        'size'      => '30',
                        'placeholder'=> 'Username',
                        'required'  => 'required'
    		));
		
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
    	
    	/* SUBMIT BUTTON ****************************************/
    	$submit = new Zend_Form_Element_Submit('submit', 'Login');
	    $submit->setAttrib('class', 'form-element')
	    	->setDecorators(array('ViewHelper'));
        
	    /* ADD ELEMENTS TO FORM ********************************/
        $this->addElements(array($username, $password, $submit));
    }

}

?>