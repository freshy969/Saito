	<?php
		echo $this->element('layout/html_header', array( 'cache' => '+1 day' ));
	?>
	</head>
	<body>
		<?php if (!$CurrentUser->isLoggedIn() && $this->params['action'] != 'login') { ?>
		<div id='modalLoginDialog' style='height: 0px; overflow: hidden;'><?php echo $this->element('users/login_form'); ?></div>
		<?php } ?>
	<div style ="min-height: 100%; position: relative;">
		<div id="top" >
				<div class="spnsr">
					<?php echo $html->image('forum_logo_badge.png', array( 'alt' => 'Forum Badge', 'width' => '80', 'height' => '70')); ?>
				</div>
				<div class="right">
						<?php echo Stopwatch::start('header_search.ctp');?>
							<?php if ( $CurrentUser->isLoggedIn() ) { echo $this->element('layout/header_search', array('cache' => '+1 hour')); } ?>
						<?php echo Stopwatch::stop('header_search.ctp');?>
				</div> <!-- .right -->
				<div class="left">
						<div class="home">
							<?php echo $html->link(
											$html->image(
															'forum_logo.png',
															array( 'alt' => 'Logo', 'height' => 70)
															) ,
											array ( 'controller' => 'entries', 'action' => 'index', 'admin' => false),
											array ( 'id' => 'btn_header_logo' , 'escape'=>false));
							?>
							<div id="claim"></div>
					</div>
				</div> <!-- .left -->
				<div id="header_login">
						<?php echo $this->element('layout/header_login'); ?>
				</div>
		</div> <!-- #top -->
		<div id="topnav">
			<div>
				<div>
					<?php echo $this->element('layout/header_subnav_left'); ?>
				</div>
				<div>
					<?php echo $this->element('layout/header_subnav_center'); ?>
				</div>
				<div class="c_last_child">
					<?php echo $this->element('layout/header_subnav_right'); ?>
				</div>
			</div>
		</div>
		<?php 
		$flashMessage = $this->Session->flash();
		$emailMessage = $this->Session->flash('email'); 
		if ($flashMessage || $emailMessage) : 
		?>
			<div id="session_flash">
				<?php echo $flashMessage; ?>
				<?php echo $emailMessage; ?>
			</div>
		<?php endif; ?>
		<?php echo $this->element('layout/slidetabs'); ?>
		<div id="content">
				<?php echo $content_for_layout ?>
		</div>
		<div id="footer_pinned">
			<div id="bottomnav"  >
				<div>
						<div>
							<?php echo $this->element('layout/header_subnav_left'); ?>
						</div>
						<div>
							<a href="javascript:window.scrollTo(0,0);" style="width: 100px; display: inline-block; height: 20px;"><span class="img_up"></span></a>
						</div>
						<div class="c_last_child">
							<?php echo $this->element('layout/header_subnav_right'); ?>
						</div>
				</div>
			</div>
		</div>
	</div>
	<div class="bg_internal" style="overflow:hidden;">
		<?php
			if( isset($showDisclaimer) ) {
				Stopwatch::start('layout/disclaimer.ctp');
				echo $this->element('layout/disclaimer');
				Stopwatch::stop('layout/disclaimer.ctp');
			}
		?>

		<?php echo $html->scriptBlock("var webroot = '$this->webroot'; var user_show_inline = '{$CurrentUser['inline_view_on_click']}';"); ?>
		<?php 
			if ( Configure::read('debug') == 0 ):
				echo $html->script('js.min');
			else:
				echo $html->script('jquery.hoverIntent.minified');
				echo $html->script('jquery-ui-1.8.13.custom.min');
				echo $html->script('classes/thread.class');
				echo $html->script('classes/thread_line.class');
				echo $html->script('_app');
				echo $html->script('jquery.form');
				echo $html->script('jquery.clearabletextfield');
				echo $html->script('jquery.scrollTo-1.4.2-min');
			endif;
		?>
		<?php echo $scripts_for_layout; ?>
		<? echo $js->writeBuffer();?>
		<div class='clearfix'></div>
		<?php echo $this->Stopwatch->getResult();?>
		<?php echo $this->element('sql_dump'); ?>
	</div>
	</body>
</html>