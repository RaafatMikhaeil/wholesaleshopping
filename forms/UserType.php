<?php

class Atlas_Form_UserType extends Zend_Form
{
	protected $_permission_settings;
	protected $_permission_create;
	protected $_permission_status;
	protected $_permission_comment;
	
	public function __construct($permissions = "0000")
	{
		$this->_permission_settings = substr($permissions, 0, 1);
		$this->_permission_create   = substr($permissions, 1, 1);
		$this->_permission_status   = substr($permissions, 2, 1);
		$this->_permission_comment  = substr($permissions, 3, 1);
	
		parent::__construct();
	}
	
    public function init()
    {
		/* FORM META DATA **************************/
        $this->setDisableLoadDefaultDecorators(true);
    	$this->addDecorator('FormElements');
    	$this->addDecorator('Form');
		
    	/* HIDDEN FORM DATA ****************************************/
    	$user_type_id = new Zend_Form_Element_Hidden('user_type_id');
    	$user_type_id->setDecorators(array('ViewHelper'));
    	
    	/* TEXT FIELD FOR FIELD LABEL ***********************************/
    	$user_type_title = new Zend_Form_Element_Text('user_type_label');
    	$user_type_title->setRequired(true)
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
		
		/* CHECKBOX FOR SETTINGS PERMISSION ******************/
    	$settings = new Zend_Form_Element_Checkbox('settings');
    	$settings->setRequired(true)
    		->setDecorators(array('ViewHelper'))
			->setValue($this->_permission_settings);
		
		/* CHECKBOX FOR CREATE PERMISSION ****************/
    	$create = new Zend_Form_Element_Checkbox('create');
    	$create->setRequired(true)
    		->setDecorators(array('ViewHelper'))
			->setValue($this->_permission_create);
		
		/* CHECK BOX FOR STATUS PERMISSION ***************/
    	$status = new Zend_Form_Element_Checkbox('status');
    	$status->setRequired(true)
    		->setDecorators(array('ViewHelper'))
			->setValue($this->_permission_status);
		
		/* CHECK BOX FOR COMMENT PERMISSION ****************/
    	$comment = new Zend_Form_Element_Checkbox('comment');
    	$comment->setRequired(true)
    		->setDecorators(array('ViewHelper'))
			->setValue($this->_permission_comment);
		
    	/* SUBMIT BUTTON *****************************************/
    	$submit = new Zend_Form_Element_Submit('submit', 'Submit');
	    $submit->setAttrib('class', 'form-element')
			->setDecorators(array('ViewHelper'));
        
	    /* ADD ELEMENTS TO FORM ************************************/
        $this->addElements(array($user_type_id, $user_type_title, $settings, $create, $status, $comment, $submit));
    }

}

?>