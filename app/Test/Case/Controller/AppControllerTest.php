<?php

	App::uses('Controller', 'Controller');
	App::uses('AppController', 'Controller');

	class AppControllerTest extends \Saito\Test\ControllerTestCase {

		public $fixtures = [
			'app.bookmark',
			'app.category',
			'app.entry',
			'app.esevent',
			'app.esnotification',
			'app.setting',
			'app.shout',
			'app.smiley',
			'app.smiley_code',
			'app.upload',
			'app.user',
			'app.user_block',
			'app.user_ignore',
			'app.user_online',
			'app.user_read'
		];

		/**
		 * Test empty title_for_layout
		 */
		public function testSetTitleForLayoutEmpty() {
			$result = $this->testAction('/entries/index',
				['method' => 'GET', 'return' => 'vars']);
			$this->assertEquals($result['title_for_layout'], 'Forum – macnemo');
			$this->assertEquals($result['title_for_page'], 'Forum');
			$this->assertEquals($result['forum_name'], 'macnemo');
		}

		/**
		 * test nonempty title_for_layout
		 */
		public function testSetTitleForLayoutNotEmpty() {
			$result = $this->testAction('/entries/view/1',
				['method' => 'GET', 'return' => 'vars']);
			$this->assertEquals($result['title_for_layout'],
				'First_Subject | Ontopic – macnemo');
		}

		/**
		 * test empty title for layout with page_titles.po set
		 */
		public function testSetTitleForLayoutPoFile() {
			$result = $this->testAction('/users/register',
				['method' => 'GET', 'return' => 'vars']);
			$this->assertEquals($result['title_for_layout'], 'Register – macnemo');
		}

		public function testLocalReferer() {
			$this->testAction('/entries/index');

			$_SERVER['HTTP_REFERER'] = FULL_BASE_URL . $this->controller->webroot . '/entries/index';
			$result = $this->controller->localReferer();
			$expected = '/entries/index';
			$this->assertEquals($expected, $result);

			$_SERVER['HTTP_REFERER'] = FULL_BASE_URL . $this->controller->webroot . '/entries/view';
			$result = $this->controller->localReferer('action');
			$expected = 'view';
			$this->assertEquals($expected, $result);

			$_SERVER['HTTP_REFERER'] = FULL_BASE_URL . $this->controller->webroot . '/some/path';
			$result = $this->controller->localReferer('controller');
			$expected = 'some';
			$this->assertEquals($expected, $result);

			$_SERVER['HTTP_REFERER'] = FULL_BASE_URL . $this->controller->webroot . '/some/';
			$result = $this->controller->localReferer('action');
			$expected = 'index';
			$this->assertEquals($expected, $result);

			$_SERVER['HTTP_REFERER'] = FULL_BASE_URL . $this->controller->webroot . '';
			$result = $this->controller->localReferer('action');
			$expected = 'index';
			$this->assertEquals($expected, $result);

			$_SERVER['HTTP_REFERER'] = FULL_BASE_URL . $this->controller->webroot . '';
			$result = $this->controller->localReferer('controller');
			if (Configure::read('Saito.installed')) :
				$expected = 'entries';
			else:
				$expected = 'install';
			endif;
			$this->assertEquals($expected, $result);

			//* external referer
			$_SERVER['HTTP_REFERER'] = 'http://heise.de/foobar/baz.html';
			$result = $this->controller->localReferer('controller');
			if (Configure::read('Saito.installed')) :
				$expected = 'entries';
			else:
				$expected = 'install';
			endif;
			$this->assertEquals($expected, $result);

			$_SERVER['HTTP_REFERER'] = 'http://heise.de/foobar/baz.html';
			$result = $this->controller->localReferer('action');
			$expected = 'index';
			$this->assertEquals($expected, $result);
		}

		public function testCurrentUser() {
			//* check there's no current user
			$result = $this->testAction('/entries/index',
				['method' => 'GET', 'return' => 'vars']);

			$this->assertTrue(is_null($result['CurrentUser']->getId()));
			$this->assertFalse($result['CurrentUser']->isLoggedIn());

			//* loginUser
			$Entries = $this->generate('Entries');
			$this->_loginUser(3);
			$result = $this->testAction('/entries/index',
				['method' => 'GET', 'return' => 'vars']
			);
			$this->assertEquals($result['CurrentUser']->getId(), 3);
			$this->assertTrue($result['CurrentUser']->isLoggedIn());
		}

	}
