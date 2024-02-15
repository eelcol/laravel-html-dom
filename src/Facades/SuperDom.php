<?php

namespace Eelcol\LaravelHtmlDom\Facades;

use Eelcol\LaravelHtmlDom\Support\SuperDom as SuperDomObject;

class SuperDom
{
	public static function loadHtml(string $html): SuperDomObject
	{
		return (new SuperDomObject())->setHtml($html);
	}
}