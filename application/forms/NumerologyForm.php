<?php
class Application_Form_NumerologyForm extends Zend_Form{
	
	protected $userdata;
	
	
	public function __construct($options=null){
		parent::__construct($options);
	}
	
	public function setUserdata($userdata){
		$this->userdata = $userdata;
	}
	
	public function init(){
		
	}
	
	public function startform(){
		//var_dump($this->_userdata); die;
		$translate = new Zend_Translate('array',APPLICATION_PATH.'/languages/resources/');
		Zend_Registry::set('Zend_Translate',$translate);
		
		$this->clearDecorators();
		$this->setDecorators(array(
				//'PrepareElements',
				'ViewScript',
				array('ViewScript', array('viewScript' => 'partials/forms/numerology-form.phtml',
						'userdata' => $this->userdata)),
		));
		
		//var_dump($userdata); die;
		$this->setName('lifepath');
		$this->setMethod('post');
		
		$fname = new Zend_Form_Element_Text('fname');
		$fname	->addFilter('StripTags')
		->setLabel('1')
		->addFilter('StringTrim')
		->setRequired(true)
		->setValidators(array('NotEmpty'))
		->setDecorators(array(
				'ViewHelper',
				'Errors',
		));
		
		$mname = new Zend_Form_Element_Text('mname');
		$mname->setLabel('1')
		->addFilter('StripTags')
		->addFilter('StringTrim')
		->setRequired(true)
		->setValidators(array('NotEmpty'))
		->setDecorators(array(
				'ViewHelper',
				'Errors',
		));
		
		$lname = new Zend_Form_Element_Text('lname');
		$lname	->addFilter('StripTags')
		->setLabel('1')
		->addFilter('StringTrim')
		->setRequired(true)
		->setValidators(array('NotEmpty'))
		->setDecorators(array(
				'ViewHelper',
				'Errors',
		));
		
		$day = new Zend_Form_Element_Select('bday');
		$day->setRequired(true)
		->setValidators(array('NotEmpty'))
		->setRegisterInArrayValidator(false)
		->setDecorators(array(
				'ViewHelper',
				'Errors',
		))
		->addMultioptions(array('' => 'день'));
		
		$month = new Zend_Form_Element_Select('bmonth');
		$month->setRequired(true)
		->setValidators(array('NotEmpty'))
		->setDecorators(array(
				'ViewHelper',
				'Errors',
		));
		
		$year = new Zend_Form_Element_Select('byear');
		$year->setRequired(true)
		->setValidators(array('NotEmpty'))
		->setDecorators(array(
				'ViewHelper',
				'Errors',
		));
		
		$smalltype = new Zend_Form_Element_Hidden('smalltype');
		$smalltype->setDecorators(array(
				'ViewHelper',
		));
		
		$submit = new Zend_Form_Element_Button('submit');
		$submit->setLabel('Отправить данные');
		$submit->setDecorators(array(
				'ViewHelper',
		));
		
		$this->addElements(array($fname,$mname,$lname,$year,$month,$day,$smalltype,$submit));
		$this->fillMonthes();
		$this->fillYears();
	}
	
	private function fillYears(){
		$years = array('' => 'год');
		for($i = date('Y',strtotime(date('Y').' + 10 year')); $i > 1930; $i--){
			$years[$i] = $i;
		}
		$this->byear->addMultiOptions($years);
	}
	
	private function fillMonthes(){
		$monthes = array(
				'' => 'месяц',
				'01' => 'Январь',
				'02' => 'Февраль',
				'03' => 'Март',
				'04' => 'Апрель',
				'05' => 'Май',
				'06' => 'Июнь',
				'07' => 'Июль',
				'08' => 'Август',
				'09' => 'Сентябрь',
				'10' => 'Октябрь',
				'11' => 'Ноябрь',
				'12' => 'Декабрь'
		);
		$this->bmonth->addMultiOptions($monthes);
	}
}