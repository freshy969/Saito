<?php
  $this->start('headerSubnavLeft');
  echo $this->Layout->navbarBack('/bookmarks/index/#' .
    $this->request->data['Entry']['id']);
  $this->end();
?>
<div class="panel">
	<?= $this->Layout->panelHeading(__('Edit Bookmark'),
		['pageHeading' => true]) ?>
	<div class="panel-content">
		<?= $this->element('/entry/view_content', ['entry' => $entry]); ?>
	</div>
	<div class="panel-footer panel-form">
		<?php
			echo $this->Form->create();
			echo $this->Html->div('input textarea',
					$this->Form->textarea('comment', array(
							'maxlength' 	=> 255,
							'columns'   	=> 80,
							'rows'				=> 3,
							'placeholder' => __('Enter your comment here'),
			)));
			echo $this->Form->submit(__('btn-comment-title'), array(
					'class' => 'btn btn-submit',
			));
			echo $this->Form->end();
		?>
	</div>
</div>