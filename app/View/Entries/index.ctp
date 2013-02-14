<?php echo Stopwatch::start('entries/index'); ?>
<?php
  $this->start('headerSubnavLeft');
  echo $this->Html->link(
      '<i class="icon-plus"></i>&nbsp; ' . __('new_entry_linkname'),
      '/entries/add',
      array( 'class' => 'textlink', 'escape' => FALSE ));
  $this->end();

	$this->start('headerSubnavCenter');
		if ($CurrentUser->isLoggedIn()) :
			echo $this->Html->link(
				'<i class="icon-refresh"></i>',
				'#',
				array(
					'id' => 'btn-manuallyMarkAsRead',
					'escape' => false,
					'class' => 'btn-hf-center shp shp-bottom',
					'data-title' => __('Help'),
					'data-content' => __('btn_manualy_mark_as_read_shp'),
				)
			);
		endif;
	$this->end();

	$this->start('headerSubnavRightTop');
	if (isset($categoryChooserIsUsed) && $categoryChooserIsUsed):
		// category-chooser link
		echo $this->Html->link(
				'<i class="icon-tags"></i> ' . ((isset($categoryChooser[$categoryChooserTitleId])) ? $categoryChooser[$categoryChooserTitleId] : $categoryChooserTitleId) . '&nbsp;',
				'#', array(
				'id'		 => 'btn-category-chooser',
				'escape' => false,
				)
		);
		echo $this->element('entry/category-chooser');
		$chooser_title = __('Categories');
		$this->Js->buffer(<<<EOF
			$('#category-chooser').dialog({
				autoOpen: false,
				show: {effect: "scale", duration: 200},
				hide: {effect: "fade", duration: 200},
				width: 400,
				position: [$('#btn-category-chooser').offset().left + $('#btn-category-chooser').width() - $(window).scrollLeft() - 410, $('#btn-category-chooser').offset().top - $(window).scrollTop() + $('#btn-category-chooser').height()],
				title: "$chooser_title",
				resizable: false
				});

EOF
		);
		$this->Js->get('#btn-category-chooser')->event('click',
				<<<EOF
			if ($('#category-chooser').is(":visible")) {
				$('#category-chooser').dialog('close');
			} else {
				$('#category-chooser').dialog('open');
			}
EOF
		);
	endif;
	$this->end();
?>

<div id="entry_index" class="entry index">
	<?php echo $this->element('entry/thread_cached_init', array ( 'entries_sub' => $entries, 'level' => 0 )) ; ?>
</div>
<?php echo Stopwatch::stop('entries/index');?>