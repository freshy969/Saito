<?php

	App::uses('BbcodeProcessorInterface', 'Lib/Bbcode/Processors');

	class BbcodeQuotePostprocessor extends BbcodeProcessor {

		public function process($string) {
			$quoteSymbolSanitized = h($this->_sOptions['quote_symbol']);
			$string = preg_replace(
			// Begin of the text or a new line in the text, maybe one space afterwards
				'/(^|\n\r\s?)' .
				$quoteSymbolSanitized .
				'\s(.*)(?!\<br)/m',
				"\\1<span class=\"c-bbcode-citation\">" . $quoteSymbolSanitized . " \\2</span>",
				$string
			);
			return $string;
		}

	}
