<?php

namespace Eelcol\LaravelHtmlDom\Tests\Feature;

use Eelcol\LaravelHtmlDom\Support\Dom;
use Eelcol\LaravelHtmlDom\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DomTest extends TestCase
{
	public Dom $dom;

	public function setUp(): void
	{
		parent::setUp();

		$html = $this->getHtml();

		$this->dom = \Eelcol\LaravelHtmlDom\Facades\Dom::loadHtml($html);
	}

	/** @test */
	public function it_should_search_classes()
	{
		$search = $this->dom->searchClass("someDiv");
		$this->assertEquals($search->count(), 4);

		$search = $this->dom->searchClass("someDiv", "div");
		$this->assertEquals($search->count(), 3);

		// find .innerSpan inside 1 specific div
		// option 1: use the dom
		$last_item = $search->last()->getNode();
		$search = $this->dom->searchClass("innerSpan", "span", $last_item);
		$this->assertEquals($search->count(), 1);

		$search = $this->dom->searchClass("someDiv", "footer");
		$this->assertEquals($search->count(), 1);

		$search = $this->dom->searchClass("anotherClassName");
		$this->assertEquals($search->count(), 0);

		$search = $this->dom->searchClass("anotherClassName", "div");
		$this->assertEquals($search->count(), 0);
	}

	/** @test */
	public function it_should_search_attributes()
	{
		$search = $this->dom->searchWithAttribute("data-top-level", "1");
		$this->assertEquals($search->count(), 4);

		$search = $this->dom->searchWithAttribute("data-some-attribute", "yes");
		$this->assertEquals($search->count(), 1);

		$search = $this->dom->searchWithAttribute("data-top-level", "1", "div");
		$this->assertEquals($search->count(), 3);

		$search = $this->dom->searchWithAttribute("data-some-not-existing-attribute", "yes");
		$this->assertEquals($search->count(), 0);
	}

	/** @test */
	public function it_should_return_elements_with_attribute()
	{
		$search = $this->dom->searchHasAttribute("data-top-level");
		$this->assertEquals($search->count(), 4);

		$search = $this->dom->searchHasAttribute("data-div-item");
		$this->assertEquals($search->count(), 2);

		$search = $this->dom->searchHasAttribute("data-top-level", "div");
		$this->assertEquals($search->count(), 3);

		$search = $this->dom->searchHasAttribute("not-existing-attribute");
		$this->assertEquals($search->count(), 0);
	}

	/** @test */
	public function it_should_check_if_attribute_values_contains()
	{
		$search = $this->dom->searchAttributeContains("data-top-level", 2);
		$this->assertEquals($search->count(), 0);

		$search = $this->dom->searchAttributeContains("data-some-attribute", 'ye');
		$this->assertEquals($search->count(), 1);

		$search = $this->dom->searchAttributeContains("data-some-attribute", 'yes');
		$this->assertEquals($search->count(), 1);

		$search = $this->dom->searchAttributeContains("data-some-attribute", 'yess');
		$this->assertEquals($search->count(), 0);
	}

	/** @test */
	public function it_should_extract_linking_data()
	{
		$data = $this->dom->getAllLinkedData();

		$this->assertTrue(isset($data['Product']));
		$this->assertEquals($data['Product'][0]['name'], "Some product name");
		$this->assertEquals($data['Product'][0]['image'], "https://via.placeholder.com/728x90.png");
		$this->assertEquals($data['Product'][0]['url'], "https://www.nu.nl");
		$this->assertEquals($data['Product'][0]['brand']['name'], "HP");
		$this->assertEquals($data['Product'][0]['description'], "Some description");
		$this->assertEquals($data['Product'][0]['offers']['price'], 199);
	}

	/** @test */
	public function it_should_search_a_string()
	{
		$search = $this->dom->findString("This paragraph contains some text");
		$this->assertEquals($search->count(), 2);

		$search = $this->dom->findString("This last paragraph contains another text");
		$this->assertEquals($search->count(), 1);

		$search = $this->dom->findString("Some other text");
		$this->assertEquals($search->count(), 0);

	}
}