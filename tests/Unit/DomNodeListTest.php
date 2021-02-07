<?php

namespace Eelcol\LaravelHtmlDom\Tests\Feature;

use Eelcol\LaravelHtmlDom\Support\Dom;
use Eelcol\LaravelHtmlDom\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DomNodeListTest extends TestCase
{
	public Dom $dom;

	public function setUp(): void
	{
		parent::setUp();

		$html = $this->getHtml();

		$this->dom = \Eelcol\LaravelHtmlDom\Facades\Dom::loadHtml($html);
	}

	/** @test */
	public function it_should_return_the_first()
	{
		$search = $this->dom->searchClass("someDiv");
		$element = $search->first();
		$html = $element->getHtml();

		$this->assertStringContainsString('<div class="someDiv anotherClass" data-div-item="1" data-top-level="1">', $html);
		$this->assertStringContainsString('<span class="innerSpan innerSpanFirst" data-some-attribute="yes">', $html);
		$this->assertStringContainsString('<p class="innerP" data-paragraph-element>This paragraph contains some text</p>', $html);
	}

	/** @test */
	public function it_should_return_the_last()
	{
		$search = $this->dom->searchClass("someDiv");
		$element = $search->last();
		$html = $element->getHtml();

		$this->assertStringContainsString('<footer class="someDiv" data-top-level="1">', $html);
	}

	/** @test */
	public function it_should_return_the_index()
	{
		$search = $this->dom->searchClass("someDiv");
		$element = $search->item(0);
		$html = $element->getHtml();

		$this->assertStringContainsString('<div class="someDiv anotherClass" data-div-item="1" data-top-level="1">', $html);
		$this->assertStringContainsString('<span class="innerSpan innerSpanFirst" data-some-attribute="yes">', $html);
		$this->assertStringContainsString('<p class="innerP" data-paragraph-element>This paragraph contains some text</p>', $html);

		$this->assertEquals($search->item(10), NULL);
	}

	/** @test */
	public function it_should_loop()
	{
		$search = $this->dom->searchClass("someDiv");
		$found = 0;

		foreach ($search as $element) {
			$found++;
		}

		$this->assertEquals($found, 4);
	}

	/** @test */
	public function it_should_get_all_tags_from_the_list()
	{
		$search = $this->dom->searchClass("someDiv");

		$this->assertEquals($search->getElementsByTagName('p')->count(), 3);
		$this->assertEquals($search->getElementsByTagName('*')->count(), 6);
	}

	/** @test */
	public function it_should_get_all_tags_with_a_class()
	{
		$search = $this->dom->searchClass("someDiv");

		$this->assertEquals($search->searchClass('innerSpan')->count(), 3);
		$this->assertEquals($search->searchClass('innerSpanFirst')->count(), 1);
	}
}