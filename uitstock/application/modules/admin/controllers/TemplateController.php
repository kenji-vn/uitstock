<?php
class Admin_TemplateController extends ZendStock_Controller_Action {
	public $config;		
	public $request;
	public $templateMapper;
	public $themeMapper;
	public $privileges;
	public $privilegeTypeMapper;
	public $entry;
    
	public function init() {
		 if (!isset($_SESSION['log']))
		 	$this->_redirect('admin/index/error');
		 			   		 
		 $this->privileges = $_SESSION['privilege'];		 
		 $flag = false;		
		 foreach ($this->privileges as $privilege) {
			if ($privilege['id'] == 53) $flag = true; 
		 }
		 
	 	 if (!$flag) 
			$this->_redirect('admin/index/error');		 
		  		
		 $this->templateMapper = new Cloud_Model_Template_CloudTemplateMapper();	  		
		 $this->themeMapper = new Cloud_Model_Theme_CloudThemeMapper(); 			     	           
	     $dirTemplate = $this->templateMapper->getTemplateDefault(1); 
		 $dirTheme = $this->themeMapper->getThemeDefault(1);		     	           
	     $this->config = $this->createLayout($dirTemplate, $dirTheme);	    
	        	    		 
		 $this->request = $this->getRequest();

		 $this->privilegeTypeMapper = new Cloud_Model_PrivilegeType_CloudPrivilegeTypeMapper();
		 $this->view->privileges = $this->privileges;	
		 
		 $this->entry = "";
		 foreach ($this->privileges as $privilege) {
			$this->entry = $this->entry . "," . $privilege['id']; 
		 }
		 $this->entry = substr($this->entry, 1);
		 
		 $this->view->assign(array(	    	
		 	'userMapper' => new Cloud_Model_User_CloudUserMapper(),
		 	'session' => new Cloud_Model_UserSession_CloudUserSessionMapper(),	    	   		    
	    ));			 
	}				
	
	public function listAction() {
		$this->view->headTitle($this->config['title']['template']);		
		$_SESSION['temp'] = $_SERVER['REQUEST_URI'];		
		
		$componentMapper = new Cloud_Model_Component_CloudComponentMapper();
		$components = $componentMapper->fetchAllByOrder();
		$component_1 = $components[0];	
												
		$component_id = $this->request->getParam('component');	
		
		$c = (null == $component_id) ? $component_1->id : $component_id;

		$name = $this->request->getParam('name');
		if (null == $name) 
			$templates = $this->templateMapper->getTemplateByComponent($c);					    		
		else
			$templates = $this->templateMapper->searchTemplate($name, $c);
			
		$this->view->assign(array(
				'c' => $c,
				'components' => $components,
				'templates' => $templates,
		        'button1' => $this->privilegeTypeMapper->getButton1ById($this->entry, 14),
		));														     				  		     		     	
	}  
	
	public function addAction() 
	{		
		 $flag = false;		
		 foreach ($this->privileges as $privilege) {
			if ($privilege['id'] == 55) $flag = true; 
		 }
	 	 if (!$flag) {
			$this->_redirect('admin/index/error');
		 }
		 			
		$this->view->headTitle($this->config['title']['addTemplate']);
		
		$componentMapper = new Cloud_Model_Component_CloudComponentMapper();
		$components = $componentMapper->fetchAll();		
		$form = new Cloud_Form_Admin_Template_Add(array('components' => $components));				
		
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($this->request->getPost())) {			
				$values = $form->getValues();
				$template = new Cloud_Model_Template_CloudTemplate($values);
				$component_id = $values['component_id'];																	
				$this->templateMapper->saveTemplate($template, $component_id);
				$this->view->message = 'Đã thêm template: ' . $template->getName();
			}
		}
		
		$this->view->form = $form;						
	}
	
	public function editAction() {		
		 $flag = false;		
		 foreach ($this->privileges as $privilege) {
			if ($privilege['id'] == 56) $flag = true; 
		 }
	 	 if (!$flag) {
			$this->_redirect('admin/index/error');
		 }
		 
		$this->view->headTitle($this->config['title']['editTemplate']); 		    
		
		if ($this->request->getParam('id') != null) {
			$id = $this->request->getParam('id');
			
			$componentMapper = new Cloud_Model_Component_CloudComponentMapper();
			$components = $componentMapper->fetchAll();	
			$currentTemplate = new Cloud_Model_Template_CloudTemplate();		 		
			$this->templateMapper->find($id, $currentTemplate);					
			
			$form = new Cloud_Form_Admin_Template_Edit(array(
			               'template' => $currentTemplate,
			               'components' => $components,		               
			));									
			
			if ($this->getRequest()->isPost()) {
				if ($form->isValid($this->request->getPost())) {			
					$template = new Cloud_Model_Template_CloudTemplate($form->getValues());																				
					$this->templateMapper->save($template);
					$this->view->message = 'Đã sửa template: ' . $currentTemplate->getName();
				}
			}
			
			$this->view->form = $form;
		}								
	}
	
	public function deleteAction()
	{
		 $flag = false;		
		 foreach ($this->privileges as $privilege) {
			if ($privilege['id'] == 57) $flag = true; 
		 }
	 	 if (!$flag) {
			$this->_redirect('admin/index/error');
		 }
		 
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);		
				
		$id = $this->request->getParam('id');		
		$template = new Cloud_Model_Template_CloudTemplate();		 		
		$this->templateMapper->find($id, $template);		
		if (null == $template) echo 'error';
		else if ($template->getIs_default() == '1') echo 'default';
		else {
			$db = $this->templateMapper->getDbTable();
			$where = $db->getAdapter()->quoteInto('id = ?', $id);
			$db->delete($where);
		}
	}
	
	public function setDefaultTemplateAction()
	{
		$flag = false;		
		 foreach ($this->privileges as $privilege) {
			if ($privilege['id'] == 56) $flag = true; 
		 }
	 	 if (!$flag) {
			$this->_redirect('admin/index/error');
		 }
		 
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);										
			
		$component_id = $this->request->getParam('component');		
		$id = $this->request->getParam('id');			
		$count = $this->request->getParam('count');		
			
		$template = new Cloud_Model_Template_CloudTemplate();		 		
		$this->templateMapper->find($id, $template);	
							
		if (null == $template) echo 'error';
		else {
			$this->templateMapper->setDefaultTemplate($id, $component_id, $count);	
		}
	}	
	
	public function autoSuggestionTemplateAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);	
				
		$name = $this->request->getParam('name');		
		$component_id = $this->request->getParam('component_id');
		$result = $this->templateMapper->autoSuggestionTemplate($name, $component_id);					
		if ($result) {			
			echo '<ul>';
			foreach ($result as $row) {
				echo '<li onClick="fill(\''.$row->name.'\');">'.$row->name.'</li>';	
			}
			echo '</ul>';
		} 
	}	
	
}