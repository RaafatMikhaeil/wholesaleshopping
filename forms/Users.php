<?php

class Atlas_Form_Users extends Zend_Form
{

    public function init()
    {
		/* FORM META DATA **************************/
    	$this->setDisableLoadDefaultDecorators(true);
    	$this->addDecorator('FormElements');
    	$this->addDecorator('Form');

    	/* HIDDEN FORM ELEMENTS **************************/
		$user_id = new Zend_Form_Element_Hidden("user_id");
		$user_id->setDecorators(array('ViewHelper'));
		
		/* TEXT FIELD TO SET USER'S NAME *********/
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
				'class'     => 'form-element',
				'size'      => '50'
			));
			
		/* TEXT FIELD TO SET USER'S USERNAME *************/
		$username = new Zend_Form_Element_Text("username");
		$username->setRequired(true)
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
			
		/* TEXT FIELD TO SET USER'S EMAIL **********/
		$email = new Zend_Form_Element_Text("email");
		$email->setRequired(true)
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
		
		/* DROP DOWN TO DESIGNATE ADMIN STATUS *************/
		$is_admin = new Zend_Form_Element_Select("is_admin");
		$is_admin->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(new Zend_Validate_Digits(), array('NotEmpty', true)))
			->setAttribs(array('class'=>'form-element'))
			->addMultiOption(0, 'No')
			->addMultiOption(1, 'Yes');
		/* DROP DOWN TO DESIGNATE ADMIN STATUS *************/
                
                $customers_mapper = new Atlas_Model_CustomersMapper();
                $customers = $customers_mapper->selectAll()->query()->fetchAll();
		
		$cust_id = new Zend_Form_Element_Select("cust_id");
		$cust_id->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(new Zend_Validate_Digits(), array('NotEmpty', true)))
			->setAttribs(array('class'=>'form-element'))
			->addMultiOption(0, '- SELECT CUSTOMER -');
                foreach ($customers as $customer){
                    $cust_id->addMultiOption($customer['cust_id'],$customer['cust_name']);
                }
		/* DROP DOWN TO DESIGNATE USER STATUS ****************/
		$is_active = new Zend_Form_Element_Select("is_active");
		$is_active->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setFilters(array('StringTrim', 'StripTags'))
			->setValidators(array(new Zend_Validate_Digits(), array('NotEmpty', true)))
			->setAttribs(array('class'=>'form-element'))
			->addMultiOption(0, 'Inactive')
			->addMultiOption(1, 'Active');
		
    	/* SUBMIT BUTTON *****************************************/
    	$submit = new Zend_Form_Element_Submit('submit', 'Submit');
	    $submit->setAttrib('class', 'form-element')
			->setDecorators(array('ViewHelper'));
        
	    /* ADD ELEMENTS TO FORM ***********************/
        $this->addElements(array($user_id,$cust_id, $name, $username, $email, $is_admin, $is_active, $submit));
    }

}

?>