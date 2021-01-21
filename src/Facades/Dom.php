<?php

namespace Eelcol\LaravelHtmlDom\Facades;

use Eelcol\LaravelHtmlDom\Support\Dom as DomObject;

class Dom
{
	public static function loadHtml(string $html)
	{
		return (new DomObject())->setHtml($html);
	}
}