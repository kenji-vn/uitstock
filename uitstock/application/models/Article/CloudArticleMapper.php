<?php
	/**
	 * Class        : Article Mapper
	 * Description  :
	 * Author       : Vita - Nguyen Ngoc Linh
	 * Student ID   : 07520194
	 * Faculty      : IS
	 */
	class Cloud_Model_Article_CloudArticleMapper implements Cloud_Model_Article_Interface
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
				$this->setDbTable('Cloud_Model_DbTable_CloudArticle');
			}
			return $this->_dbTable;
		}
		
	    public function getEntry($row)
		{
			$entry = new Cloud_Model_Article_CloudArticle();			
			$entry->setId($row->id)	
			      ->setCat_id($row->cat_id)				      			      
				  ->setUser_id($row->user_id)
				  ->setRelative_id($row->relative_id)						  				  				  				 
				  ->setTitle($row->title)
				  ->setAlias($row->alias)
				  ->setSummarize($row->summarize)
				  ->setImage($row->image)
				  ->setContent($row->content)
				  ->setCreate_date($row->create_date)
				  ->setModify_date($row->modify_date)
				  ->setPublished($row->published)
				  ->setImportant($row->important)
				  ->setCount($row->count);				  
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
		
		public function save(Cloud_Model_Article_CloudArticle $article)
		{			
			$data = array(	
				'cat_id' => $article->getCat_id(),					    			  
				'user_id' => $article->getUser_id(),
				'relative_id' => $article->getRelative_id(),
			    'title' => $article->getTitle(),
			    'alias' => $article->getAlias(),
			    'summarize' => $article->getSummarize(),			
			    'content' => $article->getContent(),
			    'create_date' => $article->getCreate_date(),
			    'modify_date' => $article->getModify_date(),			    
			    'published' => $article->getPublished(),			    															    							 
			    'important' => $article->getImportant(),
				'count' => $article->getCount(),
			);
			
			if (null == ($id = $article->getId())) {
				unset($data['id']);
				$db = $this->getDbTable();
				$db->insert($data);
				return $db->getAdapter()->lastInsertId();
			} else {
				$this->getDbTable()->update($data, array('id = ?' => $id));		
			}
		}				
		
		public function find($id, Cloud_Model_Article_CloudArticle $article)
		{
			$result = $this->getDbTable()->find($id);
			if (0 == count($result)) {
				return;
			}
			$row = $result->current();	
			$article->setId($row->id)		
				 	->setCat_id($row->cat_id)				      			      
				    ->setUser_id($row->user_id)
				    ->setRelative_id($row->relative_id)						  				  				  				 
				    ->setTitle($row->title)
				    ->setAlias($row->alias)
				    ->setSummarize($row->summarize)
				    ->setImage($row->image)
				    ->setContent($row->content)
				    ->setCreate_date($row->create_date)
				    ->setModify_date($row->modify_date)
				    ->setPublished($row->published)
				    ->setImportant($row->important)
				    ->setCount($row->count);								    	 				 	 			 				      		   
		}	
		
		public function search($title, $from, $end)
		{		
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];								
				
			$select = $db->select()		                  
		                 ->from(array('a' => $dbArticleName))
		                 ->where('a.title like ?', "%$title%")
		                 ->where('a.published = 1')
		                 ->order('a.id desc', 'a.create_date desc')
		                 ->limit($end, $from);		               		        	             			                            	
                     
           return $db->fetchAll($select);  
		}
		
		public function delete($listid)
		{
			$array = explode(',', $listid);
			for ($i = 0; $i < count($array); $i++) {
				$db = $this->getDbTable();					
				$where = $db->getAdapter()->quoteInto('id = ?', $array[$i]);
				$db->delete($where);	
			}
		}		
		
		public function fetchAll()
		{
			$db = $this->getDbTable();			
			$select = $db->select()
						 ->from($db, array('title', 'relative_id'));
			             					
			return $db->fetchAll();
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
		
		public function updateRelative($id, $listId)
		{
			$array = explode(',', $listId);	
			
			if (count($array) < 1) return;	
			
			$data = array('relative_id' => 0);
			$where = array('relative_id = ?' => $id);
			$this->getDbTable()->update($data, $where);								
			
			for ($i = 0; $i < count($array); $i++) {			
				$data = array('relative_id' => $id);
				$where = array('id = ?' => $array[$i]);
				$this->getDbTable()->update($data, $where);								
			}									
		}
		
		public function updateImage($id, $path)
		{			
			if ($_FILES['image']['name'] == '') return;
			
			$f = $_FILES['image'];
			$name = $f['name'];																
			$ext = substr($name, strrpos($name, '.'));
			$fileName = Zend_Date::now()->toString('yyyMMddHHmmss');
			$file = $fileName . $ext;
			$oldPath = $path . '/' . $name;
			$newPath = $path . '/' . $file;
			rename($oldPath, $newPath);			

			$data = array('image' => $newPath);
			$where = array('id = ?' => $id);
			$this->getDbTable()->update($data, $where);
		}
		
		public function updateImage2($id, $image, $path, $fileName)
		{			
			if ($_FILES['image']['name'] == '') return;
			
			$f = $_FILES['image'];
			$name = $f['name'];			
			unlink($image);
			$path = $path . '/' . $name;		
			rename($path, $image);																							
		}
		
		public function updateCount($alias)
		{		
			$db = $this->getDbTable();			
			$select = $db->select()
						 ->from($db, array('count'))
						 ->where('alias = ?', $alias);
			             
			$row = $db->fetchRow();
				
			$data = array('count' => (int) $row->count + 1);
			$where = array('id = ?' => $id);
			$this->getDbTable()->update($data, $where);																						
		}
				
		public function fetchArticleByPage($page)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
				
			$select = $db->select()		                  
		                 ->from(array('a' => $dbArticleName))
		                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array('name as cat_name'))
		                 ->order('a.id desc', 'a.create_date desc');		               		        	             
			    
           $rows = $db->fetchAll($select);           
           
           $paginator = Zend_Paginator::factory($rows);
    	   $paginator->setItemCountPerPage(5);
    	   $paginator->setCurrentPageNumber($page);           		
                     
           return $paginator;  
		}
		
		public function fetchArticleById($id)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
				
			$select = $db->select()		                  
		                 ->from(array('a' => $dbArticleName))
		                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array('name as cat_name'))
		                 ->where('a.cat_id = ?', $id)
		                 ->order('a.id desc', 'a.create_date desc');			               		        	             
			    
           $rows = $db->fetchAll($select);           
           
           $paginator = Zend_Paginator::factory($rows);
    	   $paginator->setItemCountPerPage(5);
    	   $paginator->setCurrentPageNumber($page);           		
                     
           return $paginator;  
		}
		
		public function fetchArticleBySub($id)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
				
			$select = $db->select()		                  
		                 ->from(array('a' => $dbArticleName))
		                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array('name as cat_name'))
		                 ->where('c.parent_id = ?', $id)
		                 ->order('a.id desc', 'a.create_date desc');		               		        	             
			    
           $rows = $db->fetchAll($select);           
           
           $paginator = Zend_Paginator::factory($rows);
    	   $paginator->setItemCountPerPage(5);
    	   $paginator->setCurrentPageNumber($page);           		
                     
           return $paginator;  
		}
		
		public function getArticleById($id)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
				
			$select = $db->select()		                  
		                 ->from(array('a' => $dbArticleName))
		                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array('name as cat_name'))
		                 ->where('a.id = ?', $id);		                               		        	             			                                  	
                   
           return $db->fetchRow($select);
		}
		
		public function getArticleByAlias($alias)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
				
			$select = $db->select()		                  
		                 ->from(array('a' => $dbArticleName))
		                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array('name as cat_name'))
		                 ->where('a.alias = ?', $alias);		                               		        	             			                                  	
                   
           return $db->fetchRow($select);
		}			

		public function getArticleByParent($id, $from, $end)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
				
			$select = $db->select()		                  
		                 ->from(array('a' => $dbArticleName))
		                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array(''))
		                 ->where('c.parent_id = ?', $id)
		                 ->where('a.published = 1')
		                 ->where('a.important = 0')		                
		                 ->order('a.id desc', 'a.create_date desc')
		                 ->limit($end, $from);	                               		        	             			                                  	
                   
           return $db->fetchAll($select);
		}
		
		public function getArticleByParentAlias($alias, $from, $end)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
			
			$select = $db->select()		                  		                 
		                 ->from(array('c' => $dbCategoryName), array('id'))
		                 ->where('c.alias = ?', $alias);
		                 		                
			$row = $db->fetchRow($select);		                 		                 
				
			$select = $db->select()		                  
		                 ->from(array('a' => $dbArticleName))
		                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array(''))
		                 ->where('c.parent_id = ?', $row['id'])
		                 ->where('a.published = 1')		                
		                 ->order('a.id desc', 'a.create_date desc')
		                 ->limit($end, $from);	                               		        	             			                                  	
                   
           return $db->fetchAll($select);
		}
		
		public function countByParentAlias($alias)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
			
			$select = $db->select()		                  		                 
		                 ->from(array('c' => $dbCategoryName), array('id'))
		                 ->where('c.alias = ?', $alias);
		                 		                
			$row = $db->fetchRow($select);		                 		                 
				
			$stmt = $db->query("SELECT count(*) as count from $dbArticleName a, $dbCategoryName c
								WHERE a.cat_id = c.id and c.parent_id = " . $row['id']);
			$row = $stmt->fetch();
			
			if (null == $row['count']) return 0;
			else return $row['count'];			                        		        	             			                                  	                              
		}
		
		public function getArticleBySub($id, $from, $end, $flag = null)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
				
			if ($flag) {
				$select = $db->select()		                  
			                 ->from(array('a' => $dbArticleName))
			                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array(''))
			                 ->where('c.id = ?', $id)
			                 ->where('a.published = 1')
			                 ->order('a.create_date asc')
			                 ->limit($end, $from);				
			} else {
				$select = $db->select()		                  
			                 ->from(array('a' => $dbArticleName))
			                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array(''))
			                 ->where('c.id = ?', $id)
			                 ->where('a.published = 1')
			                 ->order('a.id desc', 'a.create_date desc')
			                 ->limit($end, $from);				
			}
		                               		        	             			                                  	
		                                    
           return $db->fetchAll($select);
		}
		
		public function getArticleBySubAlias($alias, $from, $end)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
			
			$select = $db->select()		                  		                 
		                 ->from(array('c' => $dbCategoryName), array('id'))
		                 ->where('c.alias = ?', $alias);
		                 		                
			$row = $db->fetchRow($select);	
				
			$select = $db->select()		                  
		                 ->from(array('a' => $dbArticleName))
		                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array(''))
		                 ->where('c.id = ?', $row['id'])
		                 ->where('a.published = 1')
		                 ->order('a.id desc', 'a.create_date desc')
		                 ->limit($end, $from);		                               		        	             			                                  	
		                                    
           return $db->fetchAll($select);
		}
		
		public function countBySubAlias($alias)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
			
			$select = $db->select()		                  		                 
		                 ->from(array('c' => $dbCategoryName), array('id'))
		                 ->where('c.alias = ?', $alias);
		                 		                
			$row = $db->fetchRow($select);		                 		                 
				
			$stmt = $db->query("SELECT count(*) as count from $dbArticleName a, $dbCategoryName c
								WHERE a.cat_id = c.id and c.id = " . $row['id']);
			$row = $stmt->fetch();
			
			if (null == $row['count']) return 0;
			else return $row['count'];			                        		        	             			                                  	                              
		}
				
		
		public function getArticleInParent($alias1, $alias, $from, $end)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
			
			$cat = $categoryMapper->getIdByAlias($alias);
				
			$select = $db->select()		                  
		                 ->from(array('a' => $dbArticleName))
		                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array(''))		               
		                 ->where('a.alias != ?', $alias1)
		                 ->where('c.parent_id = ?', $cat->id)
		                 ->where('a.published = 1')		                
		                 ->order('a.id desc', 'a.create_date desc')
		                 ->limit($end, $from);		                               		        	             			                                  	
                   
            return $db->fetchAll($select);
		}
		
		public function getArticleInSub($alias1, $alias, $from, $end)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
			
			$cat = $categoryMapper->getIdByAlias($alias);
				
			$select = $db->select()		                  
		                 ->from(array('a' => $dbArticleName))
		                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array(''))
		                 ->where('a.alias != ?', $alias1)
		                 ->where('c.id = ?', $cat->id)
		                 ->where('a.published = 1')
		                 ->order('a.id desc', 'a.create_date desc')
		                 ->limit($end, $from);	                               		        	             			                                  	
		                                    
           return $db->fetchAll($select);
		}
		
		public function getRelativeArticle($relativeId)
		{			
			if ($relativeId == 0) return '';
			$db = $this->getDbTable();						
			             
			$select = $db->select()		
						 ->where('id = ?', $id)    
						 ->where('published = 1')              		                 		                 		                 
		                 ->order('id desc', 'create_date desc');		                           	                               		        	             			                                  	
                   
           return $db->fetchAll($select);						
		}
		
		public function getImportantArticle($from, $end, $flag)
		{			
			$db = $this->getDbTable();						
					
			if ($flag){             
				$select = $db->select()		
							 ->where('important = 1')    
							 ->where("published = 1")
							 ->where('cat_id = 2')     							                 		                 		                
			                 ->order('id desc', 'create_date desc')
			                 ->limit($end, $from);
			}else {
				$select = $db->select()		
							 ->where('important = 1')    
							 ->where("published = 1")
							 ->where('cat_id != 2')     							                 		                 		                
			                 ->order('id desc', 'create_date desc')
			                 ->limit($end, $from);
			}
							                 		   		                 	                               		        	             			                                  	                   
           return $db->fetchAll($select);						
		}	
		
		public function getMostCountArticle($from, $end)
		{			
			$db = $this->getDbTable();						
			             
			$select = $db->select()		      
			             ->where('published = 1') 
			             ->where('important = 0')              		                 		                 		       
		                 ->order('count desc')
		                 ->limit($end, $from);		                 	                               		        	             			                                  	
                   
           return $db->fetchAll($select);						
		}
		
		public function getTitleByAlias($alias)
		{
			$db = $this->getDbTable();			
			$select = $db->select()
						 ->from($db, array('title'))			           
			             ->where('alias = ?', $alias);				           
                   
            return $db->fetchRow($select);  
		}
		
		public function getSummarize($id, $from, $end)
		{
			$db = Zend_DB_table_Abstract::getDefaultAdapter();

			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];			
			
			$stmt = $db->query("SELECT distinct(summarize) as date FROM $dbArticleName
								WHERE cat_id = $id
								LIMIT $from, $end");
			$row = $stmt->fetchAll();

			return $row;; 			
		}

	 	public function setImportant($listid)
		{	
			$array = explode(',', $listid);
			for ($i = 0; $i < count($array); $i++) {
				$data = array('important' => 1);
				$where = array('id = ?' => $array[$i]);
				$this->getDbTable()->update($data, $where);		
			}											
		}
		
		public function setNormal($listid)
		{	
			$array = explode(',', $listid);
			for ($i = 0; $i < count($array); $i++) {
				$data = array('important' => 0);
				$where = array('id = ?' => $array[$i]);
				$this->getDbTable()->update($data, $where);		
			}											
		}
		
		public function autoSuggestionArticle($title)
		{
			if (null == $title) exit();	
			
			$db = $this->getDbTable();			
			$select = $db->select()
						 ->from($db, array('title'))
			             ->where('title like ?', "%$title%")
			             ->limit(5, 0);			          
			                    
            return $db->fetchAll($select);            
		}
		
		public function searchArticle($title)
		{
			if (null == $title) exit();
			
			$db = Zend_DB_table_Abstract::getDefaultAdapter();							
			
			$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
					
			$categoryMapper = new Cloud_Model_ContentCategory_CloudContentCategoryMapper();
			$dbCategory= $categoryMapper->getDbTable()->info();
			$dbCategoryName = $dbCategory['name'];
				
			$select = $db->select()		                  
		                 ->from(array('a' => $dbArticleName))
		                 ->join(array('c' => $dbCategoryName), 'a.cat_id = c.id', array('name as cat_name'))
		                 ->where('title like ?', $title)
		                 ->order('a.create_date desc');		               		        	             
			    
           $rows = $db->fetchAll($select);           
           
           $paginator = Zend_Paginator::factory($rows);
    	   $paginator->setItemCountPerPage(5);
    	   $paginator->setCurrentPageNumber($page);           		
                     
           return $paginator;         
		}	
		
		public function showPaging($page, $key, $number)
        {            
           	$db = Zend_DB_table_Abstract::getDefaultAdapter();	
           	
           	$dbArticle = $this->getDbTable()->info();
			$dbArticleName = $dbArticle['name'];
			
			$stmt = $db->query("SELECT count(*) as count from articles
								WHERE title like '%$key%'");
			$row = $stmt->fetch();
									                                           
            $totalPages = ceil((int) $row['count'] / (int) $number);              
            return $this->paging(5, $totalPages,$page,$key);
        } 

		public function paging($pageCount, $totalPages, $currentPage, $key)
        {
            if ($totalPages < 1) return "";
            $currentURL = "news/index/search?key=$key";
                      
            if ($currentPage <= 0) $currentPage = 1;
            $querystring = "";
                  
            $nav = "";                                
            $prev = "";
            $next = "";
            $from = $currentPage - $pageCount + 1;
            if  ($from <= 1) $from = 1;         
            $to = $from + $pageCount;
            if ($to > $totalPages) $to = $totalPages;
                   
            if ($currentPage >= 1 && $currentPage < $totalPages) 
            {
                $nextPage = $currentPage + 1;
                $next = "<a href=\"$currentURL&page=$nextPage\" class=\"btnPaging\">Next</a>"; 
            }
            if ($currentPage > 1 && $currentPage <= $totalPages)
            {
                $prevPage =  $currentPage - 1;    
                $prev = "<a href=\"$currentURL&page=$prevPage\" class=\"btnPaging\">Previous</a>";   
            } 
            
            for ($i = $from; $i <= $to; $i++)
            {           
                if ($currentPage == $i) $nav .= "<span>{$i}</span>";
                else 
                {
                    $qt = "page={$i}";
                    $nav .= "<a href=\"$currentURL&{$qt}\">{$i}</a>";  
                }
            }
            return $prev.$nav.$next;
        }               
	}