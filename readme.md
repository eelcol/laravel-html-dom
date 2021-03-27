# Laravel HTML Dom

A Laravel wrapper around the `Dom` classes of PHP.

# Example

- Load HTML:

```
$dom = Dom::loadHtml('<html-string>');
```

- Search class

```
$dom->searchClass('class', 'element');
$dom->searchClass(['class1','class2'], 'element');
```

- Perform a query

This query will find all divs which contains both `class1` and `class2` class.

```
$dom->query()
	->element('div')
	->class('class1')
	->class('class2')
	->get();
```

Multiple queries can be combined with `or`:

```
$dom->query()
	->element('div')
	->class('class1')
	->class('class2')
	->or(function ($q) {
		$q->element('ul')
			->class('list');
	})
	->get();
```

- Perform an XPath query on the document:
```
$dom->query("a[...]");
```

- Perform an XPath query on an element:
```
$elements = $dom->getElementsByTagName("a");
$element = $elements->first();

$element->xpath("....");
```

- Get the next sibling
```
$element->getNextSibling();
$element->getNextSibling("span");
```

# Installation

Require this package with composer.

````
composer require eelcol/laravel-html-dom
````
