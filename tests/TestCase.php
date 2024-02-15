<?php

namespace Eelcol\LaravelHtmlDom\Tests;

use Eelcol\LaravelHtmlDom\HtmlDomServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app): array
    {
        return [
            HtmlDomServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }

    protected function getHtml(): string
    {
        //return file_get_contents("https://www.tui.nl/kontiki-beach-resort-curacao-50971728/#prijzen-en-boeken");

        return file_get_contents(base_path("../../../../test.html"));
    }
}