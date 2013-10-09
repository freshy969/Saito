<?php

	App::uses('AppHelper', 'View/Helper');

	class ShoutsHelper extends AppHelper {

		public $helpers = [
			'Bbcode'
		];

		protected $_cacheKey = 'Saito.Shouts.prepared';

		function prepare($shouts) {
			$lastId = $shouts[0]['Shout']['id'];
			$cache = $this->_readCache($lastId);
			if ($cache) {
				return $cache;
			}

			$prepared = [];
			foreach ($shouts as $shout) {
				$prepared[] = [
					'id' => $shout['Shout']['id'],
					'time' => $shout['Shout']['time'],
					'text' => $shout['Shout']['text'],
					'html' => $this->Bbcode->parse(
						$shout['Shout']['text'],
						['multimedia' => false]
					),
					'user_id' => $shout['Shout']['user_id'],
					'user_name' => $shout['Shout']['username']
				];
			}

			$this->_writeCache($prepared);
			return $prepared;
		}

		protected function _readCache($lastId) {
			$cache = Cache::read($this->_cacheKey);
			if ($cache && $cache['lastId'] === $lastId) {
				return $cache['data'];
			}
			return false;
		}

		protected function _writeCache($prepared) {
			$lastId = $prepared[0]['id'];
			Cache::write(
				$this->_cacheKey,
				[
					'lastId' => $lastId,
					'data' => $prepared
				]
			);
		}

	}