<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

	if (Configure::read('Saito.installed')) {
		/**
		 * Default route
		 */
		Router::connect(
				'/',
				['controller' => 'entries', 'action' => 'index']
		);
	} else {
		/**
		 * installer route
		 */
		Router::connect(
				'/',
				['plugin' => 'install', 'controller' => 'install', 'action' => 'index']
		);
	}

	/**
	 * /users/login -> /login
	 */
	Router::connect('/login', ['controller' => 'users', 'action' => 'login']);


	/**
	 * ...and connect the rest of 'Pages' controller's urls.
	 */
	Router::connect('/pages/*', ['controller' => 'pages', 'action' => 'display']);

	/**
	 * Admin Route
	 */
	Router::connect('/admin',
			['controller' => 'admins', 'action' => 'index', 'admin' => true]);
	Router::connect(
		'/admin/plugins',
		['controller' => 'admins', 'action' => 'plugins', 'admin' => true]
	);

	/**
	 * Default search action
	 */
	Router::connect('/searches',
			['controller' => 'searches', 'action' => 'simple']);

	/**
	 * Dynamic Assets
	 */
	Router::connect('/da/:action/*', ['controller' => 'DynamicAssets']);

	/**
	 * Sitemaps
	 */
	Router::connect('/sitemap', ['plugin' => 'sitemap', 'controller' => 'sitemaps']);
	Router::connect('/sitemaps/:action/*', ['plugin' => 'sitemap', 'controller' => 'sitemaps']);

	/**
	 * Pagination for entries/index
	 */
	/*
	Router::connect(
			'/entries/index/*',
			array('controller' => 'entries', 'action' => 'index'),
			array( 'named' => array('page'), 'page' => '[0-9]+' )
			);
	 *
	 */

	/**
	 * RSS & JSON setup
	 */
	Router::parseExtensions('rss', 'json', 'xml');

	/**
	 * Load all plugin routes. See the CakePlugin documentation on
	 * how to customize the loading of plugin routes.
	 */
	CakePlugin::routes();

	/**
	 * Load the CakePHP default routes. Only remove this if you do not want to use
	 * the built-in default routes.
	 */
	require CAKE . 'Config' . DS . 'routes.php';
