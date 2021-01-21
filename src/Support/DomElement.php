<?php 

namespace Eelcol\LaravelHtmlDom\Support;

use DOMDocument;
use DOMElement as DOMElementCore;
use DOMNodeList as DOMNodeListCore;
use DOMXPath;
use Eelcol\LaravelHtmlDom\Facades\Dom;
use Eelcol\LaravelHtmlDom\Support\DomElement;
use Eelcol\LaravelHtmlDom\Support\DomNodeList;

class DomElement
{
	private DOMElementCore $element;

    private DOMXPath $xpath;

	public function __construct(DOMElementCore $element)
	{
		$this->element = $element;
	}

    public function __get($name)
    {
        return $this->element->{$name};
    }

    public function getNode()
    {
        return $this->element;
    }

    public function __call($method, $params)
    {
        $return = call_user_func_array([$this->element, $method], $params);

        if (is_object($return)) {

            if (is_a($return, DOMNodeListCore::class)) {
                return new DomNodeList($return);
            }

            if (is_a($return, DOMElementCore::class)) {
                return new DomElement($return);
            }
        }
        
        return $return;
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
    */
    public function searchClass(string $class, string $element = "*")
    {
        $dom    = Dom::loadHtml('<?xml encoding="utf-8" ?>' . $this->getInnerHtml());
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
        $dom    = Dom::loadHtml('<?xml encoding="utf-8" ?>' . $this->getInnerHtml());

        return $dom->searchWithAttribute($attribute_key, $attribute_value, $element);
    }

    public function getParent()
    {
        $parent = $this->parentNode;

        if (!$parent || is_a($parent, DOMDocument::class)) {
            return null;
        }

        return new DomElement($parent);
    }

}