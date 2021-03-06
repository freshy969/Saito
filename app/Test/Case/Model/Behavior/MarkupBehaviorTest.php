<?php

	App::uses('MarkupBehavior', 'Model/Behavior');

	/**
	 * BbcodeBehavior Test Case
	 *
	 */
	class MarkupBehaviorTest extends CakeTestCase {

		/**
		 * setUp method
		 *
		 * @return void
		 */
		public function setUp() {
			parent::setUp();
			$this->Markup = new MarkupBehavior();
			new Saito\Markup\Settings([
				'server' => 'http://example.com',
				'webroot' => '/foo/',
				'hashBaseUrl' => 'hash/base/'
			]);
		}

		/**
		 * tearDown method
		 *
		 * @return void
		 */
		public function tearDown() {
			unset($this->Markup);

			parent::tearDown();
		}

		/**
		 * Test hashing of internal view links as string
		 *
		 * @return void
		 */
		public function testPrepareInputHashString() {
			$Model = $this->getMock('Model');
			$input = 'http://example.com/foo/hash/base/345';
			$result = $this->Markup->prepareMarkup($Model, $input);
			$expected = "#345";
			$this->assertEquals($result, $expected);

			$input = '[url=http://example.com/foo/hash/base/345]foo[/url]';
			$result = $this->Markup->prepareMarkup($Model, $input);
			$expected = $input;
			$this->assertEquals($result, $expected);
		}

	}
