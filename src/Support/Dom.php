<?php 

namespace Eelcol\LaravelHtmlDom\Support;

use DOMDocument;
use DOMElement as DOMElementCore;
use DOMNode;
use DOMNodeList as DOMNodeListCore;
use DOMXPath;
use Eelcol\LaravelHtmlDom\Support\DomElement;
use Eelcol\LaravelHtmlDom\Support\DomNodeList;
use Eelcol\LaravelHtmlDom\Support\DomQuery;

class Dom
{
	protected DOMDocument $dom;

	protected DOMXPath $xpath;

	/**
	* Startpoint of the class
	* Set the HTML to use
	*
	* @param string $html
	* @return this
	*/
	public function setHtml(string $html)
	{
		$this->dom = new DOMDocument;
		libxml_use_internal_errors(true);
		$this->dom->loadHtml($html);

		return $this;
	}

	/**
	* Get a property from the dom
	*/
	public function __get($name)
    {
        return $this->dom->{$name};
    }

    /**
    * Call a function on the DOMDocument
    * @return DomNodeList | DomElement
    */
    public function __call(string $method, array $params = [])
    {
        $return = call_user_func_array([$this->dom, $method], $params);
        
        if (is_object($return)) {

        	if (is_a($return, DOMNodeListCore::class)) {
        		return new DomNodeList($return, $this);
        	}

        	if (is_a($return, DOMElementCore::class)) {
        		return new DomElement($return, $this);
        	}
        }

        return $return;
    }

	public function getDom(): DOMDocument
	{
		return $this->dom;
	}

	/**
	* Search elements with a class
	* @param string | array $class : when an array of classes is given, search for elements containing all classes
	* @param string $element (div, p, etc.)
	* @param \DOMNode $searchIn
	* @return DomNodeList
	*/
	public function searchClass($class, string $element="*", DOMNode $searchIn = NULL)
	{
        // first build the concat string
        $concatString = "";
        foreach ((array) $class as $className) {
            if ($concatString != "") {
                $concatString .= " and ";
            }
            $concatString .= "contains(concat(' ', normalize-space(@class), ' '), ' ".$className." ')";
        }

    	return $this->query("//".$element."[".$concatString."]", $searchIn);
    }

    /**
    * Search elements which have an attribute key and an attribute value
    * @return DomNodeList
    */
    public function searchWithAttribute(string $attribute_key, string $attribute_value, string $element="*")
    {
    	return $this->query("//".$element."[@".$attribute_key."='".$attribute_value."']");
    }

    /**
    * Search elements which have a specific attribute key
    */
    public function searchHasAttribute(string $attribute_key, string $element="*")
    {
    	return $this->query("//".$element."[@".$attribute_key."]");
    }

    /**
    * Search elements which attribute key contains a specific value
    */
    public function searchAttributeContains(string $attribute_key, string $contains_value, string $element="*")
    {
    	return $this->query("//".$element."[contains(@".$attribute_key.",'" . $contains_value . "')]");
    }

    /**
    * Return all linked data from a page
    * https://json-ld.org/
    * @return array
    */
    public function getAllLinkedData(): array
    {
    	$linking_data = [];

    	$elements = $this->searchWithAttribute('type', 'application/ld+json', 'script');

    	foreach ($elements as $element) {
    		$value = trim($element->nodeValue);
    		$value = str_replace("\r\n", " ", $value);
    		$value = str_replace("\t", " ", $value);

    		$json = json_decode($value, true);

    		$type = $json['@type'];

    		$linking_data[$type] = $linking_data[$type] ?? [];

    		$linking_data[$type][] = $json;
    	}

    	return $linking_data;
    }

    /**
    * Search the HTML for a specific string of text
    * and return all parent elements which contains this string
    * @return DomNodeList
    */
    public function findString(string $text)
    {
    	$allNodes = $this->getElementsByTagName('*'); // Get all DOMElements
		foreach ($allNodes as $node) {
			if (strpos($node->nodeValue, $text) !== false) {
				// we found the string
				// only return the most inner html-tag
				// example:
				// <div><p>found string!</p></div>
				// in this case, only return the 'p'-tag
				if (
					is_a($node->parentNode, DOMElementCore::class)
					&& $node->parentNode->hasAttribute("dom-search-result")
				) {
					$node->parentNode->removeAttribute("dom-search-result");
				}

				$node->setAttribute("dom-search-result", "true");
			}
		}

		$elements = $this->searchWithAttribute("dom-search-result", "true", "*");

		foreach ($elements as $elem) {
			$elem->removeAttribute('dom-search-result');
		}

		return $elements;
    }

    /**
    * @param string | null $expression
    * @param DOMNode | null $contextnode
    * @return DomQuery | DomNodeList
    *
    * When no expression is given, return a new DomQuery object
    */
    public function query(?string $expression = null, DOMNode $contextnode = null)
    {
    	if (is_null($expression)) {
        	return (new DomQuery())->setDom($this);
    	}

    	$this->requireXpath();

        if ($contextnode && substr($expression, 0, 1) != ".") {
            $expression = "." . $expression;
        }

    	$results = $this->xpath->query($expression, $contextnode);

	    return new DomNodeList($results, $this);
    }

    protected function requireXpath()
    {
    	if(!isset($this->xpath))
		{
			$this->xpath = new DOMXPath($this->dom);
		}
    }
}