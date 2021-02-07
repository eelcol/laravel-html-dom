<?php 

namespace Eelcol\LaravelHtmlDom\Support;

use DOMElement as DOMElementCore;
use DOMXPath;
use Eelcol\LaravelHtmlDom\Support\Dom;
use Eelcol\LaravelHtmlDom\Support\DomElement;

class DomQuery
{
	protected Dom $dom;

	protected ?DOMElementCore $node = null;

	protected array $search = [];

	protected array $orWhere = [];

	protected string $searchString = '';

	protected string $element = "*";

	protected bool $is_build = false;

	public function setDom(Dom $dom)
	{
		$this->dom = $dom;

		return $this;
	}

	public function setNode(DomElement $element)
	{
		$this->node = $element->getNode();

		return $this;
	}

	public function element(string $element)
	{
		$this->element = $element;

		return $this;
	}

	public function class(string $classname)
	{
		$this->search[] = [
			'type' => 'class',
			'classname' => $classname
		];

		return $this;
	}

	public function or($callback)
	{
		$newQuery = new self();

		$callback->call($this, $newQuery);

		$this->orWhere[] = $newQuery->getSearchString();

		return $this;
	}

	/**
	* Create the Xpath query string
	*/
	public function get()
	{
        $this->buildSearchString();

        return $this->dom->query($this->getSearchString(), $this->node);
    }

    public function first()
    {
    	return $this->get()->first();
    }

    public function dd()
    {
    	$this->buildSearchString();

        dd($this->searchString);
    }

    public function getSearchString()
    {
    	if (!$this->is_build) {
    		$this->buildSearchString();
    	}

    	return $this->searchString;
    }

    protected function addGroup($callback) {
    	if ($this->searchString !== "") {
    		$this->searchString .= " and ";
    	}

    	$callback->call($this);
    }

    protected function buildSearchString()
    {
    	foreach ($this->search as $search) {
        	$method_name = 'build' . ucfirst($search['type']);

        	$this->addGroup(function () use ($search, $method_name) {
        		$this->{$method_name}($search);
        	});
        }

        // now add element
        $this->searchString = "//" . $this->element . "[" . $this->searchString . "]";

        if ($this->node) {
        	$this->searchString = "." . $this->searchString;
        }

        // check orWheres
        foreach ($this->orWhere as $or) {
        	if ($this->node) {
	        	$or = "." . $or;
	        }
        	$this->searchString .= " | " . $or;
        }

        $this->is_build = true;
    }

    protected function buildClass($search)
    {
		$this->searchString .= "contains(concat(' ', normalize-space(@class), ' '), ' ".$search['classname']." ')";

		$this->is_build = false;
	}
}