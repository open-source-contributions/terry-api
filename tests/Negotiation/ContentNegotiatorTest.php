<?php

declare(strict_types=1);

namespace TerryApiBundle\Tests\Negotiation;

use PHPUnit\Framework\TestCase;
use TerryApiBundle\Negotiation\ContentNegotiator;
use TerryApiBundle\Negotiation\NotNegotiableException;
use TerryApiBundle\Request\AcceptHeader;
use TerryApiBundle\Tests\Stubs\Config;
use TerryApiBundle\Tests\Stubs\MimeTypes;

/**
 * @covers TerryApiBundle\Negotiation\ContentNegotiator
 * @uses \TerryApiBundle\Negotiation\NotNegotiableException
 * @uses \TerryApiBundle\Request\AcceptHeader
 */
class ContentNegotiatorTest extends TestCase
{
    private ContentNegotiator $contentNegotiator;

    public function setUp(): void
    {
        parent::setUp();

        $this->contentNegotiator = new ContentNegotiator(Config::SERIALIZE_FORMATS, Config::SERIALIZE_FORMAT_DEFAULT);
    }

    /**
     * @dataProvider providerShouldNegotiateContentType
     */
    public function testShouldNegotiateContentType(string $expected, string $accept): void
    {
        $accept = AcceptHeader::fromString($accept);

        $this->assertEquals($expected, $this->contentNegotiator->negotiate($accept)->toString());
    }

    public function providerShouldNegotiateContentType(): array
    {
        return [
            [MimeTypes::APPLICATION_XML, 'application/pdf, application/xml'],
            [MimeTypes::APPLICATION_JSON, '*/*'],
            [MimeTypes::APPLICATION_JSON, 'random/random, */*'],
            [MimeTypes::APPLICATION_JSON, 'application/*, random/random'],
            [MimeTypes::APPLICATION_JSON, 'application/xml;q=0.9,application/json;q=1.0,*/*;q=0.8'],
            [MimeTypes::APPLICATION_JSON, 'application/xml;q=0.9,application/json,*/*;q=0.8'],
            [MimeTypes::APPLICATION_JSON, 'application/xml;q=0.9,text/html;q=0.8,*/*'],
            [MimeTypes::APPLICATION_JSON, ''],
        ];
    }

    /**
     * @dataProvider providerShouldThrowNotNegotiatableException
     */
    public function testShouldThrowNotNegotiatableException(string $accept): void
    {
        $this->expectException(NotNegotiableException::class);

        $accept = AcceptHeader::fromString($accept);

        $this->contentNegotiator->negotiate($accept)->toString();
    }

    public function providerShouldThrowNotNegotiatableException(): array
    {
        return [
            ['randomstringButNotEmpty'],
            ['application/random']
        ];
    }
}
