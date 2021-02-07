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

    protected function getPackageProviders($app)
    {
        return [
            HtmlDomServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }

    protected function getHtml()
    {
        return <<<EOD
        <html><head><script type="application/ld+json">
        {
            "@context": "http://schema.org/",
            "@type": "Product",
            "@id": "https://www.google.nl/id.html",
            "name": "Some product name",
            "image": "https://via.placeholder.com/728x90.png",
            "url": "https://www.nu.nl",
            "brand": {
                "@type": "Brand",
                "name": "HP"
            },
            "description": "Some description",
            "sku": "123456",
            "offers": {
                "@type": "Offer",
                "priceCurrency": "EUR",
                "price": "199",
                "availability": "http://schema.org/InStock",
                "itemCondition": "http://schema.org/NewCondition"
            },
            "aggregateRating": {
                "@type": "AggregateRating",
                "worstRating": "1",
                "bestRating": "10",
                "ratingValue": "8.5",
                "reviewCount": "37"
            }
        }
        </script>
        </head>
        <body><div class="someDiv anotherClass" data-div-item="1" data-top-level="1">
            <span class="innerSpan innerSpanFirst" data-some-attribute="yes">
                <p class="innerP" data-paragraph-element>This paragraph contains some text</p>
            </span>
        </div>
        <div class="someDiv anotherClass" data-div-item="2" data-top-level="1">
            <span class="innerSpan">
                <p class="innerP" data-paragraph-element>This paragraph contains some text</p>
            </span>
        </div>
        <div class="someDiv anotherClass anotherDivClassLast" data-top-level="1">
            <span class="innerSpan anotherClass">
                <p class="innerP" data-paragraph-element>This last paragraph contains another text</p>
            </span>
        </div>
        <footer class="someDiv" data-top-level="1">
        </footer></body>
        EOD;
    }
}