<?php
class Cloud_Form_Stock_ChangePassword extends Zend_Form
{
	/**
	 * Class        : Change Password Player
	 * Description  : Form
	 * Author       : Vita - Nguyen Ngoc Linh
	 * Student ID   : 07520194
	 * Faculty      : IS
	 */
	
	protected $_player;
	
	public function init()
	{		
		// thêm validator
		$this->addElementPrefixPath(
		     'Cloud_Validate',
		     APPLICATION_PATH . '/models/validate/Player',
		     'validate'
		);		
		
		$this->setMethod('post');				  
		
		$this->addElement('password', 'password', array(	
		      'required' => true,			      
		      'label' => 'Mật khẩu cũ',
		      'validators' => array(							
					array('PasswordValid', false, array(new
		            Cloud_Model_Player_CloudPlayerMapper())),
					array('StringLength', false, array(5, 50)),					
		      ),
			  'filters' => array('StringTrim'),		     
		));	
		
		$this->addElement('password', 'password_confirm', array(	
		      'required' => true,			      
		      'label' => 'Nhập lại mật khẩu cũ',
		      'validators' => array(
		            array('PasswordConfirmation', false),		
		            array('stringLength', false, array(5, 50)),
		      ),
			  'filters' => array('StringTrim'),	     
		));	
		
		$this->addElement('password', 'new_password', array(	
		      'required' => true,			      
		      'label' => 'Mật khẩu mới',
		      'validators' => array(							
					array('StringLength', false, array(5, 50)),
		      ),
			  'filters' => array('StringTrim'),		     
		));	
		
		$this->addElement('submit', 'Change', array(              
		       'ignore' => true,		      	
			   'label' => 'Cập nhật',	   
		));	
		
		$this->addDisplayGroup(
			array('password', 'password_confirm', 'new_password', 'Change'),
			'group1',
			array(				
				'legend' => 'Đổi mật khẩu',				
				'class' => 'group g-red',			   
			)			
		);

		$this->addElement('hidden', 'id', array(
		       'filters' => array('StringTrim'),
		       'required' => true,
		       'value' => $this->_player->id,
		       'decorators' => array('ViewHelper', array('HtmlTag',
		               array('tag' => 'dd', 'class' => 'noDisplay')))
		));	
		
		$this->addElement('hidden', 'username', array(
		       'filters' => array('StringTrim'),
		       'required' => true,
		       'value' => $this->_player->username,
		       'decorators' => array('ViewHelper', array('HtmlTag',
		               array('tag' => 'dd', 'class' => 'noDisplay')))
		));	
	}
	
	public function setPlayer($player)
	{
		$this->_player = $player;
	}
}