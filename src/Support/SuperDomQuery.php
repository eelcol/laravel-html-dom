<?php 

namespace Eelcol\LaravelHtmlDom\Support;

use DOMElement;
use DOMXPath;

class SuperDomQuery
{
	protected SuperDom $dom;

	protected ?SuperDomElement $node = null;

	protected array $search = [];

	protected array $orWhere = [];

	protected string $searchString = '';

	protected string $element = "*";

	protected bool $is_build = false;

	public function setDom(SuperDom $dom): self
	{
		$this->dom = $dom;

		return $this;
	}

	public function setNode(SuperDomElement $element): self
	{
		$this->node = $element;

		return $this;
	}

	public function element(string $element): self
	{
		$this->element = $element;

		return $this;
	}

	public function class(string $classname): self
	{
		$this->search[] = [
			'type' => 'class',
			'classname' => $classname
		];

		return $this;
	}

	public function or($callback): self
	{
		$newQuery = new self();

		$callback->call($this, $newQuery);

		$this->orWhere[] = $newQuery->getSearchString();

		return $this;
	}

	/**
	* Create the Xpath query string
	*/
	public function get(): SuperDomList
	{
        $this->buildSearchString();

        if ($this->node) {
            return $this->dom->queryInNode($this->getSearchString(), $this->node);
        }

        return $this->dom->query($this->getSearchString());
    }

    public function first(): SuperDomElement
    {
    	return $this->get()->first();
    }

    public function dd(): void
    {
    	$this->buildSearchString();

        dd($this->searchString);
    }

    public function getSearchString(): string
    {
    	if (!$this->is_build) {
    		$this->buildSearchString();
    	}

    	return $this->searchString;
    }

    protected function addGroup($callback): void
    {
    	if ($this->searchString !== "") {
    		$this->searchString .= " and ";
    	}

    	$callback->call($this);
    }

    protected function buildSearchString(): void
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

    protected function buildClass($search): void
    {
		$this->searchString .= "contains(concat(' ', normalize-space(@class), ' '), ' ".$search['classname']." ')";

		$this->is_build = false;
	}
}