<?php
class DivinationController extends App_Controller_Action_ParentController{
	
	protected $divinationService;
	
	protected $deckService;
	
	protected $categoryService;
	
	protected $profileService;
	
	public function init(){
		$this->divinationService = new App_DivinationService();
		$this->deckService = new App_DeckService();
		$this->categoryService = new App_CategoryService();
		$this->profileService = new App_ProfileService($this->view->userdata);
	}
	
	public function divListAction(){
		$divtype = $this->_getParam('divtype',false);
		$divtypes = $this->categoryService->getCategoryTypes();
		$inArray = false; $divtypeId = 0;
		if(count($divtypes)){
			foreach ($divtypes as $item){
				if($item['type'] == $divtype){
					$inArray = true;
					$divtypeId = $item['id'];
				}
			}
		}
		if($divtype && $inArray){
			$navItem = $this->view->navigation()->findOneById($divtype.'-list');
			if($navItem){
				$navItem->setActive('true');
			}
			$this->view->divType = $divtype;
			
			$divTypeRu = '';
			if($divtype == 'taro'){
				$this->view->pageTitle = 'Гадания на картах Таро';
			}
			if($divtype == 'classic'){
				$this->view->pageTitle = 'Гадания на класических картах';
			}
			if($divtype == 'rune'){
				$this->view->pageTitle = 'Гадания на Рунах';
			}
			if($divtype == 'book'){
				$this->view->pageTitle = 'Гадания по Книге перемен И-цзин';
			}
			if($divtype == 'other'){
				$this->view->pageTitle = 'Другие гадания';
			}
			
			$this->view->sliderExist = true;
			$this->prepareData($divtype,$divtypeId);
			
			if($divtype != 'book' && $divtype != 'other'){
				$this->view->seotitle = 'Гадания::'.$this->view->data['root-category']['name'];
				$this->view->seokeywords = $this->view->data['root-category']['seo-keywords'];
				$this->view->seodescription = $this->view->data['root-category']['seo-description'];
			}else{
				$this->view->seotitle = 'Гадания::'.$this->view->data['name'];
				$this->view->seokeywords = $this->view->data['seo-keywords'];
				$this->view->seodescription = $this->view->data['seo-description'];
			}
			//$this->data['root-category']['description'];
			//echo '<pre>';
			//var_dump($this->view->data); die;
			$this->render($divtype.'-list');
		}else{
			throw new Zend_Controller_Action_Exception('Что то пошло не так.. Страница не найдена!', 404);
		}
	}
	
	public function dayDescriptionAction(){
		$type = $this->_getParam('type',false);
		if(in_array($type,array('taro','rune','hexagramm'))){
			$this->view->dayType = $type;
			$indexService = new App_IndexService();
			if($type == 'taro'){
				$this->view->pageTitle = 'Таро карта дня';
				$this->view->smallPageTitle = 'Ваша карта дня';
				$this->view->taroDayData = $indexService->taroDay($this->view->taroDay,$this->view->taroDayState);
			}
			if($type == 'rune'){
				$this->view->pageTitle = 'Руна дня';
				$this->view->smallPageTitle = 'Ваша руна дня';
				$this->view->runeDayData = $indexService->runeDay($this->view->runeDay,$this->view->runeDayState);
			}
			if($type == 'hexagramm'){
				$this->view->pageTitle = 'Гексаграмма дня';
				$this->view->smallPageTitle = 'Ваша гексаграмма дня';
				$this->view->hexagrammDayData = $indexService->hexagrammDay($this->view->hexagrammDay);
			}
			$navItem = $this->view->navigation()->findOneById($type.'-day');
			if($navItem){
				$navItem->setActive('true');
			}
		}else{
			throw new Zend_Controller_Action_Exception('Что то пошло не так.. Страница не найдена!', 404);
		}
	}
	
	protected function prepareData($divtype,$divId){
		$this->view->data = $this->divinationService->getListDivinationsWithCategories($divtype,$divId);
		
	}
	
	public function categoryDivinationsAction(){
		$divtype = $this->_getParam('divtype',false);
		$alias = $this->_getParam('alias',false);
		$divtypes = $this->categoryService->getCategoryTypes();
		$inArray = false; $divtypeId = 0;
		if(count($divtypes)){
			foreach ($divtypes as $item){
				if($item['type'] == $divtype){
					$inArray = true;
					$divtypeId = $item['id'];
				}
			}
		}
		//var_dump($divtypeId); die;
		if($divtype && $alias && $inArray){
			$navItem = $this->view->navigation()->findOneById($divtype.'-'.$alias);
			//var_dump($navItem); die;
			if($navItem){
				$navItem->setActive('true');
			}
			$this->view->divType = $divtype;
			$this->prepareCategoryData($divtype,$divtypeId,$alias);
			//echo '<pre>';
			//var_dump($this->view->data);
			//die;
			$this->view->seotitle = 'Гадания::'.(App_DivinationService::getDivTypeRu($divtype)).'::'.$this->view->data['name'];
			$this->view->seokeywords = $this->view->data['seo-keywords'];
			$this->view->seodescription = $this->view->data['seo-description'];
			
			$this->render($divtype.'-category');
		}else{
			throw new Zend_Controller_Action_Exception('Что то пошло не так.. Страница не найдена!', 404);
		}
		
	}
	
	protected function prepareCategoryData($divtype,$divId, $alias){
		$data = $this->divinationService->getListDivinationsWithCategories($divtype,$divId);
		//echo '<pre>';
		//var_dump($data); die;
		
		$category = null;
		foreach($data as $item){
			if($item['alias'] == $alias){
				$category = $item;
			}
		}
		if(null === $category ){
			throw new Zend_Controller_Action_Exception('Что то пошло не так.. Страница не найдена!', 404);
		}
		$this->view->pageTitle = $category['name'];
		$this->view->data = $category;//$this->divinationService->getListDivinationsWithCategories($divtype,$divId);
		//var_dump($this->view->data); die;
	}
	
	//view single divination
	public function divinationAction(){
		$divtype = $this->_getParam('divtype',false);
		$alias = $this->_getParam('alias',false);
		$divalias = $this->_getParam('divalias',false);
		
		$divtypes = $this->categoryService->getCategoryTypes();
		$inArray = false; $divtypeId = 0;
		if(count($divtypes)){
			foreach ($divtypes as $item){
				if($item['type'] == $divtype){
					$inArray = true;
					$divtypeId = $item['id'];
				}
			}
		}
		if($divtype && $divalias && $alias && $inArray){
			$divination = $this->divinationService->getDivinationByAlias($divalias);//DivinationById($id);
			if($divination['activity'] == 'n'){
				throw new Zend_Controller_Action_Exception('Что то пошло не так.. Страница не найдена!', 404);
			}
			if(isset($this->view->userdata)){
				$divination['is_favorite'] = $this->profileService->isFavorite($divination['id'], 'divination', $this->view->userdata->id);
			}else{
				$divination['is_favorite'] = false;
			}
			//var_dump($this->view->userdata->id); die;
			$this->view->divination = $divination;
			$this->view->pageTitle = $divination['name'];
			
			$this->view->divType = $divtype;
			$decks = $this->deckService->getDecksByDivination($divination['id']);
			foreach($decks as &$deck){
				unset($deck['id']);
			}
			$this->view->decks = $decks;
			$this->view->divinationNet = $this->divinationService->getCardsByDivinationId($divination['id']);
			
			//var_dump($this->view->divinationNet); die;
			$haveNotParticipatedCards = 'false';
			$notParticipatedCards = 0;
			if($divination['type'] == 'classic'){
				foreach ($this->view->divinationNet as $index => $card){
					if($card['participation'] == 'n'){
						$haveNotParticipatedCards = 'true';
						$notParticipatedCards++;
					}
				}
			}
			
			$this->view->divinationsList = $this->divinationService->getOtherDivinationsInCategory($divination['category_id']);
			//echo '<pre>';
			//var_dump($this->view->divinaionsList); die;
			
			$this->view->haveNotParticipatedCards = $haveNotParticipatedCards;
			$this->view->notParticipatedCards = $notParticipatedCards;
			
			if($divtype != 'book' && $divtype != 'other'){
				$navItem1 = $this->view->navigation()->findOneById($divtype.'-'.$alias);
				//$navItem1->setActive('true');
				
				if($navItem1){
					$navItem2 = $navItem1->findOneById($divalias);
					//var_dump($navItem2); die;
					if($navItem2){
						$navItem2->setActive('true');
					}
				}
			}else{
				//var_dump($divalias); die;
				$navItem = $this->view->navigation()->findOneById($divalias);
				if($navItem){
					$navItem->setActive('true');
				}
			}
			
			$this->view->seotitle = 'Гадания::'.(App_DivinationService::getDivTypeRu($divtype)).'::'.$divination['name'];
			$this->view->seokeywords = $divination['seokeywords'];
			$this->view->seodescription = $divination['seodescription'];
			
			$this->view->attributes = array(
				'type' => 'divination',
				'subtype' => '',
				'sign' => '',
				'resource_id' => $divination['id'] 
			);
			$this->view->comments = $this->commentsService->getComments('divination', '', '', $divination['id']);
			//var_dump($this->view->divination); die;
		}else{
			throw new Zend_Controller_Action_Exception('Что то пошло не так.. Страница не найдена!', 404);
		}
	}
	
	public function getCardDescriptionAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$divId = $this->_getParam('divination_id',false);
		$cardOrder = preg_replace('#(<.*?>)#ims','',$this->_getParam('card_order',false));
		$cardNumber = preg_replace('#(<.*?>)#ims','',$this->_getParam('card_number',false));
		$cardSide = $this->_getParam('side',false);
		//$isSign = $this->_getParam('is_sign',false);
		$this->view->cardDeck = preg_replace('#(<.*?>)#ims','',$this->_getParam('deck',false));
		
		if($divId){
			if($cardOrder && is_numeric($cardOrder)){
				$this->view->cardOrder = $cardOrder;
			}
			$json = array();
			$divination = $this->divinationService->getDivinationById($divId);
			$cardsData = $this->divinationService->getCardsByDivinationId($divId);
			$data = $this->divinationService->getDivinationDataItemByPosition($cardNumber, $divId);//ivinationDataById($dataid);
			
			if($cardSide == 'normal'){
				$this->view->cardDescription = $data['description'];
				$this->view->isReverse = false;
				$this->view->cardImage = $cardNumber.'.png';
				$this->view->cardHeader = $data['title'];
			}elseif($cardSide == 'reverse'){
				$this->view->cardDescription = $data['description_reverse'];
				$this->view->isReverse = true;
				$this->view->cardImage = $cardNumber.'_0.png';
				$this->view->cardHeader = $data['title_reverse'];
			}
			foreach($cardsData as $item){
				if( ($cardOrder+1) == $item['alignment_position'] ){
					$this->view->orderDescription = $item['position_desc'];
					break;
				} 
			}
			$this->render('divination-description-item');
		}
	}
	public function getBookDescriptionAction(){
		$this->_helper->layout->disableLayout();
		$divId = $this->_getParam('divination_id',false);
		$hex = $this->_getParam('hexagramm',false);
		if($divId && $hex){
			$divination = $this->divinationService->getDivinationById($divId);
			$this->view->deck = App_UtilsService::generateTranslit($divination['name']);
			$this->view->item = $this->divinationService->getBookDescriptionItemByHex($divId,$hex);
			$this->render('divination-book-description-item');
		}
	}
	
	public function getOtherDescriptionAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$divId = $this->_getParam('divination_id',false);
		$order = $this->_getParam('order',false);
		if($divId && $order){
			$item = $this->divinationService->getOtherDescriptionItemByOrder($divId,$order);
			echo Zend_Json::encode($item);
		}
	}
	
	public function voteAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$id = $this->_getParam('id',false);
		if($id && $this->getRequest()->isPost()){
			if(!isset($_COOKIE['votediv_'.$id])){
				setcookie('votediv_'.$id, 'vote', time() + 3600*24*14, '/');
				$this->divinationService->setVote($id);
			}
		}
	} 
	
	public function postDispatch(){
		
	}
}