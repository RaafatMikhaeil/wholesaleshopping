<?php

class Atlas_Form_Profile extends Zend_Form
{

    public function init()
    {
		/* FORM META DATA **************************/
    	$this->setDisableLoadDefaultDecorators(true);
    	$this->addDecorator('FormElements');
    	$this->addDecorator('Form');
    	
		$elements = array();
		
    	/* HIDDEN FORM ELEMENT ***************************/
		$user_id = new Zend_Form_Element_Hidden("user_id");
		$user_id->setDecorators(array('ViewHelper'));
		$elements[] = $user_id;

		/* HIDDEN FORM ELEMENT ***************************/
		$data = new Zend_Form_Element_Hidden("data");
		$data->setDecorators(array('ViewHelper'));
		$elements[] = $data;

		/* HIDDEN FORM ELEMENT ***************************/
		$height = new Zend_Form_Element_Hidden("height");
		$height->setDecorators(array('ViewHelper'));
		$elements[] = $height;

		/* HIDDEN FORM ELEMENT ***************************/
		$width = new Zend_Form_Element_Hidden("width");
		$width->setDecorators(array('ViewHelper'));
		$elements[] = $width;

		/* FILE FIELD FOR SIGNATURE FILE ***************/
		$signature_file = new Zend_Form_Element_File("signature_file");
		$signature_file->setRequired(false)
			->setDecorators(array('File'))
			->addValidator('Count', false, 1)
			->addValidator('Size', false, array('max' => '500000000'))
			->addValidator('Extension', false, array("png", "jpg", "jpeg", "gif"))
			->setAttribs(array(
				'class' => 'profile-form-element',
			));
		$elements[] = $signature_file;
		
		/* USER'S NAME TEXT FIELD ****************/
		$name = new Zend_Form_Element_Text("name");
		$name->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setFilters(array('StringTrim', 'StripTags'))
			->setValidators(array(
				array('NotEmpty',true),
				array('StringLength', false, array(1, 35))
			))
			->setAttribs(array(
				'maxlength' => '35',
				'class'     => 'profile-form-element',
				'size'      => '35'
			));
		$elements[] = $name;
			
		/* USER'S EMAIL TEXT FIELD *****************/
		$email = new Zend_Form_Element_Text("email");
		$email->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setFilters(array('StringTrim', 'StripTags'))
			->setValidators(array(
				new Zend_Validate_EmailAddress(),
				array('NotEmpty',true),
				array('StringLength', false, array(1, 35))
			))
			->setAttribs(array(
				'maxlength' => '35',
				'class'     => 'profile-form-element',
				'size'      => '35'
			));
		$elements[] = $email;
		
		/* USER'S PASSWORD TEXT FIELD ****************************/
		$password_1 = new Zend_Form_Element_Password("password_1");
		$password_1->setRequired(false)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(
				array('NotEmpty',true),
				array('StringLength', false, array(1, 35))
			))
			->setAttribs(array(
				'maxlength' => '35',
				'class'     => 'profile-form-element',
				'size'      => '25'
			));
		$elements[] = $password_1;

		/* USER'S NEW PASSWORD TEXT FIELD ********************/
		$password_2 = new Zend_Form_Element_Password("password_2");
		$password_2->setRequired(false)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(
				array('NotEmpty',true),
				array('StringLength', false, array(1, 35))
			))
			->setAttribs(array(
				'maxlength' => '35',
				'class'     => 'profile-form-element',
				'size'      => '25'
			));
		$elements[] = $password_2;

		/* USER'S CONFIRM NEW PASSWORD TEXT FIELD ********************/
		$password_3 = new Zend_Form_Element_Password("password_3");
		$password_3->setRequired(false)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(
				array('NotEmpty',true),
				array('StringLength', false, array(1, 35))
			))
			->setAttribs(array(
				'maxlength' => '35',
				'class'     => 'profile-form-element',
				'size'      => '25'
			));
		$elements[] = $password_3;
			
		// get the default task meta data
		$user_id   = Zend_Registry::get('user_id');
			// set the value as 0 and hidden if the user doesn't have access to statuses
			$default_task = new Zend_Form_Element_Hidden('default_task');
			$default_task->setValue(0);
			$elements[] = $default_task;

		
    	/* SUBMIT BUTTON *****************************************/
    	$submit = new Zend_Form_Element_Submit('submit', 'Submit');
	    $submit->setDecorators(array('ViewHelper'));
		$elements[] = $submit;
        
	    /* ADD ELEMENTS TO FORM ****************/
        $this->addElements($elements);
    }

}

?>