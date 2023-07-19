<?php 

namespace Eelcol\LaravelHtmlDom\Support;

use DOMDocument;
use DOMElement as DOMElementCore;
use DOMNodeList as DOMNodeListCore;
use Eelcol\LaravelHtmlDom\Facades\Dom as DomFacade;
use Eelcol\LaravelHtmlDom\Support\Dom;
use Eelcol\LaravelHtmlDom\Support\DomElement;
use Eelcol\LaravelHtmlDom\Support\DomNodeList;
use Eelcol\LaravelHtmlDom\Support\DomQuery;

class DomElement
{
    protected DOMElementCore $element;

    protected Dom $domDocument;

    public function __construct(DOMElementCore $element, Dom $domDocument)
    {
        $this->element = $element;

        $this->domDocument = $domDocument;
    }

    public function __get($name)
    {
        return $this->convert($this->element->{$name});
    } 

    public function __call($method, $params)
    {
        $return = call_user_func_array([$this->element, $method], $params);

        return $this->convert($return);
    }

    // perform an xpath expression on the current node
    public function xpath(string $expression)
    {
        return $this->domDocument->query($expression, $this->getNode());
    }

    public function getNode()
    {
        return $this->element;
    }

    public function getNextSibling($tag = null)
    {
        if (is_null($tag)) {
            $tag = "node()";
        }
        
        return $this->xpath("./following-sibling::".$tag."[1]")->first();
    }

    /**
    * Return the outer HTML
    * This includes the current tag
    */
    public function getHtml(): string
    {
        return $this->element->ownerDocument->saveHTML($this->element);
    }

    /**
    * Returns the inner HTML
    * This does NOT include the current tag
    */
    public function getInnerHtml(): string
    {
        $innerHTML = ""; 
        $children  = $this->element->childNodes;

        foreach ($children as $child) 
        { 
            $innerHTML .= $this->element->ownerDocument->saveHTML($child);
        }

        return trim($innerHTML);
    }

    public function nodeValue()
    {
        return trim($this->nodeValue);
    }

    public function hasClass(string $class): bool
    {
        if($this->element->hasAttribute('class') && strstr($this->element->getAttribute('class'), $class))
        {
            return true;
        }

        return false;
    }

    /**
    * Search other elements with a specific class
    * @param string | array $class
    * When supplied an array, all classes must be present
    */
    public function searchClass($class, string $element = "*")
    {
        $dom    = $this->createNewDom();
        $list   = $dom->searchClass($class, $element);

        return $list;
    }

    /**
    * Search other elements by tagname
    */
    public function searchElements(string $tagname = '*')
    {
        return $this->getElementsByTagName($tagname);
    }

    /**
    * Search other elements with attribute
    */
    public function searchWithAttribute(string $attribute_key, string $attribute_value, string $element="*")
    {
        $dom    = $this->createNewDom();

        return $dom->searchWithAttribute($attribute_key, $attribute_value, $element);
    }

    /**
     * Search elements which have a specific attribute key
     */
    public function searchHasAttribute(string $attribute_key, string $element="*")
    {
        $dom    = $this->createNewDom();

        return $dom->searchHasAttribute($attribute_key, $element);
    }

    public function createNewDom(): Dom
    {
        return DomFacade::loadHtml('<?xml encoding="utf-8" ?>' . $this->getInnerHtml());
    }

    public function getParent()
    {
        $parent = $this->parentNode;

        if (!$parent || is_a($parent, DOMDocument::class)) {
            return null;
        }

        if (is_a($parent, DomElement::class)) {
            return new DomElement($parent->getNode(), $this->domDocument);
        }

        return new DomElement($parent, $this->domDocument);
    }

    public function removeElement()
    {
        $this->parentNode->removeChild($this->getNode());
    }

    /**
    * Remove the outer element
    * But keep the children
    */
    public function removeOuterElement()
    {
        $node = $this->getNode();
        
        while ($node->hasChildNodes()) {
            $child = $node->removeChild($node->firstChild);
            $node->parentNode->insertBefore($child, $node);
        }

        // Remove the tag.
        $node->parentNode->removeChild($node);
    }

    public function removeEmptyTags(string $element)
    {
        foreach ($this->searchElements($element) as $elem) {
            if($elem->childNodes->count() == 0 && $elem->nodeValue() == "") {
                // the element is empty
                $elem->removeElement();
            }
        }
    }

    public function query()
    {
        return (new DomQuery())->setDom($this->domDocument)->setNode($this);
    }

    protected function convert($mixed_value)
    {
        if (is_object($mixed_value)) {

            if (is_a($mixed_value, DOMNodeListCore::class)) {
                return new DomNodeList($mixed_value, $this->domDocument);
            }

            if (is_a($mixed_value, DOMElementCore::class)) {
                return new DomElement($mixed_value, $this->domDocument);
            }
        }
        
        return $mixed_value;
    }
}