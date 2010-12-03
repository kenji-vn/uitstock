<?php
class Admin_ArticleController extends ZendStock_Controller_Action {
	public $config;		
	public $request;
	public $templateMapper;
	public $themeMapper;
	public $articleMapper;
	public $contentCategoryMapper;
    
	public function init() {    		
		 $this->templateMapper = new Cloud_Model_Template_CloudTemplateMapper();	  		
		 $this->themeMapper = new Cloud_Model_Theme_CloudThemeMapper(); 		
		 $this->contentCategoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();			     	           
	     $dirTemplate = $this->templateMapper->getTemplateDefault(1); 
		 $dirTheme = $this->themeMapper->getThemeDefault(1);		     	           
	     $this->config = $this->createLayout($dirTemplate, $dirTheme);	    
	        	    
		 session_start();
		 $_SESSION['log'] = true;			 
		 $this->request = $this->getRequest();		 		 		 	
	}	
	
	public function listCategoryAction()
	{
		$this->view->headTitle($this->config['title']['category']);	
		$_SESSION['temp'] = $_SERVER['REQUEST_URI'];	

		$name = $this->request->getParam('name');
		if (null == $name) {
			$parents = $this->contentCategoryMapper->fetchAllParent();
			$subs = $this->contentCategoryMapper->fetchAllSub();
		} else 
			$parents = $this->contentCategoryMapper->searchCat($name);
						    
	    $this->view->assign(array(
	    		'parents' => $parents,
	    		'subs' => $subs,
	    ));
	}
	
	public function addCatAction()
	{
		$this->view->headTitle($this->config['title']['addCategory']);		  
	    
	    $form = new Cloud_Form_Admin_ContentCategory_Add(array(
			'parents' => $this->contentCategoryMapper->fetchAllParent(),						
		));

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($this->request->getPost())) {			
				$values = $form->getValues();
				$category = new Cloud_Model_ContentCategory_CloudContentCategory($values);
				$newId = $this->contentCategoryMapper->save($category);
				$this->contentCategoryMapper->updateAlias($newId, $values['name']);																	
				$this->view->message = 'Đã thêm loại tin: ' . $values['name'];
			}
		}
		
		$this->view->form = $form;
	}
	
	public function editCatAction()
	{
		$this->view->headTitle($this->config['title']['editCategory']);

		if (null != $this->request->getParam('id')) {
			$id = $this->request->getParam('id');
			$currentCategory = new Cloud_Model_ContentCategory_CloudContentCategory();
			$this->contentCategoryMapper->find($id, $currentCategory);
		
			$form = new Cloud_Form_Admin_ContentCategory_Edit(array(
			  'category' => $currentCategory,		
			  'parents' => $this->contentCategoryMapper->fetchAllParent(),				
			));
			
			if ($this->getRequest()->isPost()) {
				if ($form->isValid($this->request->getPost())) {			
					$values = $form->getValues();
					$category = new Cloud_Model_ContentCategory_CloudContentCategory($values);
					$this->contentCategoryMapper->save($category);
					$this->contentCategoryMapper->updateAlias($values['id'], $values['name']);																	
					$this->view->message = 'Đã sửa loại tin: ' . $values['name'];
				}
			}
			
			$this->view->form = $form;
		}		
	}
	
	public function deleteCatAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		if (null != $this->request->getParam('id')) {			
			$id = $this->request->getParam('id');	
			var_dump($id);		
			$this->contentCategoryMapper->delete($id);
		}
	}
		
	public function publishCatAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
				 
		if ($this->request->getParam('listid') != null) {			
			$listid = explode(",", $this->request->getParam('listid'));
			$this->contentCategoryMapper->setPublishAction($listid);
		}	
		else 
			echo 'error';							
	}
	
	public function unpublishCatAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		if ($this->request->getParam('listid') != null) {				 			
			$listid = explode(",", $this->request->getParam('listid'));					 			
			$this->contentCategoryMapper->setUnPublish($listid);
		}		
		else 
			echo 'error';						
	}	
	
	public function setPublishCatAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		if ($this->request->getParam('id') != null) {							
			$id = $this->request->getParam('id');		 															 			
			$this->contentCategoryMapper->setPublishCatAjax($id);
		}		
		else 
			echo 'error';
	}
	
	public function autoSuggestionCatAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);	
				
		$name = $this->request->getParam('name');				
		$result = $this->contentCategoryMapper->autoSuggestionCat($name);
		if ($result) {			
			echo '<ul>';
			foreach ($result as $row) {
				echo '<li onClick="fill(\''.$row->name.'\');">'.$row->name.'</li>';	
			}
			echo '</ul>';
		} 
	}	
	
}