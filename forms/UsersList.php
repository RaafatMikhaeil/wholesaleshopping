<?php

class Atlas_Form_UsersList extends Zend_Form
{
	public function init()
    {
		/* FORM META DATA **************************/
    	$this->setDisableLoadDefaultDecorators(true);
    	$this->addDecorator('FormElements');
    	$this->addDecorator('Form');
		
		/* DROP DOWN FOR USER SELECTION ******************/
		$user_id = new Zend_Form_Element_Select("user_id");
		$user_id->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(array('NotEmpty', true)))
			->setAttribs(array('class'=>'form-element'))
			->addMultiOption("#", "");
		$user_mapper = new Atlas_Model_UsersMapper();
		$users       = $user_mapper->fetchAll();
		foreach( $users as $user ) {
			$user_id->addMultiOption(
				"/quality/permissions/id/".$user->getUser_id(),
				$user->getName()
			);
		}
        
	    /* ADD ELEMENT TO FORM ************/
        $this->addElements(array($user_id));
    }
	
	public function buildAltList()
	{
		/* DROP DOWN FOR USER SELECTION ******************/
		$user_id = new Zend_Form_Element_Select("user_id");
		$user_id->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(array('NotEmpty', true)))
			->setAttribs(array('class'=>'form-element'))
			->addMultiOption("#", "");
		$user_mapper = new Atlas_Model_UsersMapper();
		$users       = $user_mapper->fetchAll();
		foreach( $users as $user ) {
			$user_id->addMultiOption(
				"/calls/alerts/id/".$user->getUser_id(),
				$user->getName()
			);
		}
        
	    /* ADD ELEMENT TO FORM ************/
        $this->addElements(array($user_id));
	}
	
	public function buildAltList2()
	{
		/* DROP DOWN FOR USER SELECTION ******************/
		$user_id = new Zend_Form_Element_Select("user_id");
		$user_id->setRequired(true)
			->setDecorators(array('ViewHelper'))
			->setValidators(array(array('NotEmpty', true)))
			->setAttribs(array('class'=>'form-element'))
			->addMultiOption("#", "");
		$user_mapper = new Atlas_Model_UsersMapper();
		$users       = $user_mapper->fetchAll();
		foreach( $users as $user ) {
			$user_id->addMultiOption(
				"/stability-tests/alerts/id/".$user->getUser_id(),
				$user->getName()
			);
		}
        
	    /* ADD ELEMENT TO FORM ************/
        $this->addElements(array($user_id));
	}

    public function buildAltList3()
    {
        /* DROP DOWN FOR USER SELECTION ******************/
        $user_id = new Zend_Form_Element_Select("user_id");
        $user_id->setRequired(true)
            ->setDecorators(array('ViewHelper'))
            ->setValidators(array(array('NotEmpty', true)))
            ->setAttribs(array('class'=>'form-element'))
            ->addMultiOption("#", "");
        $user_mapper = new Atlas_Model_UsersMapper();
        $users       = $user_mapper->fetchAll();
        foreach( $users as $user ) {
            $user_id->addMultiOption(
                "/arms/alertusers/id/".$user->getUser_id(),
                $user->getName()
            );
        }

        /* ADD ELEMENT TO FORM ************/
        $this->addElements(array($user_id));
    }

}

?>