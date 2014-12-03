<?php
class App_NavigationService {
	
	protected $articles;
	
	protected $tags;
	
	protected $divination;
	
	protected $navigationPath;
	
	protected $staticNav;
	
	protected $themesService; 
	
	public function __construct(){
		$this->articles = new Application_Model_DbTable_ArticleTable();
		$this->tags = new Application_Model_DbTable_TagsTable();
		
		$this->divination = new Application_Model_DbTable_DivinationTable();
		
		$this->themesService = new App_PayserviceService();
		
		$this->navigationPath = APPLICATION_PATH . '/configs/nav.xml';
		$this->staticNav = APPLICATION_PATH . '/configs/static-nav.xml';
	}
	
	public function refreshNavigation(){
		$static = simplexml_load_file($this->staticNav);
		
		//articles
		$articles = $static->xpath('//articles');
		$articles = $articles[0];
		$articlesPages = $articles->pages;
		
		$tags = $this->getTags();
		foreach($tags as $tag){
			$rootTag =  $articlesPages->addChild('tag-'.$tag['alias'],'');
			$rootTag->addChild('label',$tag['tagname']);
			$rootTag->addChild('action','tag');
			$rootTag->addChild('controller','article');
			$rootTag->addChild('route','showArticlesByTagWithPage');
			$rootTag->addChild('id',$tag['alias']);
			$params = $rootTag->addChild('params','');
			$params->addChild('page','id');
			$params->addChild('tag','tag');
			$tagArticles = $this->getArticlesByTag($tag);
			if(count($tagArticles)){
				$pages = $rootTag->addChild('pages','');
			}
			foreach($tagArticles as $article){
				$tmpArticle = $pages->addChild('article-'.$article['alias'],'');
				$tmpArticle->addChild('label',$article['title']);
				$tmpArticle->addChild('action','content');
				$tmpArticle->addChild('controller','article');
				$tmpArticle->addChild('route','showPage');
				$tmpArticle->addChild('id',$article['alias']);
				$params = $tmpArticle->addChild('params','');
				$params->addChild('tag','tag');
				$params->addChild('alias','alias');
			}
		}
		
		//news
		$news = $static->xpath('//news');
		$news = $news[0];
		$newsPages = $news->pages;
		$tags = $this->getNewsTags();
		foreach($tags as $tag){
			$rootTag =  $newsPages->addChild('tag-'.$tag['alias'],'');
			$rootTag->addChild('label',$tag['tagname']);
			$rootTag->addChild('action','tag');
			$rootTag->addChild('controller','article');
			$rootTag->addChild('route','showNewsByTagWithPage');
			$rootTag->addChild('id',$tag['alias']);
			$params = $rootTag->addChild('params','');
			$params->addChild('page','id');
			$params->addChild('tag','tag');
			$tagArticles = $this->getNewsByTag($tag);
			if(count($tagArticles)){
				$pages = $rootTag->addChild('pages','');
			}
			foreach($tagArticles as $article){
				$tmpArticle = $pages->addChild('news-'.$article['alias'],'');
				$tmpArticle->addChild('label',$article['title']);
				$tmpArticle->addChild('action','content');
				$tmpArticle->addChild('controller','article');
				$tmpArticle->addChild('route','showPage');
				$tmpArticle->addChild('id',$article['alias']);
				$params = $tmpArticle->addChild('params','');
				$params->addChild('tag','tag');
				$params->addChild('alias','alias');
			}
		}
		
		$magic = $static->xpath('//magic');
		$magic = $magic[0];
		$magicPages = $magic->pages;
		$tags = $this->getMagicTags();
		//var_dump($tags); die;
		foreach($tags as $tag){
			$rootTag =  $magicPages->addChild('tag-'.$tag['alias'],'');
			$rootTag->addChild('label',$tag['tagname']);
			$rootTag->addChild('action','tag');
			$rootTag->addChild('controller','magic');
			$rootTag->addChild('route','showMagicByTagWithPage');
			$rootTag->addChild('id',$tag['alias']);
			$params = $rootTag->addChild('params','');
			$params->addChild('page','id');
			$params->addChild('tag','tag');
			$tagArticles = $this->getMagicByTag($tag);
			if(count($tagArticles)){
				$pages = $rootTag->addChild('pages','');
			}
			foreach($tagArticles as $article){
				$tmpArticle = $pages->addChild('magic-'.$article['alias'],'');
				$tmpArticle->addChild('label',$article['title']);
				$tmpArticle->addChild('action','content');
				$tmpArticle->addChild('controller','magic');
				$tmpArticle->addChild('route','showMagicPage');
				$tmpArticle->addChild('id',$article['alias']);
				$params = $tmpArticle->addChild('params','');
				$params->addChild('tag','tag');
				$params->addChild('alias','alias');
			}
		}
		
		//divinations taro
		$categories = $this->getDivCategories();
		//var_dump
		foreach($categories as $rootCategory){
			$item = null;
			$type = '';
			if($rootCategory['metadata']['cat_type'] == App_CategoryService::TARO_CATEGORY_TYPE){
				$item = $static->xpath('//gadaniya-taro');
				$type = 'taro';
			}
			if($rootCategory['metadata']['cat_type'] == App_CategoryService::CLASSIC_CATEGORY_TYPE){
				$item = $static->xpath('//gadaniya-classic');
				$type = 'classic';
			}
			if($rootCategory['metadata']['cat_type'] == App_CategoryService::RUNE_CATEGORY_TYPE){
				$item = $static->xpath('//gadaniya-rune');
				$type = 'rune';
			}
			if($rootCategory['metadata']['cat_type'] == App_CategoryService::BOOK_CATEGORY_TYPE){
				$item = $static->xpath('//gadaniya-book');
				$type = 'book';
			}
			if($rootCategory['metadata']['cat_type'] == App_CategoryService::OTHER_CATEGORY_TYPE){
				$item = $static->xpath('//gadaniya-other');
				$type = 'other';
			}
			//var_dump($item); //die;
			$item = $item[0];
			if($item){
				$itemPages = $item->pages;
				if($rootCategory['metadata']['cat_type'] == App_CategoryService::BOOK_CATEGORY_TYPE
					|| $rootCategory['metadata']['cat_type'] == App_CategoryService::OTHER_CATEGORY_TYPE)
				{
					$divinations = $this->getDivinationsByCategory($rootCategory['attr']['id']);
					//var_dump($divinations); die;
					if(count($divinations)){
						foreach($divinations as $divination){
							$rootTag = $itemPages->addChild('divination-'.$divination['alias'],'');
							$rootTag->addChild('label',$divination['name']);
							$rootTag->addChild('uri','/gadaniya/'.$type.'/'.$divination['alias']);
							$rootTag->addChild('id',$divination['alias']);
						}
					}
				}else{
					foreach($rootCategory['children'] as $child){
						//var_dump($child['data']);
						$rootTag = $itemPages->addChild('gadaniya-'.$type.'-'.App_UtilsService::generateTranslit($child['data']),'');
						$rootTag->addChild('label',$child['data']);
						$rootTag->addChild('id',$type.'-'.App_UtilsService::generateTranslit($child['data']));
						$rootTag->addChild('uri','/gadaniya/'.$type.'/'.App_UtilsService::generateTranslit($child['data']));
						$divinations = $this->getDivinationsByCategory($child['attr']['id']);
						//var_dump($divinations); die;
						if(count($divinations)){
							$pagesTag = $rootTag->addChild('pages');
							foreach($divinations as $divination){
								$divRoot = $pagesTag->addChild('divination-'.$divination['alias'],'');
								$divRoot->addChild('label',$divination['name']);
								$divRoot->addChild('action','divination');
								$divRoot->addChild('controller','divination');
								$divRoot->addChild('route','showDivination');
								$divRoot->addChild('id',$divination['alias']);
								$pagesParams = $divRoot->addChild('params','');
								$pagesParams->addChild('divtype','divtype');
								$pagesParams->addChild('alias','id');
								$pagesParams->addChild('divalias','id');
							}
						}
					}
				}
			}
			
		}
		
		
		//-------------------[payservice]------------------
		$horoscopeService = $static->xpath('//payservice-horoscope');
		$horoscopeService = $horoscopeService[0];
		$themes = $this->themesService->getThemesByType('horoscope');
		
		
		if(count($themes)){
			$payPages = $horoscopeService->addChild('pages');
			foreach($themes as $theme){
				$rootTag =  $payPages->addChild('theme-'.$theme['theme_smalltype'],'');
				$rootTag->addChild('label',$theme['theme_name']);
				$rootTag->addChild('id',$theme[id].'-'.$theme['theme_smalltype']);
				$rootTag->addChild('uri','/service/horoscope/'.$theme['theme_smalltype']);
			}
		}
		
		$divinationService = $static->xpath('//payservice-divination');
		$divinationService = $divinationService[0];
		$themes = $this->themesService->getThemesByType('divination');
		if(count($themes)){
			$payPages = $divinationService->addChild('pages');
			$themes = $this->themesService->getThemesByType('divination');
			//var_dump($tags); die;
			foreach($themes as $theme){
				$rootTag =  $payPages->addChild('theme-'.$theme['theme_smalltype'],'');
				$rootTag->addChild('label',$theme['theme_name']);
				$rootTag->addChild('id',$theme[id].'-'.$theme['theme_smalltype']);
				$rootTag->addChild('uri','/service/divination/'.$theme['theme_smalltype']);
			}
		}
		//-------------------------------------------------
		
		//die;
		//$taro = $static->xpath('//hadaniya-taro');
		//$taro = $taro[0];
		//$taroPages = $taro->pages;
		//$taroDivinations = $this->getTaroDivinations();
		
		$static->asXML($this->navigationPath);
	}
	
	private function getDivinationsByCategory($id){
		$query = $this->divination->getAdapter()->select();
		$query->from(array('divination'))
				->where('category_id=?',$id);
		$stmt = $query->query(Zend_Db::FETCH_ASSOC);
		$data = $stmt->fetchAll();
		return $data;
	}
	
	private function getDivCategories(){
		$categoryService = new App_CategoryService();
		$categories = $categoryService->structuredCategories();
		return $categories;
	}
	
	private function getArticlesByTag($tag){
		$query = $this->articles->getAdapter()->select();
		$subquery = $this->articles->getAdapter()->select();
		$subquery->from('article_tags',array('article_id'))->where($this->articles->getAdapter()->quoteInto('tag_id=?', $tag['id']));
		
		$query->from(array('a' => 'article'),
				array('title','alias','image','anonse','date_created','quicktag'))
				->where('a.type="a" AND a.activity="y" AND a.id IN ?',$subquery)
				->order('date_created DESC');
		//var_dump($query->assemble()); die;
		$stmt = $query->query(Zend_Db::FETCH_ASSOC);
		return $stmt->fetchAll();
	}
	
	private function getNewsByTag($tag){
		$query = $this->articles->getAdapter()->select();
		$subquery = $this->articles->getAdapter()->select();
		$subquery->from('article_tags',array('article_id'))->where($this->articles->getAdapter()->quoteInto('tag_id=?', $tag['id']));
	
		$query->from(array('a' => 'article'),
				array('title','alias','image','anonse','date_created','quicktag'))
				->where('a.type="n" AND a.activity="y" AND a.id IN ?',$subquery)
				->order('date_created DESC');
		$stmt = $query->query(Zend_Db::FETCH_ASSOC);
		return $stmt->fetchAll();
	}
	
	private function getMagicByTag($tag){
		$query = $this->articles->getAdapter()->select();
		$subquery = $this->articles->getAdapter()->select();
		$subquery->from('article_tags',array('article_id'))->where($this->articles->getAdapter()->quoteInto('tag_id=?', $tag['id']));
	
		$query->from(array('a' => 'article'),
				array('title','alias','image','anonse','date_created','quicktag'))
				->where('a.type="m" AND a.activity="y" AND a.id IN ?',$subquery)
				->order('date_created DESC');
		$stmt = $query->query(Zend_Db::FETCH_ASSOC);
		return $stmt->fetchAll();
	}
	
	private function getTags(){
		$cache = Zend_Registry::get('cache');
		if(! $tags = $cache->load('tags',true) ){
			$tags = $this->tags->fetchAll('type="a"')->toArray();
			$cache->save($tags,'tags');
		}
		return $tags;
	}
	
	private function getNewsTags(){
		$cache = Zend_Registry::get('cache');
		if(! $tags = $cache->load('newstags',true) ){
			$tags = $this->tags->fetchAll('type="n"')->toArray();
			$cache->save($tags,'newstags');
		}
		return $tags;
	}
	
	private function getMagicTags(){
		$cache = Zend_Registry::get('cache');
		if(! $tags = $cache->load('magictags',true) ){
			$tags = $this->tags->fetchAll('type="m"')->toArray();
			$cache->save($tags,'magictags');
		}
		return $tags;
	}
	
}