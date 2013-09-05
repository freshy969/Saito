<?php

	class AllApiTest extends CakeTestSuite {

		public static function suite() {
			$testPath = App::pluginPath('Api') . 'Test' . DS;
			$suite = new CakeTestSuite('All Api tests.');
			$suite->addTestDirectoryRecursive($testPath . 'Case' . DS . 'Controller');
			return $suite;
		}

	}

