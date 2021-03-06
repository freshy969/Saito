<?php

	use Saito\Smiley\Renderer;

	class RenderTest extends CakeTestCase {

		public $fixtures = ['app.smiley', 'app.smiley_code'];

		public function setUp() {
			//= smiley fixture
			$smiliesFixture = [
				[
					'order' => 1,
					'icon' => 'wink.png',
					'image' => 'wink.png',
					'title' => 'Wink',
					'code' => ';)',
					'type' => 'image'
				],
				[
					'order' => 2,
					'icon' => 'smile_icon.svg',
					'image' => 'smile_image.svg',
					'title' => 'Smile',
					'code' => ':-)',
					'type' => 'image'
				],
				[
					'order' => 3,
					'icon' => 'coffee',
					'image' => 'coffee',
					'title' => 'Coffee',
					'code' => '[_]P',
					'type' => 'font'
				],
			];
			Cache::write('Saito.Smilies.data', $smiliesFixture);

			$Controller = new \Controller;
			$Cache = new \Saito\Smiley\Cache($Controller);
			$this->Renderer = new Renderer($Cache);

			$View = new \View($Controller);
			$this->Helper = new \ParserHelper($View);

			$this->Renderer->setHelper($this->Helper);
		}

		public function tearDown() {
			unset ($this->Helper, $this->Renderer);
		}

		public function testSmiliesPixelImage() {
			$input = ';)';
			$expected = [
				'img' => [
					'src' => $this->Helper->webroot(
						'img/smilies/wink.png'
					),
					'alt' => ';)',
					'class' => 'saito-smiley-image',
					'title' => 'Wink'
				]
			];
			$result = $this->Renderer->replace($input);
			$this->assertTags($result, $expected);
		}

		public function testSmiliesVectorFont() {
			$input = '[_]P';
			$expected = [
				'i' => [
					'class' => 'saito-smiley-font saito-smiley-coffee',
					'title' => 'Coffee'
				]
			];
			$result = $this->Renderer->replace($input);
			$this->assertTags($result, $expected);
		}

		/**
		 * smilies should not be triggered next to HTML-entities
		 */
		public function testNoSmileyReplacementNextToHtmlEntities() {
			//= test working wink
			$input = ';)';
			$expected = [
				'img' => [
					'src' => $this->Helper->webroot(
						'img/smilies/wink.png'
					),
					'alt' => ';)',
					'class' => 'saito-smiley-image',
					'title' => 'Wink'
				]
			];
			$result = $this->Renderer->replace($input);
			$this->assertTags($result, $expected);

			//= test that wink is not triggered on entities
			$input = '&quot;)';
			$expected = '&quot;)';
			$result = $this->Renderer->replace($input);
			$this->assertEquals($expected, $result);

			$input = '&lt;)';
			$expected = '&lt;)';
			$result = $this->Renderer->replace($input);
			$this->assertEquals($expected, $result);

			$input = '&gt;)';
			$expected = '&gt;)';
			$result = $this->Renderer->replace($input);
			$this->assertEquals($expected, $result);
		}

	}
