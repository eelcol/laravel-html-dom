<?php 

namespace Eelcol\LaravelHtmlDom\Support;

use ArrayAccess;
use Closure;
use DOMNodeList;
use Iterator;

class SuperDomList implements Iterator, ArrayAccess
{
    /**
    * @var array<DOMNodeList>
    */
    protected array $nodeLists = [];

    protected SuperDom $domDocument;
    
    protected array $nodeListArray = [];

    public function __construct(SuperDom $domDocument, ?DOMNodeList $nodeList = null)
    {
        $this->domDocument = $domDocument;

        if ($nodeList) {
            $this->nodeLists[] = $nodeList;

            // convert the nodelist to an array
            // for easier access with the ArrayAccess and Iterator functionalities
            $this->nodeListArray = iterator_to_array($nodeList);
        }
    }

    public function merge(DOMNodeList $nodeList): void
    {
        $this->nodeLists[]   = $nodeList;

        // convert the nodelist to an array
        // for easier access with the ArrayAccess and Iterator functionalities
        $this->nodeListArray = array_merge($this->nodeListArray, iterator_to_array($nodeList));
    }

    public function getElementsByTagName(string $tag): self
    {
        return $this->createNewNodeList(function (SuperDomElement $node) use ($tag) {
            return $node->getElementsByTagName($tag);
        });
    }

    /**
    * Return all elements WITHIN this result list which has this class
    * So it does NOT search the top-level elements
    */
    public function getElementsByClassName(string $class, string $element="*"): self
    {
        return $this->createNewNodeList(function (SuperDomElement $node) use ($class, $element) {
            return $node->getElementsByClassName($class, $element);
        });
    }

    /**
    * Return all top-level elements which has this class
    */
    public function hasClass(string $class): self
    {
        return $this->filter(function (SuperDomElement $node) use ($class) {
            return $node->hasClass($class);
        });
    }

    private function createNewNodeList(Closure $callback): SuperDomList
    {
        $list_to_return = new self($this->domDocument, null);
        foreach ($this->nodeListArray as $node) {
            $nodeList = $callback(new SuperDomElement($node, $this->domDocument))->getFirstNodeList();

            $list_to_return->merge($nodeList);
        }

        return $list_to_return;
    }

    public function getFirstNodeList()
    {
        return $this->nodeLists[0];
    }

    public function filter($callback): self
    {
        $return = (clone $this);
        $unsetItems = [];

        foreach ($this->nodeListArray as $index => $node) {
            $filter = $callback(new SuperDomElement($node, $this->domDocument));

            if ($filter !== true) {
                $unsetItems[] = $index;
            }
        }

        $return->unsetItems($unsetItems);

        return $return;
    }

    public function first(): ?SuperDomElement
    {
        if(count($this->nodeListArray) == 0) return null;

        return new SuperDomElement($this->nodeListArray[0], $this->domDocument);
    }

    public function last(): ?SuperDomElement
    {
        if(count($this->nodeListArray) == 0) return null;

        return new SuperDomElement($this->nodeListArray[ count($this->nodeListArray) - 1 ], $this->domDocument);
    }

    public function count(): int
    {
        return count($this->nodeListArray);
    }

    public function item($index): ?SuperDomElement
    {
        if (!isset($this->nodeListArray[$index])) {
            return null;
        }

        return new SuperDomElement($this->nodeListArray[$index], $this->domDocument);
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
            return new SuperDomElement($this->nodeListArray[$offset], $this->domDocument);
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
    public function rewind(): void
    {
        reset($this->nodeListArray);
    }

    public function current(): SuperDomElement
    {
        $var = current($this->nodeListArray);
        return new SuperDomElement($var, $this->domDocument);
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

    public function valid(): bool
    {
        $key = key($this->nodeListArray);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }
}