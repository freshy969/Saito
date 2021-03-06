<?php

	use Saito\JsData;

	App::uses('Component', 'Controller');

	class JsDataComponent extends Component {

		protected $_JsData;

		public function startup(Controller $Controller) {
			$this->_JsData = JsData::getInstance();
		}

		public function __call($method, $params) {
			$proxy = array($this->_JsData, $method);
			if (is_callable($proxy)) {
				return call_user_func_array($proxy, $params);
			} else {
				return parent::__call($method, $params);
			}
		}

	}
