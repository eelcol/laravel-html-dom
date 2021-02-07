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

```
$dom->query()
	->element('div')
	->class('class1')
	->class('class2')
	->get();
```

# Installation

Require this package with composer.

````
composer require eelcol/laravel-html-dom
````
