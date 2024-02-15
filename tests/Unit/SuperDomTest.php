<?php

namespace Eelcol\LaravelHtmlDom\Tests\Unit;

use Eelcol\LaravelHtmlDom\Support\SuperDom;
use Eelcol\LaravelHtmlDom\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SuperDomTest extends TestCase
{
	public SuperDom $dom;

	public function setUp(): void
	{
		parent::setUp();

		$html = $this->getHtml();

		$this->dom = \Eelcol\LaravelHtmlDom\Facades\SuperDom::loadHtml($html);
	}

    /** @test */
    public function it_should_search_tags()
    {
        $tags = $this->dom->getElementsByTagName("div");
        $this->assertEquals(3, $tags->count());

        $ps = $tags->getElementsByTagName("p");
        $this->assertEquals(4, $ps->count());

        $null = $this->dom->getElementsByTagName("section");
        $this->assertEquals(0, $null->count());
    }

    /** @test */
    public function it_should_search_by_id()
    {
        $element = $this->dom->getElementById("lastdiv");
        $this->assertNotNull($element);

        $null = $this->dom->getElementById("null");
        $this->assertNull($null);
    }

	/** @test */
	public function it_should_search_classes()
	{
		$search = $this->dom->getElementsByClassname("someDiv");
		$this->assertEquals(4, $search->count());

		$search = $this->dom->getElementsByClassname("someDiv", "div");
		$this->assertEquals(3, $search->count());

		// find .innerSpan inside 1 specific div
		// option 1: use the dom
		$last_item = $search->last();
        $search = $last_item->getElementsByClassName("innerSpan", "span");
		$this->assertEquals(1, $search->count());

		$search = $this->dom->getElementsByClassName("someDiv", "footer");
		$this->assertEquals(1, $search->count());

		$search = $this->dom->getElementsByClassName("anotherClassName");
		$this->assertEquals(0, $search->count());

		$search = $this->dom->getElementsByClassName("anotherClassName", "div");
		$this->assertEquals(0, $search->count());
	}

    /** @test */
    public function it_should_search_multiple_classes()
    {
        $search = $this->dom->getElementsByClassname(["someDiv","anotherDivClassLast"]);
        $this->assertEquals(1, $search->count());
    }

	/** @test */
	public function it_should_search_attributes()
	{
		$search = $this->dom->getElementsByAttribute("data-top-level", "1");
		$this->assertEquals(4, $search->count());

		$search = $this->dom->getElementsByAttribute("data-some-attribute", "yes");
		$this->assertEquals(1, $search->count());

		$search = $this->dom->getElementsByAttribute("data-top-level", "1", "div");
		$this->assertEquals(3, $search->count());

		$search = $this->dom->getElementsByAttribute("data-some-not-existing-attribute", "yes");
		$this->assertEquals(0, $search->count());
	}

	/** @test */
	public function it_should_return_elements_with_attribute()
	{
		$search = $this->dom->getElementsWithAttribute("data-top-level");
		$this->assertEquals(4, $search->count());

		$search = $this->dom->getElementsWithAttribute("data-div-item");
		$this->assertEquals(2, $search->count());

		$search = $this->dom->getElementsWithAttribute("data-top-level", "div");
		$this->assertEquals(3, $search->count());

		$search = $this->dom->getElementsWithAttribute("not-existing-attribute");
		$this->assertEquals(0, $search->count());
	}

	/** @test */
	public function it_should_check_if_attribute_values_contains()
	{
		$search = $this->dom->getElementsByAttributeContainsValue("data-top-level", 2);
		$this->assertEquals(0, $search->count());

		$search = $this->dom->getElementsByAttributeContainsValue("data-some-attribute", 'ye');
		$this->assertEquals(1, $search->count());

		$search = $this->dom->getElementsByAttributeContainsValue("data-some-attribute", 'yes');
		$this->assertEquals(1, $search->count());

		$search = $this->dom->getElementsByAttributeContainsValue("data-some-attribute", 'yess');
		$this->assertEquals(0, $search->count());
	}

	/** @test */
	public function it_should_extract_linking_data()
	{
		$data = $this->dom->getAllLinkedData();

		$this->assertTrue(isset($data['Product']));
		$this->assertEquals("Some product name", $data['Product'][0]['name']);
		$this->assertEquals("https://via.placeholder.com/728x90.png", $data['Product'][0]['image']);
		$this->assertEquals("https://www.nu.nl", $data['Product'][0]['url']);
		$this->assertEquals("HP", $data['Product'][0]['brand']['name']);
		$this->assertEquals("Some description", $data['Product'][0]['description']);
		$this->assertEquals(199, $data['Product'][0]['offers']['price']);
	}

	/** @test */
	public function it_should_search_a_string()
	{
		$search = $this->dom->findString("This paragraph contains some text");
		$this->assertEquals(2, $search->count());

		$search = $this->dom->findString("This last paragraph contains another text");
		$this->assertEquals(2, $search->count());

		$search = $this->dom->findString("Some other text");
		$this->assertEquals(0, $search->count());
	}
}