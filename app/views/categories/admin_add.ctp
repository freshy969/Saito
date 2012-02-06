<?php $this->Html->addCrumb(__('Categories', true), '/admin/categories'); ?>
<?php $this->Html->addCrumb(__('Add Category',
				true), '/admin/categories/add'); ?>

<div class="categories form">
	<?php echo $this->Form->create('Category'); ?>
	<fieldset>
		<legend><?php __('Add Category'); ?></legend>
		<?php
//		echo $this->Form->input('category_order');
		echo $this->Form->input('category', array( 'label' => 'Title' ));
		echo $this->Form->input('description');
		echo $this->Form->input('accession',
				array(
						'label' => __('Accession', true),
				'options' =>
				array(
						0 => __('Anonymous', true),
						1 => __('User', true),
						2 => __('Mod', true),
				)
				)
		);
		?>
	</fieldset>
	<?php echo $this->Form->submit(null,
			array( 'class' => 'btn btn-primary' )); ?>
	<?php echo $this->Form->end(); ?>
</div>