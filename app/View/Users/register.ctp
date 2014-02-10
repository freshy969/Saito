<div class="panel">
		<?= $this->Layout->panelHeading(__('register_linkname'),
				['pageHeading' => true]) ?>
	<div class="panel-content panel-form">
	<?php if ($register_success == 'email_send') : ?>
			<?php
				echo $this->element(
						'users'
						. DS . Configure::read('Config.language')
						. DS . 'register-email_send');
			?>
		<?php elseif ($register_success == 'success') : ?>
			<?php echo __('register_success_content'); ?>
		<?php else : ?>
			<?php
			echo $this->Html->tag('p', __('js-required'),
					array(
					'id'		 => 'register-js-required',
					'class'	 => 'message',
			));
			echo $this->Html->scriptBlock('$("#register-js-required").hide();', array('inline' => true));
			?>
			<?php
			echo $this->Form->create('User', array('action' => 'register'));
			echo $this->element('users/add_form_core');
			echo $this->SimpleCaptcha->input('User',
					array(
					'error' => array(
							'captchaResultIncorrect' => __d('simple_captcha',
									'Captcha result incorrect'),
							'captchaResultTooLate'	 => __d('simple_captcha',
									'Captcha result too late'),
							'captchaResultTooFast'	 => __d('simple_captcha',
									'Captcha result too fast'),
					),
							'div' => ['class' => 'input required'],
					)
			);
			if (Configure::read('Saito.Settings.tos_enabled')):
				// set tos url
				$tos_url = Configure::read('Saito.Settings.tos_url');
				if (empty($tos_url)) {
					$tos_url = '/pages/' . Configure::read('Config.language') . '/tos';
				};

				echo $this->Form->input('tos_confirm',
						array(
						'type' => 'checkbox',
						'div'	 => array('class'	 => 'input password required'),
						'label'	 => __('register_tos_label',
								$this->Html->link(__('register_tos_linktext'),
										$tos_url,
										array(
										'target' => '_blank',
										)
								)
						)
				));
				echo $this->Js->get('#UserTosConfirm')->event('click',
					<<<EOF
if (this.checked) {
	$('#btn-register-submit').removeAttr("disabled");
} else {
	$('#btn-register-submit').attr("disabled", true);
}
return true;
EOF
				);
			endif;

			echo $this->Form->submit(__('register_linkname'),
					array(
					'id'			 => 'btn-register-submit',
					'class'		 => 'btn btn-submit',
					'disabled' => Configure::read('Saito.Settings.tos_enabled') ? 'disabled' : '',
			));
			echo $this->Form->end();
			?>
<?php endif; ?>
	</div>
</div>