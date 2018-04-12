<?php
namespace App\Test\TestCase\Controller;

use App\Controller\ToolsController;
use Cake\Cache\Cache;
use Cake\Event\EventManager;
use Saito\Test\IntegrationTestCase;

/**
 * App\Controller\ToolsController Test Case
 */
class AdminControllerTest extends IntegrationTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.category',
        'app.entry',
        'app.setting',
        'app.user',
        'app.user_block',
        'app.user_ignore',
        'app.user_online',
        'app.user_read',
    ];

    /**
     * testAdminEmptyCaches method
     *
     * @return void
     */
    public function testAdminEmptyCachesNonAdmin()
    {
        $url = '/admin/admins/emptyCaches';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }

    // @td 3.0
    /*
    public function testAdminEmptyCachesUser()
    {
        $this->_loginUser(3);
        $url = '/admin/admins/emptyCaches';
        $this->get($url);
        $this->assertRedirectLogin($url);
    }
     *
     */

    public function testAdminEmptyCaches()
    {
        $this->_loginUser(1);
        Cache::write('foo', 'bar');
        $this->assertEquals('bar', Cache::read('foo'));
        $this->get('admin/admins/emptyCaches');
        $this->assertEmpty(Cache::read('foo'));
    }

    public function testPhpInfoUserAllowence()
    {
        $this->assertRouteForRole('/admin/admins/phpinfo', 'admin');
    }
}