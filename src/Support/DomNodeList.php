<?php 

namespace Eelcol\LaravelHtmlDom\Support;

use ArrayAccess;
use DOMNodeList as DOMNodeListCore;
use Iterator;

class DomNodeList implements Iterator, ArrayAccess
{
    /**
    * @var array<DOMNodeListCore>
    */
    protected array $nodeLists = [];

    protected Dom $domDocument;
    
    protected array $nodeListArray;

    public function __construct(DOMNodeListCore $nodeList, Dom $domDocument)
    {
        $this->nodeLists[]   = $nodeList;

        $this->domDocument = $domDocument;

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

    /**
    * Return all elements WITHIN this result list which has this class
    * So it does NOT search the top-level elements
    */
    public function searchClass(string $class, string $element="*")
    {
        return $this->createNewNodeList(function ($node) use ($class, $element) {
            return $node->searchClass($class, $element);
        });
    }

    /**
    * Return all top-level elements which has this class
    */
    public function hasClass(string $class)
    {
        return $this->filter(function ($node) use ($class) {
            return $node->hasClass($class);
        });
    }

    private function createNewNodeList($callback)
    {
        $list_to_return;
        foreach ($this->nodeListArray as $node) {
            $nodeList = $callback(new DomElement($node, $this->domDocument))->getNodeList();

            if (!isset($list_to_return)) {
                $list_to_return = new self($nodeList, $this->domDocument);
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

    public function filter($callback)
    {
        $return = new self($this->getNodeList(), $this->domDocument);
        $unsetItems = [];

        foreach ($this->nodeListArray as $index => $node) {
            $filter = $callback(new DomElement($node, $this->domDocument));

            if ($filter !== true) {
                $unsetItems[] = $index;
            }
        }

        $return->unsetItems($unsetItems);

        return $return;
    }

    public function first()
    {
        if(count($this->nodeListArray) == 0) return NULL;

        return new DomElement($this->nodeListArray[0], $this->domDocument);
    }

    public function last()
    {
        if(count($this->nodeListArray) == 0) return NULL;

        return new DomElement($this->nodeListArray[ count($this->nodeListArray) - 1 ], $this->domDocument);
    }

    public function count(): int
    {
        return count($this->nodeListArray);
    }

    public function item($index): ?DomElement
    {
        if (!isset($this->nodeListArray[$index])) {
            return null;
        }

        return new DomElement($this->nodeListArray[$index], $this->domDocument);
    }

    public function unsetItem(int $index)
    {
        if (isset($this->nodeListArray[$index])) {
            unset($this->nodeListArray[$index]);
            $this->nodeListArray = array_values($this->nodeListArray);
        }
    }

    public function unsetItems(array $indexes)
    {
        foreach ($indexes as $index) {
            if (isset($this->nodeListArray[$index])) {
                unset($this->nodeListArray[$index]);
            }
        }

        $this->nodeListArray = array_values($this->nodeListArray);
    }

    public function removeElements()
    {
        foreach ($this as $el) {
            $el->removeElement();
        }
    }

    public function removeOuterElements()
    {
        foreach ($this as $el) {
            $el->removeOuterElement();
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->nodeListArray[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (isset($this->nodeListArray[$offset])) {
            return new DomElement($this->nodeListArray[$offset], $this->domDocument);
        }

        return null;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        // not possible
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        // not possible
    }

    /**
    * Iterator functions
    */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->nodeListArray);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        $var = current($this->nodeListArray);
        return new DomElement($var, $this->domDocument);
    }

    #[\ReturnTypeWillChange]
    public function key() 
    {
        return key($this->nodeListArray);
    }

    #[\ReturnTypeWillChange]
    public function next() 
    {
        $var = next($this->nodeListArray);
        return $var;
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        $key = key($this->nodeListArray);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }
}