<?php

	// @codingStandardsIgnoreFile

	/* Bbcode Test cases generated on: 2010-08-02 07:08:44 : 1280727824 */
	App::import('Lib', 'Stopwatch.Stopwatch');
	App::import('Helper',
			array(
			'FileUpload.FileUpload',
			'CakephpGeshi.Geshi',
	));
	App::import('Helper', 'MailObfuscator.MailObfuscator');

	App::uses('Sanitize', 'Utility');
	App::uses('Controller', 'Controller');
	App::uses('View', 'View');
	App::uses('BbcodeHelper', 'View/Helper');
	App::uses('HtmlHelper', 'View/Helper');
	App::uses('CakeRequest', 'Network');

	App::uses('BbcodeUserlistArray', 'Lib/Bbcode');

	class BbcodeHelperTest extends CakeTestCase {

		private $Bbcode = null;

		/**
		 * Preserves $GLOBALS vars through PHPUnit test runs
		 *
		 * @see http://www.phpunit.de/manual/3.6/en/fixtures.html#fixtures.global-state
		 * @var array
		 */
		protected $backupGlobalsBlacklist = array(
			/*
			 * $GLOBALS['__STRINGPARSER_NODE_ID' is set in stringparser.class.php
			 * and must not cleared out
			 */
			'__STRINGPARSER_NODE_ID'
		);

		public function testSimpleTextDecorations() {

			//* bold
			$input = '[b]bold[/b]';
			$expected = array( 'strong' => array( ), 'bold', '/strong' );
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			//* emphasis
			$input = '[i]italic[/i]';
			$expected = array( 'em' => array( ), 'italic', '/em' );
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			//* underline
			$input = '[u]text[/u]';
			$expected = array( 'span' => array( 'class' => 'c-bbcode-underline' ), 'text', '/span' );
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testStrike() {
			$expected = ['del' => [], 'text', '/del'];

			// [strike]
			$input  = '[strike]text[/strike]';
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// [s]
			$input  = '[s]text[/s]';
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testSpoiler() {
			$input    = 'pre [spoiler] te "\' xt[/spoiler]';
			$expected = [
				'pre',
				[
					'div' => [
						'class' => 'c-bbcode-spoiler',
						'style' => 'display: inline;'
					]
				],
				['script' => true],
				'preg:/(.*?)"string":" te \\\"\' xt"(.*?)(?=<)/',
				'/script',
				[
					'a' => [
						'href'         => '#',
						'class'        => 'c-bbcode-spoiler-link',
						'onclick'
					]
				],
				'preg:/.*▇ Spoiler ▇.*?(?=<)/',
				'/a',
				'/div'
			];
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected, true);
		}

		public function testList() {
			$input = "[list]\n[*]fooo\n[*]bar\n[/list]";
			$expected = array(
					array( 'ul' => array( 'class' => 'c-bbcode-ul' ) ),
					array( 'li' => array( 'class' => 'c-bbcode-li' ) ),
					'fooo',
					array( 'br' => array( ) ),
					'/li',
					array( 'li' => array( 'class' => 'c-bbcode-li' ) ),
					'bar',
					'/li',
					'/ul'
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testLink() {
			$input = '[url=http://cgi.ebay.de/ws/eBayISAPI.dll?ViewItem&item=250678480561&ssPageName=ADME:X:RTQ:DE:1123]test[/url]';
			$expected = "<a href='http://cgi.ebay.de/ws/eBayISAPI.dll?ViewItem&item=250678480561&ssPageName=ADME:X:RTQ:DE:1123' rel='external' target='_blank'>test</a> <span class='c-bbcode-link-dinfo'>[ebay.de]</span>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			/**
			 * external server
			 */
			$input = '[url]http://heise.de/foobar[/url]';
			$expected = array(
					'a' => array(
							'href' => 'http://heise.de/foobar',
							'rel' => 'external',
							'target' => '_blank'
					),
					'http://heise.de/foobar',
					'/a'
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			$input = '[link]http://heise.de/foobar[/link]';
			$expected = array(
					'a' => array(
							'href' => 'http://heise.de/foobar',
							'rel' => 'external',
							'target' => '_blank'
					),
					'http://heise.de/foobar',
					'/a'
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// masked link
			$input = '[url=http://heise.de/foobar]foobar[/url]';
			$expected = array(
					'a' => array(
							'href' => 'http://heise.de/foobar',
							'rel' => 'external',
							'target' => '_blank'
					),
					'foobar',
					'/a',
					'span' => array( 'class' => 'c-bbcode-link-dinfo' ), '[heise.de]', '/span'
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// masked link with no label
			$input = '[url=http://heise.de/foobar  label=none ]foobar[/url]';
			$expected = array(
					'a' => array(
							'href' => 'http://heise.de/foobar',
							'rel' => 'external',
							'target' => '_blank',
					),
					'foobar',
					'/a',
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			/**
			 * local server
			 */
			$input = '[url=http://macnemo.de/foobar]foobar[/url]';
			$expected = "<a href='http://macnemo.de/foobar'>foobar</a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			$input = '[url]/foobar[/url]';
			$expected = array(
					'a' => array(
							'href' => '/foobar',
					),
					'preg:/\/foobar/',
					'/a',
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);


			// test lokaler server with absolute url
			$input = '[url=/foobar]foobar[/url]';
			$expected = "<a href='/foobar'>foobar</a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// test 'http://' only
			$input = '[url=http://]foobar[/url]';
			$expected = "<a href='http://'>foobar</a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// test for co.uk
			$input = '[url=http://heise.co.uk/foobar]foobar[/url]';
			$expected = array(
					'a' => array(
							'href' => 'http://heise.co.uk/foobar',
							'rel' => 'external',
							'target' => '_blank'
					),
					'foobar',
					'/a',
					'span' => array( 'class' => 'c-bbcode-link-dinfo' ), '[heise.co.uk]', '/span'
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testHashLinkSuccess() {
			// inline content ([i])
			$input = "[i]#2234[/i]";
			$expected = [
					'em' => [],
					'a' => [
							'href' => '/hash/2234'
					],
					'#2234',
					'/a',
					'/em'
			];
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// lists
			$input = "[list][*]#2234[/list]";
			$expected = [
					'ul' => ['class'],
					'li' => ['class'],
					'a' => [
							'href' => '/hash/2234'
					],
					'#2234',
					'/a',
					'/li',
					'/ul'
			];
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testHashLinkFailure() {
			// don't hash html encoded chars
			$input = '&#039;';
			$expected = '&#039;';
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// don't hash code
			$input = '[code]#2234[/code]';
			$result = $this->Bbcode->parse($input);
			$this->assertNotContains('>#2234</a>', $result);

			// not a valid hash
			$input = '#2234t';
			$result = $this->Bbcode->parse($input);
			$this->assertEqual('#2234t', $result);
		}

		public function testAtLinkKnownUsers() {
			$input = '@Alice @Bob @Bobby Junior @Bobby Tables @Dr. No';
			$expected =
					"<a href='/at/Alice'>@Alice</a>"
					." @Bob "
					."<a href='/at/Bobby+Junior'>@Bobby Junior</a>"
					." @Bobby Tables "
					."<a href='/at/Dr.+No'>@Dr. No</a>";

			$result = $this->Bbcode->parse($input);
			$this->assertEqual($result, $expected);

			$input = '[code]@Alice[/code]';
			$result = $this->Bbcode->parse($input);
			$this->assertNotContains('>@Alice</a>', $result);
		}

		public function testLinkEmptyUrl() {
			$input = '[url=][/url]';
			$expected = "<a href=''></a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);
		}

		/*
		 * without obfuscator
		 */
		public function testEmail() {
			/*
				// mailto:
				$input = '[email]mailto:mail@tosomeone.com[/email]';
				$expected = "<a href='mailto:mail@tosomeone.com'>mailto:mail@tosomeone.com</a>";
				$result = $this->Bbcode->parse($input);
				$this->assertIdentical($expected, $result);

				// mailto: mask
				$input = '[email=mailto:mail@tosomeone.com]Mail[/email]';
				$expected = "<a href='mailto:mail@tosomeone.com'>Mail</a>";
				$result = $this->Bbcode->parse($input);
				$this->assertIdentical($expected, $result);

				// no mailto:
				$input = '[email]mail@tosomeone.com[/email]';
				$expected = "<a href='mailto:mail@tosomeone.com'>mail@tosomeone.com</a>";
				$result = $this->Bbcode->parse($input);
				$this->assertIdentical($expected, $result);

				// no mailto: mask
				$input = '[email=mail@tosomeone.com]Mail[/email]';
				$expected = "<a href='mailto:mail@tosomeone.com'>Mail</a>";
				$result = $this->Bbcode->parse($input);
				$this->assertIdentical($expected, $result);
				*/

		}

		public function testEmailMailto() {
			$MO = $this->getMock('MailObfuscator', array('link'));
			$MO->expects($this->once(4))
					->method('link')
					->with('mail@tosomeone.com', null);
			$this->Bbcode->MailObfuscator = $MO;

			$input = '[email]mailto:mail@tosomeone.com[/email]';
			$this->Bbcode->parse($input);
		}

		public function testEmailMailtoMask() {
			$MO = $this->getMock('MailObfuscator', array('link'));
			$MO->expects($this->once(4))
					->method('link')
					->with('mail@tosomeone.com', 'Mail');
			$this->Bbcode->MailObfuscator = $MO;

			$input = '[email=mailto:mail@tosomeone.com]Mail[/email]';
			$this->Bbcode->parse($input);
		}

		public function testEmailNoMailto() {
			$MO = $this->getMock('MailObfuscator', array('link'));
			$MO->expects($this->once(4))
					->method('link')
					->with('mail@tosomeone.com', null);
			$this->Bbcode->MailObfuscator = $MO;

			$input = '[email]mail@tosomeone.com[/email]';
			$this->Bbcode->parse($input);
		}

		public function testEmailNoMailtoMask() {
			$MO = $this->getMock('MailObfuscator', array('link'));
			$MO->expects($this->once(4))
					->method('link')
					->with('mail@tosomeone.com', 'Mail');
			$this->Bbcode->MailObfuscator = $MO;

			$input = '[email=mail@tosomeone.com]Mail[/email]';
			$this->Bbcode->parse($input);
		}

		public function testFloat() {
			$expected = [
					'div' => ['class' => 'c-bbcode-float'],
					'text',
					'/div',
					'more'
			];

			$input  = '[float]text[/float]more';
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);
		}

		public function testAutoLink() {
			$input = 'http://heise.de/foobar';
			$expected = "<a href='http://heise.de/foobar' rel='external' target='_blank'>http://heise.de/foobar</a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// autolink surrounded by text
			$input = 'some http://heise.de/foobar text';
			$expected = "some <a href='http://heise.de/foobar' rel='external' target='_blank'>http://heise.de/foobar</a> text";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// autolink without http:// prefix
			$input = 'some www.heise.de/foobar text';
			$expected = array(
					'some ',
					'a' => array(
							'href' => 'http://www.heise.de/foobar',
							'rel' => 'external',
							'target' => '_blank',
					),
					'http://www.heise.de/foobar',
					'/a',
					'preg:/ text/'
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// no autolink in [code]
			$input = '[code]http://heise.de/foobar[/code]';
			$needle = 'heise.de/foobar</a>';
			$result = $this->Bbcode->parse($input);
			$this->assertNotContains($result, $needle);

			// email autolink
			$input = 'text mail@tosomeone.com test';
			// $expected = "text <a href='mailto:mail@tosomeone.com'>mail@tosomeone.com</a> test";
			$result = $this->Bbcode->parse($input);
			// $this->assertIdentical($expected, $result);
			// @bogus weak test
			$this->assertRegExp('/^text .*href=".* test$/sm', $result);
		}

		public function testShortenLink() {
			$length_max = 15;

			$text_word_maxlenghth = Configure::read('Saito.Settings.text_word_maxlength');
			Configure::write('Saito.Settings.text_word_maxlength', $length_max);

			$input = '[url]http://this/url/is/32/chars/long[/url]';
			$expected = "<a href='http://this/url/is/32/chars/long' rel='external' target='_blank'>http:// … /long</a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($result, $expected);

			$input = 'http://this/url/is/32/chars/long';
			$expected = "<a href='http://this/url/is/32/chars/long' rel='external' target='_blank'>http:// … /long</a>";
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($result, $expected) ;

			Configure::write('Saito.Settings.text_word_maxlength', $text_word_maxlenghth);
		}

		public function testIframe() {
			$bbcode_img = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			$video_domains = Configure::read('Saito.Settings.video_domains_allowed');
			Configure::write('Saito.Settings.video_domains_allowed', 'youtube | vimeo');

			//* test allowed domain
			$input = '[iframe height=349 width=560 ' .
					'src=http://www.youtube.com/embed/HdoW3t_WorU ' .
					'frameborder=0][/iframe]';
			$expected = array(
					array( 'iframe' => array(
									'src' => 'http://www.youtube.com/embed/HdoW3t_WorU?&wmode=Opaque',
									'height' => '349',
									'width' => '560',
									'frameborder' => '0',
					) ),
					'/iframe',
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			//* test forbidden domains
			$input = '[iframe height=349 width=560 ' .
					'src=http://www.youtubescam.com/embed/HdoW3t_WorU ' .
					'frameborder=0][/iframe]';
			$expected = '/src/i';
			$result = $this->Bbcode->parse($input);
			$this->assertNoPattern($expected, $result);

      /*
       * test if all domains are allowed
       */
			Configure::write('Saito.Settings.video_domains_allowed', '*');

      $refObject   = new ReflectionObject($this->Bbcode);
      $refProperty = $refObject->getProperty('_allowedVideoDomains');
      $refProperty->setAccessible(true);
      $refProperty->setValue('_allowedVideoDomains', null);

			$input = '[iframe height=349 width=560 ' .
					'src=http://www.youtubescam.com/embed/HdoW3t_WorU ' .
					'][/iframe]';
			$expected = 'src="http://www.youtubescam.com/embed/HdoW3t_WorU';
			$result = $this->Bbcode->parse($input);
			$this->assertContains($expected, $result);

      /*
       * test if no domains are allowed
       */
			Configure::write('Saito.Settings.video_domains_allowed', '');

      $refObject   = new ReflectionObject($this->Bbcode);
      $refProperty = $refObject->getProperty('_allowedVideoDomains');
      $refProperty->setAccessible(true);
      $refProperty->setValue('_allowedVideoDomains', null);

			$input = '[iframe height=349 width=560 ' .
					'src=http://www.youtubescam.com/embed/HdoW3t_WorU ' .
					'][/iframe]';
			$expected = '/src/i';
			$result = $this->Bbcode->parse($input);
			$this->assertNoPattern($expected, $result);
		}

		public function testExternalImage() {
			$bbcode_img = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			// test for standard URIs
			$input = '[img]http://localhost/img/macnemo.png[/img]';
			$expected = '<img src="http://localhost/img/macnemo.png" class="c-bbcode-external-image" alt="" />';
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// test for URIs without protocol
			$input = '[img]/somewhere/macnemo.png[/img]';
			$expected = '<img src="'.$this->Bbcode->webroot.'somewhere/macnemo.png" class="c-bbcode-external-image" alt="" />';
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($result, $expected);

			// test scaling with 1 parameter
			$input = '[img=50]http://localhost/img/macnemo.png[/img]';
			$expected = '<img src="http://localhost/img/macnemo.png" class="c-bbcode-external-image" width="50" alt="" />';
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// test scaling with 2 parameters
			$input = '[img=50x100]http://localhost/img/macnemo.png[/img]';
			$expected = '<img src="http://localhost/img/macnemo.png" class="c-bbcode-external-image" width="50" height="100" alt="" />';
			$result = $this->Bbcode->parse($input);
			$this->assertIdentical($expected, $result);

			// float left
			$input = '[img=left]http://localhost/img/macnemo.png[/img]';
			$expected = array(
					array( 'img' => array(
									'src' => 'http://localhost/img/macnemo.png',
									'class' => "c-bbcode-external-image",
									'style' => "float: left;",
									'alt' => "",
					) )
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// float right
			$input = '[img=right]http://localhost/img/macnemo.png[/img]';
			$expected = array(
					array( 'img' => array(
									'src' => 'http://localhost/img/macnemo.png',
									'class' => "c-bbcode-external-image",
									'style' => "float: right;",
									'alt' => "",
					) )
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// image nested in external link
			$input = '[url=http://heise.de][img]http://heise.de/img.png[/img][/url]';
			$expected = "<a href='http://heise.de' rel='external' target='_blank'><img src=\"http://heise.de/img.png\" class=\"external_image\" style=\"\" width=\"auto\" height=\"auto\" alt=\"\" /></a>";
			$expected = array(
					array( 'a' => array(
									'href' => 'http://heise.de',
									'rel' => 'external',
									'target' => '_blank',
					) ),
					array( 'img' => array(
									'src' => 'http://heise.de/img.png',
									'class' => 'c-bbcode-external-image',
									'alt' => '',
					) ),
					'/a'
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			Configure::write('Saito.Settings.bbcode_img', $bbcode_img);
		}

		public function testInternalImage() {
			  $bbcode_img = Configure::read('Saito.Settings.bbcode_img');
			  Configure::write('Saito.Settings.bbcode_img', true);

         // Create a map of arguments to return values.
        $map = array(
          array( 'test.png', [], '<img src="test.png" />'),
          array(
              'test.png',
              array(
                  'autoResize'      => false,
                  'resizeThumbOnly' => false,
                  'width'           => '50',
                  'height'          => '60',
              ),
              '<img src="test.png" width="50" height="60" alt="">'
          )
        );
				$FileUploader = $this->getMock('FileUploaderHelper', array( 'image', 'reset' ));
				$FileUploader->expects($this->atLeastOnce())
					->method('image')
					->will($this->returnValueMap($map));
				$this->Bbcode->FileUpload =  $FileUploader;

        // internal image
			  $input 		= '[upload]test.png[/upload]';
			  $expected = array(
			  array( 'div' => array('class' => 'c-bbcode-upload')),
			  array( 'img' => array(
			  'src'	=> 'test.png',
			  )),
			  '/div',
			  );
			  $result		= $this->Bbcode->parse($input);
			  $this->assertTags($result, $expected);

        // internal image with attributes
			  $input = '[upload width=50 height=60]test.png[/upload]';
        $expected = array(
            array( 'div' => array( 'class' => 'c-bbcode-upload' ) ),
            array( 'img' => array(
                    'src'     => 'test.png',
                    'width'   => '50',
                    'height'  => '60',
                    'alt'     => '',
            ),
                ),
            '/div',
        );
        $result = $this->Bbcode->parse($input);
        $this->assertTags($result, $expected);

        // internal image legacy [img#] tag
			  $input 		= '[img#]test.png[/img]';
			  $expected = array(
			  array( 'div' => array(
            'class' => 'c-bbcode-upload'
            ) ),
          array( 'img' => array(
                  'src' => 'test.png',
          ) ),
          '/div',
        );
			  $result		= $this->Bbcode->parse($input);
			  $this->assertTags($result, $expected);

			  // nested image does not have [domain.info]
			  $input 		= '[url=http://heise.de][upload]test.png[/upload][/url]';
			  $expected	=	"/c-bbcode-link-dinfo/";
			  $result		= $this->Bbcode->parse($input);
			  $this->assertNoPattern($expected, $result);

			  Configure::write('Saito.Settings.bbcode_img', $bbcode_img);
		}

		public function testSmilies() {

			$input = ';)';
			$expected = array(
				'img' => array(
					'src'   => $this->Bbcode->webroot(
						'img/smilies/wink.png'
					),
					'alt'   => ';)',
					'title' => 'Wink'
				)
			);
			$result = $this->Bbcode->parse($input, array('cache' => false));
			$this->assertTags($result, $expected);

			// test html entities
			$input = Sanitize::html('ü :-) ü');
			$expected = array( '&uuml; ', 'img' => array( 'src' => $this->Bbcode->webroot('img/smilies/smile_image.png'), 'alt' => ':-)', 'title' => 'Smile' ), ' &uuml;' );
			$result = $this->Bbcode->parse($input, array( 'cache' => false ));
			$this->assertTags($result, $expected);

			// test html entities
			$input = Sanitize::html('foo …) bar €) batz');
			$expected = 'foo &hellip;) bar &euro;) batz';
			$result = $this->Bbcode->parse($input, array( 'cache' => false ));
			$this->assertIdentical($expected, $result);

			// test no smilies in code
			$input = '[code text]:)[/code]';
			$needle = '<img';
			$result = $this->Bbcode->parse($input, array( 'cache' => false ));
			$this->assertNotContains($needle, $result);
		}

		public function testCode() {

			//* test whitespace
			$input = "[code]\ntest\n[/code]";
			$expected = "/>test</";
			$result = $this->Bbcode->parse($input);
			$this->assertPattern($expected, $result);

			//* test escaping of [bbcode]
			$input = '[code][b]text[b][/code]';
			$expected = array(
					array( 'div' => array( 'class' => 'c-bbcode-code-wrapper' ) ),
					'preg:/.*?\[b\]text\[b\].*/',
			);
			$result = $this->Bbcode->parse($input);
			$this->assertTags($result, $expected);

			// [code]<citation mark>[/code] should not be cited
			$input = Sanitize::html(
				"[code]\n" . $this->Bbcode->settings['quoteSymbol'] . "\n[/code]"
			);
			$expected = '`span class=.*?c-bbcode-citation`';
			$result = $this->Bbcode->parse($input);
			$this->assertNoPattern($expected, $result);

      /*
       * test setting a sourcecode type
       */
			$input = '[code php]text[/code]';
			$result = $this->Bbcode->parse($input);
			$expected = 'lang="php"';
      $this->assertContains($expected, $result);
		}

		public function testCodeDetaginize() {
			$input = '[code bash]pre http://example.com post[/code]';
			$result = $this->Bbcode->parse($input);
			$this->assertNotContains('autoLink', $result);
		}

		public function testMarkiereZitat() {

			$input = Sanitize::html("» test");
			$result = $this->Bbcode->parse($input);
			$expected = array(
					'span' => array( 'class' => 'c-bbcode-citation' ),
					'&raquo; test',
					'/span',
			);
			$this->assertTags($result, $expected);
		}

		public function testHtml5Video() {
			//* setup
			$bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			//* @td write video tests
			$url = 'http://example.com/audio.mp4';
			$input = "[video]{$url}[/video]";
			$result = $this->Bbcode->parse($input);
			$expected = array(
					'video' => array( 'src' => $url, 'controls' => 'controls', 'x-webkit-airplay' => 'allow' ),
			);
			$this->assertTags($result, $expected);

			//* test autoconversion for audio files
			$html5AudioExtensions = array( 'm4a', 'ogg', 'mp3', 'wav', 'opus' );
			foreach ( $html5AudioExtensions as $extension ) {
				$url = 'http://example.com/audio.' . $extension;
				$input = "[video]{$url}[/video]";
				$result = $this->Bbcode->parse($input);
				$expected = array(
						'audio' => array( 'src' => $url, 'controls' => 'controls' ),
				);
				$this->assertTags($result, $expected);
			}

			//* teardown
			Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
		}

		public function testHr() {
			$input = '[hr][hr]';
			$expected = '<hr class="c-bbcode-hr"><hr class="c-bbcode-hr">';
			$result = $this->Bbcode->parse($input);
			$this->assertEqual($result, $expected);
		}

		public function testHrShort() {
			$input = '[---][---]';
			$expected = '<hr class="c-bbcode-hr"><hr class="c-bbcode-hr">';
			$result = $this->Bbcode->parse($input);
			$this->assertEqual($result, $expected);
		}

    public function testEmbedly() {

			//* setup
			$bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

      $embedly_enabled = Configure::read('Saito.Settings.embedly_enabled');
      $embedly_key = Configure::read('Saito.Settings.embedly_key');

      // Embedly is disabled
      $observer = $this->getMock('Embedly', array( 'setApiKey', 'embedly' ));
      $observer->expects($this->never())
          ->method('setApiKey');
      $this->Bbcode->Embedly = $observer;
      $input = '[embed]foo[/embed]';
      $result = $this->Bbcode->parse($input);

      // Embedly is enabled
      Configure::write('Saito.Settings.embedly_enabled', true);
      Configure::write('Saito.Settings.embedly_key', 'abc123');

      $observer = $this->getMock('Embedly', array( 'setApiKey', 'embedly' ));
      $observer->expects($this->once())
          ->method('setApiKey')
          ->with($this->equalTo('abc123'));
      $observer->expects($this->once())
          ->method('embedly')
          ->with($this->equalTo('foo'));
      $this->Bbcode->Embedly = $observer;
      $result = $this->Bbcode->parse($input);

			//* teardown
      Configure::write('Saito.Settings.embedly_enabled', $embedly_enabled);
      Configure::write('Saito.Settings.embedly_key', $embedly_key);
			Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
    }

    public function testHtml5Audio() {

			//* setup
			$bbcodeImg = Configure::read('Saito.Settings.bbcode_img');
			Configure::write('Saito.Settings.bbcode_img', true);

			//* simple test
			$url = 'http://example.com/audio3.m4a';
			$input = "[audio]{$url}[/audio]";
			$result = $this->Bbcode->parse($input);
			$expected = array(
					'audio' => array( 'src' => $url, 'controls' => 'controls' ),
			);
			$this->assertTags($result, $expected);

			//* teardown
			Configure::write('Saito.Settings.bbcode_img', $bbcodeImg);
		}

		public function testCiteText() {

			$input = "";
			$result = $this->Bbcode->citeText($input);
			$expected = "";
			$this->assertEqual($result, $expected);

			$input = "123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789";
			$result = $this->Bbcode->citeText($input);
			$expected = "» 123456789 123456789 123456789 123456789 123456789 123456789 123456789\n» 123456789\n";
			$this->assertEqual($result, $expected);
		}

		/*		 * ******************** Setup ********************** */

		public function setUp() {
			Cache::clear();

			Configure::write('Asset.timestamp', false);

			$smilies_fixture = array(
					array(
							'order' => 1,
							'icon' => 'wink.png',
							'image' => 'wink.png',
							'title' => 'Wink',
							'code' => ';)',
					),
					array(
							'order' => 2,
							'icon' => 'smile_icon.png',
							'image' => 'smile_image.png',
							'title' => 'Smile',
							'code' => ':-)',
					),
					array(
							'order' => 2,
							'icon' => 'smile_icon.png',
							'image' => 'smile_image.png',
							'title' => 'Smile',
							'code' => ';-)',
					),
			);

			$this->smilies_all = Configure::read('Saito.Smilies.smilies_all');
			Configure::write('Saito.Smilies.smilies_all', $smilies_fixture);

			$this->smilies = Configure::read('Saito.Settings.smilies');
			Configure::write('Saito.Settings.smilies', true);

			Configure::write("Saito.Smilies.smilies_all_html", false);

			if ( isset($_SERVER['SERVER_NAME']) ) {
				$this->server_name = $_SERVER['SERVER_NAME'];
			} else {
				$this->server_name = false;
			}

			if ( isset($_SERVER['SERVER_PORT']) ) {
				$this->server_port = $_SERVER['SERVER_PORT'];
			} else {
				$this->server_port = false;
			}

			$this->asset_timestamp = Configure::read('Asset.timestamp');

			$this->text_word_maxlength = Configure::read('Saito.Settings.text_word_maxlength');
			Configure::write('Saito.Settings.text_word_maxlength', 10000);

			$this->autolink = Configure::read('Saito.Settings.autolink');
			Configure::write('Saito.Settings.autolink', true);

			$_SERVER['SERVER_NAME'] = 'macnemo.de';
			$_SERVER['SERVER_PORT'] = '80';

			parent::setUp();
			$Request = new CakeRequest('/');
			$Controller = new Controller($Request);
			$View = new View($Controller);
			$BbcodeUserlist = new BbcodeUserlistArray();
			$BbcodeUserlist->set(array(
					'Alice',
					'Bobby Junior',
					'Dr. No'
				)
			);

			$settings = array(
				'quoteSymbol' => '»',
				'hashBaseUrl' => '/hash/',
				'atBaseUrl'   => '/at/',
				'useSmilies'  => false,
				'UserList'  => $BbcodeUserlist
			);

			$this->Bbcode = new BbcodeHelper($View);
			$this->Bbcode->settings = $settings;
			$this->Bbcode->beforeRender(null);
		}

		public function tearDown() {
			parent::tearDown();
			if ( $this->server_name ) {
				$_SERVER['SERVER_NAME'] = $this->server_name;
			}

			if ($this->server_name) {
				$_SERVER['SERVER_PORT'] = $this->server_port;
			}

			Configure::write('Asset.timestamp', $this->asset_timestamp);

			Configure::write('Saito.Settings.text_word_maxlength',
					$this->text_word_maxlength);

			Configure::write('Saito.Settings.autolink', $this->autolink);

			Configure::write('Saito.Settings.smilies', $this->smilies);
			Configure::write('Saito.Settings.smilies_all', $this->smilies_all);

			Cache::clear();
			ClassRegistry::flush();
			unset($this->Bbcode);
		}

	}

