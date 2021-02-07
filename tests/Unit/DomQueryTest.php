<?php

namespace Eelcol\LaravelHtmlDom\Tests\Feature;

use Eelcol\LaravelHtmlDom\Support\Dom;
use Eelcol\LaravelHtmlDom\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DomQueryTest extends TestCase
{
	public Dom $dom;

	public function setUp(): void
	{
		parent::setUp();

		$html = $this->getHtml();

		$this->dom = \Eelcol\LaravelHtmlDom\Facades\Dom::loadHtml($html);
	}

	/** @test */
	public function it_should_query_classes()
	{
		$query = $this->dom->query()
			->element("div")
			->class("someDiv")
			->get();

		$this->assertEquals($query->count(), 3);

		$query = $this->dom->query()
			->element("div")
			->class("anotherClass")
			->get();

		$this->assertEquals($query->count(), 3);

		$query = $this->dom->query()
			->class("anotherClass")
			->get();

		$this->assertEquals($query->count(), 4);

		$query = $this->dom->query()
			->element("div")
			->class("someDiv")
			->class("anotherClass")
			->class("anotherDivClassLast")
			->get();

		$this->assertEquals($query->count(), 1);
	}
}