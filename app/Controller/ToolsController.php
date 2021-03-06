<?php

	App::uses('AppController', 'Controller');

/**
 * Tools Controller
 *
 * @property Tool $Tool
 */
	class ToolsController extends AppController {

		/**
		 * Empty out all caches
		 */
		public function admin_emptyCaches() {
			$this->CacheSupport->clear();
			$this->Session->setFlash(__('Caches cleared.'), 'flash/success');
			return $this->redirect($this->referer());
		}

		public function testJs() {
			if (Configure::read('debug') === 0) {
				echo 'Please activate debug mode.';
				exit;
			}

			$this->autoLayout = false;
		}

/**
 * Gives a deploy script a mean to empty PHP's APC-cache
 *
 * @link https://github.com/jadb/capcake/wiki/Capcake-and-PHP-APC>
 */
		public function clearCache() {
			if (in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
				$this->CacheSupport->clear(['Apc', 'OpCache']);
				echo json_encode(['Opcode Clear Cache' => true]);
			}
			exit;
		}

		public function beforeFilter() {
			parent::beforeFilter();
			$this->Auth->allow(
				'clearCache',
				'testJs',
				'langJs'
			);
		}

	}