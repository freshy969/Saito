<?php

	App::uses('AppHelper', 'View/Helper');

	class CakeLogEntry {

		public function __construct($text) {
			$lines = explode("\n", trim($text));
			$_firstLine = array_shift($lines);
			preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) (.*?): (.*)/',
				$_firstLine,
				$matches);
			$this->_time = $matches[1];
			$this->_type = $matches[2];
			$this->_message = trim($matches[3]);
			if (empty($this->_message)) {
				$this->_message = array_shift($lines);
			}
			$this->_detail = implode($lines, '<br>');
		}

		public function time() {
			return $this->_time;
		}

		public function type() {
			return $this->_type;
		}

		public function message() {
			return $this->_message;
		}

		public function details() {
			return $this->_detail;
		}

	}

	class AdminHelper extends AppHelper {

		public $helpers = [
			'SaitoHelp',
			'Html',
			'TimeH'
		];

		public function help($id) {
			return $this->SaitoHelp->icon($id, ['style' => 'float: right;']);
		}

		protected function _cBadge($engine) {
			switch ($engine) {
				case 'File':
					$badge = 'warning';
					break;
				case 'Apc':
					$badge = 'success';
					break;
				default:
					$badge = 'info';
			}
			return $badge;
		}

		public function badge($text, $type = null) {
			if (is_callable([$this, $type])) {
				$badge = $this->$type($text);
			} elseif (is_string(($type))) {
				$badge = $type;
			} else {
				$badge = 'info';
			}
			return $this->Html->tag('span', $text, ['class' => "label label-$badge"]);
		}

		public function formatCakeLog($log) {
			$_nErrorsToShow = 20;
			$errors = preg_split('/(?=^\d{4}-\d{2}-\d{2})/m', $log, -1, PREG_SPLIT_NO_EMPTY);
			if (empty($errors)) {
				return '<p>' . __('No log file found.') . '</p>';
			}

			$out = '';
			$k = 0;
			$errors = array_reverse($errors);
			foreach ($errors as $error) {
				$e = new CakeLogEntry($error);
				$_i = self::tagId();
				$_details = $e->details();
				if (!empty($_details)) {
					$out .= '<button class="btn btn-mini" style="float:right;" onclick="$(\'#' . $_i . '\').toggle(); return false;">' . __('Details') . '</button>' . "\n";
				}
				$out .= '<pre style="font-size: 10px;">' . "\n";
				$out .= '<div class="row"><div class="span2" style="text-align: right">';
				$out .= $this->TimeH->formatTime($e->time(), 'eng');

				$out .= '</div>';
				$out .= '<div class="span7">';
				$out .= $e->message();
				if (!empty($_details)) {
					$out .= '<span id="' . $_i . '" style="display: none;">' . "\n";
					$out .= $_details;
					$out .= '</span>';
				}
				$out .= '</div></div>';
				$out .= '</pre>' . "\n";
				if ($k++ > $_nErrorsToShow) {
					break;
				}

			}
			return $out;
		}

		public function jqueryTable($selector, $sort) {
			$this->Html->script(
				'lib/datatables/media/js/jquery.dataTables.js',
				['inline' => false]
			);

			$script = <<<EOF
$(function() {
	$.extend( $.fn.dataTableExt.oStdClasses, {
			"sWrapper": "dataTables_wrapper form-inline"
	});
	var userTable = $('{$selector}').dataTable({
		 "sDom": "<'row'<'span4'l><'span6'f>r>t<'row'<'span4'i><'span6'p>>",
		 "iDisplayLength": 25,
		 "sPaginationType": "bootstrap"
		}).fnSort({$sort});
});
EOF;

			$this->Html->scriptBlock($script, ['inline' => false]);
		}

	}
