<?php 

namespace Eelcol\LaravelHtmlDom\Support;

use DOMDocument;
use DOMXPath;

class SuperDom
{
	protected DOMDocument $dom;

	protected DOMXPath $xpath;

	public function setHtml(string $html): self
	{
		$this->dom = new DOMDocument();
		libxml_use_internal_errors(true);
		$this->dom->loadHtml($html);

		return $this;
	}

    public function getHtml(): string
    {
        return $this->dom->saveHTML();
    }

    public function getElementsByTagName(string $tagname): SuperDomList
    {
        return new SuperDomList($this, $this->dom->getElementsByTagName($tagname));
    }

    public function getElementById(string $id): ?SuperDomElement
    {
        $element = $this->dom->getElementById($id);
        if (!$element) {
            return null;
        }

        return new SuperDomElement($element, $this);
    }

	public function getDom(): DOMDocument
	{
		return $this->dom;
	}

	/**
	* Search elements with a class
	* @param string | array $class : when an array of classes is given, search for elements containing all classes
	* @param string $element (div, p, etc.)
	* @return SuperDomList
	*/
	public function getElementsByClassname(mixed $class, string $element="*"): SuperDomList
	{
        // first build the concat string
        $concatString = "";
        foreach ((array) $class as $className) {
            if ($concatString != "") {
                $concatString .= " and ";
            }
            $concatString .= "contains(concat(' ', normalize-space(@class), ' '), ' ".$className." ')";
        }

    	return $this->query("//".$element."[".$concatString."]");
    }

    public function getElementsByAttribute(string $attribute_key, string $attribute_value, string $element="*"): SuperDomList
    {
    	return $this->query("//".$element."[@".$attribute_key."='".$attribute_value."']");
    }

    public function getElementsWithAttribute(string $attribute_key, string $element="*"): SuperDomList
    {
    	return $this->query("//".$element."[@".$attribute_key."]");
    }

    /**
     * Search elements which attribute key contains a specific value
     * Example: <div data-key="order-4"></div><div data-key="order-6">
     * These elements can be found:
     * ->getElementsByAttributeContainsValue("data-key", "order-", "div");
     */
    public function getElementsByAttributeContainsValue(string $attribute_key, string $contains_value, string $element="*"): SuperDomList
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

    	$elements = $this->getElementsByAttribute('type', 'application/ld+json', 'script');

    	foreach ($elements as $element) {
    		$value = trim($element->nodeValue());
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
    */
    public function findString(string $text): SuperDomList
    {
    	$allNodes = $this->getElementsByTagName('*'); // Get all DOMElements
		foreach ($allNodes as $node) {
			if (str_contains($node->nodeValue(), $text)) {
				// we found the string
				// only return the most inner html-tag
				// example:
				// <div><p>found string!</p></div>
				// in this case, only return the 'p'-tag
                if ($node->getParent() && $node->getParent()->hasAttribute("dom-search-result")) {
					$node->getParent()->removeAttribute("dom-search-result");
				}

				$node->setAttribute("dom-search-result", "true");
			}
		}

		$elements = $this->getElementsByAttribute("dom-search-result", "true", "*");

		foreach ($elements as $elem) {
			$elem->removeAttribute('dom-search-result');
		}

		return $elements;
    }

    /**
    * When no expression is given, return a new DomQuery object
    */
    public function query(?string $expression = null): SuperDomQuery|SuperDomList
    {
    	if (is_null($expression)) {
        	return (new SuperDomQuery())->setDom($this);
    	}

        return $this->performQuery($expression);
    }

    public function queryInNode(string $expression, SuperDomElement $element): SuperDomList
    {
        return $this->performQuery($expression, $element);
    }

    protected function performQuery(string $expression, ?SuperDomElement $element = null): SuperDomList
    {
        $this->requireXpath();

        if (!$element) {
            $results = $this->xpath->query($expression);
        } else {
            $results = $this->xpath->query($expression, $element->getNode());
        }

        return new SuperDomList($this, $results);
    }

    protected function requireXpath(): void
    {
    	if(!isset($this->xpath))
		{
			$this->xpath = new DOMXPath($this->dom);
		}
    }
}