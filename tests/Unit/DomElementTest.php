<?php

namespace Eelcol\LaravelHtmlDom\Tests\Feature;

use Eelcol\LaravelHtmlDom\Support\Dom;
use Eelcol\LaravelHtmlDom\Support\DomElement;
use Eelcol\LaravelHtmlDom\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DomElementTest extends TestCase
{
	public Dom $dom;

	public function setUp(): void
	{
		parent::setUp();

		$html = $this->getHtml();

		$this->dom = \Eelcol\LaravelHtmlDom\Facades\Dom::loadHtml($html);
	}

	/** @test */
	public function it_should_return_the_outer_html()
	{
		$search = $this->dom->getElementsByTagName('body')->first();
		$this->assertTrue(is_a($search, DomElement::class));

		$this->assertStringContainsString('<body>', $search->getHtml());
		$this->assertStringContainsString('<div class="someDiv anotherClass" data-div-item="1" data-top-level="1">', $search->getHtml());
	}

	/** @test */
	public function it_should_return_the_inner_html()
	{
		$search = $this->dom->getElementsByTagName('body')->first();

		$this->assertStringNotContainsString('<body>', $search->getInnerHtml());
		$this->assertStringContainsString('<div class="someDiv anotherClass" data-div-item="1" data-top-level="1">', $search->getInnerHtml());
	}

	/** @test */
	public function it_should_return_if_it_has_class()
	{
		$div = $this->dom->searchClass('someDiv')->first();
		$spans = $div->getElementsByTagName('span');

		$this->assertTrue($spans->first()->hasClass('innerSpan'));
		$this->assertTrue($spans->first()->hasClass('innerSpanFirst'));
		$this->assertFalse($spans->first()->hasClass('innerSpanSecond'));
	}
}