<?php 

namespace Eelcol\LaravelHtmlDom\Support;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use Eelcol\LaravelHtmlDom\Facades\SuperDom as DomFacade;
use Exception;

class SuperDomElement
{
    protected ?self $parent;

    public function __construct(
        protected DOMElement $element,
        protected SuperDom $domDocument
    ) {
        //
    }

    // perform an xpath expression on the current node
    public function xpath(string $expression)
    {
        return $this->domDocument->queryInNode($expression, $this);
    }

    public function getNode(): DOMElement
    {
        return $this->element;
    }

    public function getNextSibling(string $tag): SuperDomElement
    {
        // onderstaande kan leiden tot issues
        // omdat een node dan bv een DOMText kan zijn
        //if (is_null($tag)) {
        //    $tag = "node()";
        //}
        
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

    public function nodeValue(): string
    {
        return trim($this->element->nodeValue);
    }

    public function hasChildNodes(): bool
    {
        return $this->element->childNodes->count() > 0;
    }

    public function getAttribute(string $name): string
    {
        return $this->element->getAttribute($name);
    }

    public function hasAttribute(string $name): bool
    {
        return $this->element->hasAttribute($name);
    }

    public function removeAttribute(string $attribute): void
    {
        $this->element->removeAttribute($attribute);
    }

    public function setAttribute(string $attribute, string $value): void
    {
        $this->element->setAttribute($attribute, $value);
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
    * When supplied an array of classes, all classes must be present
    */
    public function getElementsByClassName(mixed $class, string $element = "*"): SuperDomList
    {
        $dom    = $this->createNewDom();

        return $dom->getElementsByClassname($class, $element);
    }

    public function getElementsByTagName(string $tag): SuperDomList
    {
        return new SuperDomList(
            $this->domDocument,
            $this->element->getElementsByTagName($tag)
        );
    }

    public function getElementsByAttribute(string $attribute_key, string $attribute_value, string $element="*"): SuperDomList
    {
        $dom    = $this->createNewDom();
        return $dom->getElementsByAttribute($attribute_key, $attribute_value, $element);
    }

    /**
     * Search elements which have a specific attribute key
     */
    public function getElementsWithAttribute(string $attribute_key, string $element="*"): SuperDomList
    {
        $dom    = $this->createNewDom();
        return $dom->getElementsWithAttribute($attribute_key, $element);
    }

    public function createNewDom(): SuperDom
    {
        return DomFacade::loadHtml('<?xml encoding="utf-8" ?>' . $this->getInnerHtml());
    }

    /**
     * @throws Exception
     */
    public function getParent(): ?SuperDomElement
    {
        if (isset($this->parent)) {
            return $this->parent;
        }

        $parent = $this->element->parentNode;

        if (!$parent || is_a($parent, DOMDocument::class)) {
            return null;
        }

        if (!$parent instanceof DOMElement) {
            throw new Exception('DOMElement expected but got something else.');
        }

        return $this->parent = new SuperDomElement($parent, $this->domDocument);
    }

    public function removeElement(): void
    {
        $this->element->parentNode->removeChild($this->getNode());
    }

    /**
    * Remove the outer element
    * But keep the children
    */
    public function removeOuterElement(): void
    {
        $node = $this->getNode();
        
        while ($node->hasChildNodes()) {
            $child = $node->removeChild($node->firstChild);
            $node->parentNode->insertBefore($child, $node);
        }

        // Remove the tag.
        $node->parentNode->removeChild($node);
    }

    public function removeEmptyTags(string $element): void
    {
        foreach ($this->getElementsByTagName($element) as $elem) {
            if(!$elem->hasChildNodes() && $elem->nodeValue() == "") {
                // the element is empty
                $elem->removeElement();
            }
        }
    }

    public function query(): SuperDomQuery
    {
        return (new SuperDomQuery())->setDom($this->domDocument)->setNode($this);
    }
}