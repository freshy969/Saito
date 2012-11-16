<div id="user_contact" class="user contact">
	<div class="box-form">
		<div class="l-box-header box-header">
			<div>
				<div class='c_first_child'></div>
				<div><h2><?php echo __('user_contact_title', $this->request->data['User']['username']); ?> </h2></div>
				<div class='c_last_child'></div>
			</div>
		</div>
		<div class="content">
			<?php echo $this->Form->create(false); ?>
			<div class="required">
				<?php
					if (!$CurrentUser->isLoggedIn()) {
						echo $this->Form->label('Message.sender_contact', __('user_contact_sender-contact'));
						echo $this->Form->text('Message.sender_contact');
					}
					echo $this->Form->label('Message.subject', __('user_contact_subject'));
					echo $this->Form->text('Message.subject');
				?>
			</div>
			<?php echo $this->Form->label('Message.text', __('user_contact_message')); ?>
			<?php echo $this->Form->textarea('Message.text', array('style' => 'height: 10em')); ?>
			<div>
			<?php
				$checked = true;
				if (isset($this->request->data['Message']['carbon_copy'])) {
					$checked = $this->request->data['Message']['carbon_copy'];
				}
				echo $this->Form->checkbox('Message.carbon_copy', array(
						'checked' => $checked
				));
				echo $this->Form->label('Message.carbon_copy', __('user_contact_send_carbon_copy'));
			?>
			</div>
			<div>
			<?php
				echo $this->Form->submit(__('Submit'), array(
					'class' => 'btn btn-submit'
					)); ?>
			</div>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>