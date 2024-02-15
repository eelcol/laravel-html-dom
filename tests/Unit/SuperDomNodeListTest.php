<?php

namespace Eelcol\LaravelHtmlDom\Tests\Unit;

use Eelcol\LaravelHtmlDom\Support\SuperDom;
use Eelcol\LaravelHtmlDom\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SuperDomNodeListTest extends TestCase
{
	public SuperDom $dom;

	public function setUp(): void
	{
		parent::setUp();

		$html = $this->getHtml();

		$this->dom = \Eelcol\LaravelHtmlDom\Facades\SuperDom::loadHtml($html);
	}

	/** @test */
	public function it_should_return_the_first()
	{
		$search = $this->dom->getElementsByClassname("someDiv");
		$element = $search->first();
		$html = $element->getHtml();

		$this->assertStringContainsString('<div class="someDiv anotherClass" data-div-item="1" data-top-level="1">', $html);
		$this->assertStringContainsString('<span class="innerSpan innerSpanFirst" data-some-attribute="yes">', $html);
		$this->assertStringContainsString('<p class="innerP" data-paragraph-element>This first paragraph contains some text</p>', $html);
	}

	/** @test */
	public function it_should_return_the_last()
	{
		$search = $this->dom->getElementsByClassname("someDiv");
		$element = $search->last();
		$html = $element->getHtml();

		$this->assertStringContainsString('<footer class="someDiv" data-top-level="1">', $html);
	}

	/** @test */
	public function it_should_return_the_index()
	{
		$search = $this->dom->getElementsByClassname("someDiv");
		$element = $search->item(0);
		$html = $element->getHtml();

		$this->assertStringContainsString('<div class="someDiv anotherClass" data-div-item="1" data-top-level="1">', $html);
		$this->assertStringContainsString('<span class="innerSpan innerSpanFirst" data-some-attribute="yes">', $html);
		$this->assertStringContainsString('<p class="innerP" data-paragraph-element>This first paragraph contains some text</p>', $html);

		$this->assertEquals(NULL, $search->item(10));
	}

	/** @test */
	public function it_should_loop()
	{
		$search = $this->dom->getElementsByClassname("someDiv");
		$found = 0;

		foreach ($search as $element) {
			$found++;
		}

		$this->assertEquals(4, $found);
	}

	/** @test */
	public function it_should_get_all_tags_from_the_list()
	{
		$search = $this->dom->getElementsByClassname("someDiv");

		$this->assertEquals(5, $search->getElementsByTagName('p')->count());
		$this->assertEquals(8, $search->getElementsByTagName('*')->count());
	}

	/** @test */
	public function it_should_get_all_tags_with_a_class()
	{
		$search = $this->dom->getElementsByClassname("someDiv");

		$this->assertEquals(3, $search->getElementsByClassname('innerSpan')->count());
		$this->assertEquals(1, $search->getElementsByClassname('innerSpanFirst')->count());
	}

    /** @test */
    public function it_should_filter_on_class()
    {
        $spans = $this->dom->getElementsByTagName("span");

        $search1 = $spans->hasClass("innerSpan");
        $search2 = $spans->hasClass("anotherClass");

        $this->assertEquals(3, $search1->count());
        $this->assertEquals(1, $search2->count());
    }

    /** @test */
    public function it_should_remove_elements()
    {
        $divs = $this->dom->getElementsByTagName("div");
        $divs->removeElements();

        $html = $this->dom->getHtml();

        $this->assertStringNotContainsString("<div", $html);
        $this->assertStringNotContainsString("This last paragraph contains another text", $html);
    }

    /** @test */
    public function it_should_remove_outer_elements()
    {
        $paragraphs = $this->dom->getElementsByTagName("p");
        $paragraphs->removeOuterElements();

        $html = $this->dom->getHtml();

        $this->assertStringNotContainsString("<p", $html);
        $this->assertStringContainsString("This last paragraph contains another text", $html);
    }
}