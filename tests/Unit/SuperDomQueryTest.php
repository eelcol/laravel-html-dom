<?php

namespace Eelcol\LaravelHtmlDom\Tests\Unit;

use Eelcol\LaravelHtmlDom\Support\SuperDom;
use Eelcol\LaravelHtmlDom\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SuperDomQueryTest extends TestCase
{
	public SuperDom $dom;

	public function setUp(): void
	{
		parent::setUp();

		$html = $this->getHtml();

		$this->dom = \Eelcol\LaravelHtmlDom\Facades\SuperDom::loadHtml($html);
	}

	/** @test */
	public function it_should_query_classes()
	{
		$query = $this->dom->query()
			->element("div")
			->class("someDiv")
			->get();

		$this->assertEquals(3, $query->count());

		$query = $this->dom->query()
			->element("div")
			->class("anotherClass")
			->get();

		$this->assertEquals(3, $query->count());

		$query = $this->dom->query()
			->class("anotherClass")
			->get();

		$this->assertEquals(4, $query->count());

		$query = $this->dom->query()
			->element("div")
			->class("someDiv")
			->class("anotherClass")
			->class("anotherDivClassLast")
			->get();

		$this->assertEquals(1, $query->count());
	}
}