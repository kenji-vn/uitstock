<?php
	/**
	 * Class        : Content Category Mapper
	 * Description  :
	 * Author       : Vita - Nguyen Ngoc Linh
	 * Student ID   : 07520194
	 * Faculty      : IS
	 */
	class Cloud_Model_ContentCategory_CloudContentCategoryMapper implements Cloud_Model_ContentCategory_Interface
	{
		protected $_dbTable;
		
		public function setDbTable($dbTable)
		{
			if (is_string($dbTable)) {
				$dbTable = new $dbTable;
			}
			if (!$dbTable instanceof Zend_Db_Table_Abstract) {
				throw new Exception('Invalid table data gateway provided');
			}
			$this->_dbTable = $dbTable;
			return $this;
		}
		
		public function getDbTable()
		{
			if (null == $this->_dbTable) {
				$this->setDbTable('Cloud_Model_DbTable_CloudContentCategory');
			}
			return $this->_dbTable;
		}
		
	    public function getEntry($row)
		{
			$entry = new Cloud_Model_ContentCategory_CloudContentCategory();			
			$entry->setId($row->id)	
			      ->setParent_id($row->parent_id)				      			      
				  ->setName($row->name)
				  ->setAlias($row->alias)						  				  				  				 
				  ->setDescription($row->description)
				  ->setPublished($row->published);				  
			return $entry;
		}
		
		public function getEntries($rows)
		{			
			$entries = array();
            foreach ($rows as $row) {            	 	            	       	      
                $entries[] = $this->getEntry($row);            	         
            }     
            return $entries;
		}
		
		public function save(Cloud_Model_ContentCategory_CloudContentCategory $contentCategory)
		{			
			$data = array(	
				'parent_id' => $contentCategory->getParent_id(),					    			  
				'name' => $contentCategory->getName(),
			    'alias' => $contentCategory->getAlias(),
				'description' => $contentCategory->getDescription(),
			    'published' => $contentCategory->getPublished(),			    															    							 
			);
			
			if (null == ($id = $contentCategory->getId())) {
				unset($data['id']);
				$db = $this->getDbTable();
				$db->insert($data);
				return $db->getAdapter()->lastInsertId();
			} else {
				$this->getDbTable()->update($data, array('id = ?' => $id));		
			}
		}				
		
		public function find($id, Cloud_Model_ContentCategory_CloudContentCategory  $contentCategory)
		{
			$result = $this->getDbTable()->find($id);
			if (0 == count($result)) {
				return;
			}
			$row = $result->current();	
			$contentCategory->setId($row->id)		
				 	 	    ->setParent_id($row->parent_id)				      			      
						    ->setName($row->name)
						    ->setAlias($row->alias)						  				  				  				 
						    ->setDescription($row->description)
						    ->setPublished($row->published);						    	 				 	 			 				      		   
		}	

		public function delete($id)
		{
			$currentCategory = new Cloud_Model_ContentCategory_CloudContentCategory();
			$this->find($id, $currentCategory);
			
			$db = $this->getDbTable();
			$parentId =  $currentCategory->getParent_id();
			
			if ($parentId == 0) {									
				$where = $db->getAdapter()->quoteInto('parent_id = ?', $id);
				$db->delete($where);
												
				$where = $db->getAdapter()->quoteInto('id = ?', $id);
				$db->delete($where);
			} else {								
				$where = $db->getAdapter()->quoteInto('id = ?', $id);
				$db->delete($where);
			}
		}
		
		public function updateAlias($id, $name)
		{
			$alias = Cloud_Model_Utli_CloudUtli::stripUnicode($name);
            $alias = preg_replace(array('/\s+/', '/[^A-Za-z0-9-]/', '/^[-]/', '/-$/'),
                                array('-', '','',''), strtolower($alias));     

			$data = array('alias' => $alias);
			$where = array('id = ?' => $id);
			$this->getDbTable()->update($data, $where);                                
		}		
		
		public function fetchParentByPage($page)
		{
			$db = $this->getDbTable();			
			$select = $db->select()
			             ->where('parent_id = 0');			             
			    
           $rows = $db->fetchAll($select);           
           
           $paginator = Zend_Paginator::factory($rows);
    	   $paginator->setItemCountPerPage(2);
    	   $paginator->setCurrentPageNumber($page);           		
                     
            return $paginator;  
		}

		public function fetchAllParent()
		{
			$db = $this->getDbTable();			
			$select = $db->select()
			             ->where('parent_id = 0');			             			                              	
                     
           return $db->fetchAll($select);   
		}
		
		public function fetchAllSub()
		{
			$db = $this->getDbTable();			
			$select = $db->select()
			             ->where('parent_id != 0');			             			                                 	
                     
           return $db->fetchAll($select);  
		}
		
		public function getContentCategoryByName($name, $currentContentCategory)
		{						
			if (null == $currentContentCategory) $id = "";
			else $id = $currentContentCategory->getId();
						
			$db = $this->getDbTable();
			$select = $db->select()			                	
			             ->where('name = ?', $name)			            
			             ->where('id != ?', $id);
			$row = $db->fetchRow($select);														
			if (null == $row)
				return null;
																      				 			      		
			return $row;
		}
		
		public function getSubNameById($parentId)
		{
			if ($parentId == 0) return "No";
			else {
				$db = $this->getDbTable();
				$select = $db->select()							
							 ->where('id = ?', $parentId);
				$row = $db->fetchRow($select);
				if (null == $row)
					return null;	

				return $row;	
			}
		}		

		public function getNewsParent()
		{
			$db = $this->getDbTable();			
			$select = $db->select()
						 ->from($db, array('id', 'name', 'alias'))
			             ->where('description =  ?', "Tin tức")
			             ->where('parent_id = 0')
			             ->where('published = 1');			          			                               		
                   
            return $db->fetchAll($select);       
		}
		
		public function getNewsSub($id)
		{
			$db = $this->getDbTable();			
			$select = $db->select()
						 ->from($db, array('name','alias'))			           
			             ->where('parent_id = ?', $id)
			             ->where('published = 1');			          			                               		
                   
            return $db->fetchAll($select);       
		}	

		public function getNameByAlias($alias)
		{
			$db = $this->getDbTable();			
			$select = $db->select()
						 ->from($db, array('name'))			           
			             ->where('alias = ?', $alias);			            		          			                               	
                   
            return $db->fetchRow($select);  
		}
		
		public function getAliasByArticle($articleId)
		{		
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbCategory = $this->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
					
			$articleMapper = new Cloud_Model_Article_CloudArticleMapper();
			$dbArticle= $articleMapper->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];						
							
			$select = $db->select()		                  
		                 ->from(array('a' => $dbArticleName), array())
		                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array('alias as sub_alias', 'parent_id'))
		                 ->where('a.id = ?', $articleId);		              		                                                       		        	             			                                  	
		                                    
           $row1 =  $db->fetchRow($select);          

           $select = $db->select()		                  		                
		                ->from(array('c' => $dbCategoryName), array('alias as parent_alias'))
		                ->where('id = ?', $row1['parent_id']);

		   $row2 = $db->fetchRow($select);

		   $row = array();
		   $row['sub_alias'] = $row1['sub_alias'];
		   $row['parent_alias']= $row2['parent_alias'];
		   
		   return $row;
		}				
		
		public function getIdByAlias($alias)
		{
			$db = $this->getDbTable();						
			             
			$select = $db->select()		
						 ->where('alias = ?', $alias);                  		                 		                 		                 		                                	                               		        	             			                                  	
                   
           return $db->fetchRow($select);
		}
		
		public function autoSuggestionCat($name)
		{
			if (null == $name) exit();	
			
			$db = $this->getDbTable();			
			$select = $db->select()
						 ->from($db, array('name'))
			             ->where('name like ?', "%$name%")
			             ->limit(5, 0);			          			                               		
                   
            return $db->fetchAll($select);            
		}
		
		public function searchCat($name)
		{
			if (null == $name) exit();
			
			$db = $this->getDbTable();			
			$select = $db->select()
			             ->where('name = ?', $name);			             
			    
           	$rows = $db->fetchAll($select);           
           
           	$paginator = Zend_Paginator::factory($rows);
    	   	$paginator->setItemCountPerPage(2);
    	   	$paginator->setCurrentPageNumber($page);           		
                     
	        return $paginator; 					       
		}
	}