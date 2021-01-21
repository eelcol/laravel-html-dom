<?php 

namespace Eelcol\LaravelHtmlDom\Support;

use ArrayAccess;
use DOMElement as DOMElementCore;
use DOMNodeList as DOMNodeListCore;
use Eelcol\LaravelHtmlDom\Support\DomElement;
use Eelcol\LaravelHtmlDom\Support\DomNodeList;
use Iterator;

class DomNodeList implements Iterator, ArrayAccess
{
    /**
    * @var array<DOMNodeListCore>
    */
	protected array $nodeLists = [];
	protected array $nodeListArray;

	public function __construct(DOMNodeListCore $nodeList)
	{
		$this->nodeLists[] 	 = $nodeList;

        // convert the nodelist to an array
        // for easier access with the ArrayAccess and Iterator functionalities
		$this->nodeListArray = iterator_to_array($nodeList);
	}

    public function merge(DOMNodeListCore $nodeList)
    {
        $this->nodeLists[]   = $nodeList;

        // convert the nodelist to an array
        // for easier access with the ArrayAccess and Iterator functionalities
        $this->nodeListArray = array_merge($this->nodeListArray, iterator_to_array($nodeList));
    }

    /**
    * Return all elements by tag name from the list of elements
    * @return DomNodeList
    */
    public function getElementsByTagName(string $tag)
    {
        return $this->createNewNodeList(function ($node) use ($tag) {
            return $node->searchElements($tag);
        });
    }

    public function searchClass(string $class, string $element="*")
    {
        return $this->createNewNodeList(function ($node) use ($class, $element) {
            return $node->searchClass($class, $element);
        });
    }

    private function createNewNodeList($callback)
    {
        $list_to_return;
        foreach ($this->nodeListArray as $node) {
            $nodeList = $callback(new DomElement($node))->getNodeList();

            if (!isset($list_to_return)) {
                $list_to_return = new self($nodeList);
            } else {
                $list_to_return->merge($nodeList);
            }
        }

        return $list_to_return;
    }

    public function getNodeList()
    {
        return $this->nodeLists[0];
    }

	public function first()
	{
		if(count($this->nodeListArray) == 0) return NULL;

		return new DomElement($this->nodeListArray[0]);
	}

    public function last()
    {
        if(count($this->nodeListArray) == 0) return NULL;

        return new DomElement($this->nodeListArray[ count($this->nodeListArray) - 1 ]);
    }

    public function count(): int
    {
        return count($this->nodeListArray);
    }

    public function item($index)
    {
        if (!isset($this->nodeListArray[$index])) {
            return null;
        }

        return new DomElement($this->nodeListArray[$index]);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->nodeListArray[$offset]);
    }

    public function offsetGet($offset)
    {
        if (isset($this->nodeListArray[$offset])) {
            return new DomElement($this->nodeListArray[$offset]);
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        // not possible
    }

    public function offsetUnset($offset)
    {
        // not possible
    }

	/**
	* Iterator functions
	*/
	public function rewind()
    {
        reset($this->nodeListArray);
    }
  
    public function current()
    {
        $var = current($this->nodeListArray);
        return new DomElement($var);
    }
  
    public function key() 
    {
        $var = key($this->nodeListArray);
        return $var;
    }
  
    public function next() 
    {
        $var = next($this->nodeListArray);
        return $var;
    }
  
    public function valid()
    {
        $key = key($this->nodeListArray);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }
}