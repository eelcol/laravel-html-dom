<?php

namespace Eelcol\LaravelHtmlDom\Tests\Unit;

use Eelcol\LaravelHtmlDom\Support\SuperDom;
use Eelcol\LaravelHtmlDom\Support\SuperDomElement;
use Eelcol\LaravelHtmlDom\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SuperDomElementTest extends TestCase
{
	public SuperDom $dom;

	public function setUp(): void
	{
		parent::setUp();

		$html = $this->getHtml();

		$this->dom = \Eelcol\LaravelHtmlDom\Facades\SuperDom::loadHtml($html);
	}

	/** @test */
	public function it_should_return_the_outer_html()
	{
		$search = $this->dom->getElementsByTagName('body')->first();
		$this->assertTrue(is_a($search, SuperDomElement::class));

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
		$div = $this->dom->getElementsByClassname('someDiv')->first();
		$spans = $div->getElementsByTagName('span');

		$this->assertTrue($spans->first()->hasClass('innerSpan'));
		$this->assertTrue($spans->first()->hasClass('innerSpanFirst'));
		$this->assertFalse($spans->first()->hasClass('innerSpanSecond'));
	}

    /** @test */
    public function it_should_work_with_attributes()
    {
        $div = $this->dom->getElementsByClassname('someDiv')->first();

        $this->assertEquals(true, $div->hasAttribute('data-div-item'));
        $this->assertEquals("1", $div->getAttribute("data-div-item"));

        $div->setAttribute("data-div-item", 99);
        $this->assertEquals(99, $div->getAttribute("data-div-item"));

        $div->removeAttribute("data-div-item");
        $this->assertEquals(false, $div->hasAttribute('data-div-item'));
    }

    /** @test */
    public function it_should_return_the_next_sibling()
    {
        $divs = $this->dom->getElementsByClassname("someDiv", "div");
        $first = $divs->first();
        $sibling = $first->getNextSibling("div");

        $this->assertEquals("2", $sibling->getAttribute("data-div-item"));
    }

    /** @test */
    public function it_should_search_other_elements_by_class()
    {
        $divs = $this->dom->getElementsByClassname("someDiv", "div");
        $first = $divs->first();

        $innerP = $first->getElementsByClassName("innerP");

        $this->assertEquals(1, $innerP->count());
        $this->assertEquals("This first paragraph contains some text", $innerP->first()->nodeValue());
    }

    /** @test */
    public function it_should_search_other_elements_by_tag()
    {
        $divs = $this->dom->getElementsByClassname("someDiv", "div");
        $last = $divs->last();

        $paragraphs = $last->getElementsByTagName("p");
        $this->assertEquals(3, $paragraphs->count());
    }

    /** @test */
    public function it_should_search_elements_by_attribute()
    {
        $divs = $this->dom->getElementsByClassname("someDiv", "div");
        $last = $divs->last();

        $paragraphs = $last->getElementsByAttribute("data-paragraph-element", "yes");
        $this->assertEquals(2, $paragraphs->count());

        $paragraphs = $last->getElementsWithAttribute("data-paragraph-element");
        $this->assertEquals(2, $paragraphs->count());
    }

    /** @test */
    public function it_should_remove_element()
    {
        $divs = $this->dom->getElementsByClassname("someDiv", "div");
        $first = $divs->first();

        $first->removeElement();

        $html = $this->dom->getHtml();

        $this->assertStringNotContainsString('<div class="someDiv anotherClass" data-div-item="1" data-top-level="1">', $html);
        $this->assertStringNotContainsString('<span class="innerSpan innerSpanFirst" data-some-attribute="yes">', $html);
    }

    /** @test */
    public function it_should_remove_outer_element()
    {
        $divs = $this->dom->getElementsByClassname("someDiv", "div");
        $first = $divs->first();

        $first->removeOuterElement();

        $html = $this->dom->getHtml();

        $this->assertStringNotContainsString('<div class="someDiv anotherClass" data-div-item="1" data-top-level="1">', $html);
        $this->assertStringContainsString('<span class="innerSpan innerSpanFirst" data-some-attribute="yes">', $html);
    }

    /** @test */
    public function it_should_remove_empty_tags()
    {
        $divs = $this->dom->getElementsByClassname("someDiv", "div");
        $last = $divs->last();

        $paragraphs = $last->getElementsByTagName("p");
        $this->assertEquals(3, $paragraphs->count());

        $last->removeEmptyTags('p');
        $paragraphs = $last->getElementsByTagName("p");
        $this->assertEquals(2, $paragraphs->count());
    }

    /** @test */
    public function it_should_query_on_this_element()
    {
        $divs = $this->dom->getElementsByClassname("someDiv", "div");
        $last = $divs->last();

        $query = $last->query()->class("innerP")->get();

        $this->assertEquals(2, $query->count());
    }
}