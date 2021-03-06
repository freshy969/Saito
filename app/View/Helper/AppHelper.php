<?php
	/**
	 * Application level View Helper
	 *
	 * This file is application-wide helper file. You can put all
	 * application-wide helper-related methods here.
	 *
	 * PHP 5
	 *
	 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
	 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice.
	 *
	 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
	 * @link          http://cakephp.org CakePHP(tm) Project
	 * @package       app.View.Helper
	 * @since         CakePHP(tm) v 0.2.9
	 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
	 */

	App::uses('Helper', 'View');

/**
 * Application helper
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       app.View.Helper
 */
	class AppHelper extends Helper {

		protected static $_tagId = 0;

		public function __get($name) {
			switch ($name) {
				case 'dic':
					return ClassRegistry::getObject('dic');
				default:
					return parent::__get($name);
			}
		}

		public static function tagId() {
			return 'id' . static::$_tagId++;
		}

		/**
		 * Returns the unix timestamp for a file
		 *
		 * @param $path as url `m/dist/theme.css
		 * @return int
		 * @throws InvalidArgumentException
		 */
		public function getAssetTimestamp($path) {
			$pathWithTimestamp = $this->assetTimestamp($path);
			// extracts integer unixtimestamp from `path/asset.ext?<unixtimestamp>
			if ($pathWithTimestamp) {
				if (preg_match('/(?<=\?)[\d]+(?=$|\?|\&)/', $pathWithTimestamp, $matches)) {
					return (int)$matches[0];
				}
			}
			throw new InvalidArgumentException("File $path not found.");
		}

	}