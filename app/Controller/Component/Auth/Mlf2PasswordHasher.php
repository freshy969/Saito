<?php

	App::uses('AbstractPasswordHasher', 'Controller/Component/Auth');

	/**
	 * mylittleforum 2.x salted sha1 passwords
	 */
	class Mlf2PasswordHasher extends AbstractPasswordHasher {

		public function hash($password) {
			// compare to includes/functions.inc.php generate_pw_hash() mlf 2.3
			$salt           = self::_generateRandomString(10);
			$salted_hash    = sha1($password . $salt);
			$hash_with_salt = $salted_hash . $salt;
			return $hash_with_salt;
		}

		public function check($password, $hash) {
			$out = false;
			// compare to includes/functions.inc.php is_pw_correct() mlf 2.3
			$salted_hash = substr($hash, 0, 40);
			$salt        = substr($hash, 40, 10);
			if (sha1($password . $salt) == $salted_hash) :
				$out = true;
			endif;
			return $out;
		}

		protected static function _generateRandomString($max_length = null) {
			$string = Security::generateAuthKey();
			if ($max_length) {
				$string = substr($string, 0, $max_length);
			}
			return $string;
		}
	}
