<?php

class Atlas_Form_DashboardFilter extends Zend_Form
{
	protected $_user_id;
	
	public function __construct($user_id = 0)
	{
		$this->_user_id = $user_id;
		parent::__construct();
	}
	
    public function init()
    {
		/* FORM META DATA **************************/
    	$this->setDisableLoadDefaultDecorators(true);
    	$this->addDecorator('FormElements');
    	$this->addDecorator('Form');
		
		/* STATUS SELECTION DROP DOWN ************************/
		$status_id = new Zend_Form_Element_Select("status_id");
		$status_id->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(array('NotEmpty', true)))
			->setAttribs(array('class'=>'form-element'))
			->addMultiOption("#", "");
        
	    /* ADD ELEMENT TO FORM ***************/
        $this->addElements(array($status_id));
    }

}

?>