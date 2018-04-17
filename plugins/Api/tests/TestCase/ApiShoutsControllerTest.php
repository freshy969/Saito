<?php

namespace Api\Test;

use Api\Lib\ApiIntegrationTestCase;

/**
 * ApiEntriesController Test Case
 *
 */
class ApiShoutsControllerTest extends ApiIntegrationTestCase
{

    protected $_apiRoot = 'api/v1/';

    protected $_fixtureResult = [
        0 => [
            'id' => 4,
            'time' => '2013-02-08T11:49:31+00:00',
            'text' => '<script></script>[i]italic[/i]',
            'html' => '&lt;script&gt;&lt;/script&gt;<em>italic</em>',
            'user_id' => 1,
            'user_name' => 'Alice',
        ],
        1 => [
            'id' => 3,
            'time' => '2013-02-08T11:49:31+00:00',
            'text' => 'Lorem ipsum dolor sit amet',
            'html' => 'Lorem ipsum dolor sit amet',
            'user_id' => 1,
            'user_name' => 'Alice',
        ],
        2 => [
            'id' => 2,
            'time' => '2013-02-08T11:49:31+00:00',
            'text' => 'Lorem ipsum dolor sit amet',
            'html' => 'Lorem ipsum dolor sit amet',
            'user_id' => 1,
            'user_name' => 'Alice',
        ],
    ];

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.category',
        'app.entry',
        'app.esevent',
        'app.esnotification',
        'app.setting',
        'app.shout',
        'app.smiley',
        'app.smiley_code',
        'app.upload',
        'app.user',
        'app.user_block',
        'app.user_ignore',
        'app.user_read',
        'app.user_online',
        'plugin.bookmarks.bookmark'
    ];

    public function testShoutsDisallowedRequestTypes()
    {
        $this->_checkDisallowedRequestType(
            ['PUT', 'DELETE'],
            $this->_apiRoot . 'shouts'
        );
    }

    public function testShoutsGet()
    {
        $this->markTestIncomplete();
        $this->_loginUser(3);

        $this->get($this->_apiRoot . 'shouts.json');
        $this->assertResponseOk();

        $expected = $this->_fixtureResult;
        $result = json_decode((string)$this->_response->getBody(), true);
        $this->assertEquals($expected, $result);
    }

    public function testShoutsGetNotLoggedIn()
    {
        $this->expectException('\Api\Error\Exception\ApiAuthException');
        $this->get($this->_apiRoot . 'shouts.json');
    }

    public function testShoutsPost()
    {
        $this->markTestIncomplete();
        $this->_loginUser(3);

        $data = [
            'text' => 'test < shout'
        ];
        $this->post($this->_apiRoot . 'shouts.json', $data);
        $result = (string)$this->_response->getBody();

        $this->assertResponseOk();

        $result = json_decode($result, true);
        $_newEntry = array_shift($result);

        $_newEntryTime = strtotime($_newEntry['time']);
        $this->assertGreaterThanOrEqual(time() - 1, $_newEntryTime);
        unset($_newEntry['time']);

        $expected = [
            'id' => 5,
            'text' => 'test < shout',
            'html' => 'test &lt; shout',
            'user_id' => 3,
            'user_name' => 'Ulysses',
        ];
        $this->assertEquals($_newEntry, $expected);

        $expected = $this->_fixtureResult;
        $this->assertEquals($result, $expected);
    }

    public function testShoutsPostTextMissing()
    {
        $this->_loginUser(3);

        $this->expectException(
            'Cake\Http\Exception\BadRequestException'
        );
        $this->post($this->_apiRoot . 'shouts.json');
    }

    public function testShoutsPostNotLoggedIn()
    {
        $this->expectException('\Api\Error\Exception\ApiAuthException');
        $this->post($this->_apiRoot . 'shouts.json', ['text' => 'foo']);
    }
}
