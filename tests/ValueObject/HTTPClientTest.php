<?php

declare(strict_types=1);

namespace TerryApi\Tests\ValueObject;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use TerryApiBundle\Exception\RequestHeaderException;
use TerryApiBundle\ValueObject\HTTPClient;
use TerryApiBundle\ValueObject\HTTPServer;

class HTTPClientTest extends TestCase
{
    /**
     * @Mock
     * @var HttpFoundationRequest
     */
    private \Phake_IMock $request;

    public function setUp(): void
    {
        parent::setUp();

        \Phake::initAnnotations($this);
        \Phake::when($this->request)->getLocale->thenReturn('en_GB');
    }

    public function testShouldReturnSerializerType()
    {
        $this->request->headers = new HeaderBag([
            'Accept' => 'application/pdf, application/xml',
            'Content-Type' => 'application/json'
        ]);

        $httpServer = new HTTPServer(null, [
            'application/json' => 'json',
            'application/xml' => 'xml'
        ]);

        $headers = HTTPClient::fromRequest($this->request, $httpServer);

        $this->assertEquals('xml', $headers->serializerType());
    }

    /**
     * @dataProvider providerShouldThrowExceptionIfContentTypeNotSet
     */
    public function testShouldThrowExceptionIfContentTypeNotSet(array $requestHeaders)
    {
        $this->expectException(RequestHeaderException::class);

        $this->request->headers = new HeaderBag($requestHeaders);

        $httpServer = new HTTPServer(null, [
            'application/json' => 'json',
            'application/xml' => 'xml'
        ]);

        $headers = HTTPClient::fromRequest($this->request, $httpServer);

        $headers->deserializerType();
    }

    public function providerShouldThrowExceptionIfContentTypeNotSet()
    {
        return [
            [
                []
            ],
            [
                [
                    'Content-Type' => ''
                ]
            ],
            [
                [
                    'Content-Type' => 'application/pdf'
                ]
            ],
        ];
    }

    public function testShouldReturnDeserializerType()
    {
        $this->request->headers = new HeaderBag([
            'Accept' => 'application/pdf, application/xml',
            'Content-Type' => 'application/json'
        ]);

        $httpServer = new HTTPServer(null, [
            'application/json' => 'json',
            'application/xml' => 'xml'
        ]);

        $headers = HTTPClient::fromRequest($this->request, $httpServer);

        $this->assertEquals('json', $headers->deserializerType());
    }

    /**
     * @dataProvider providerShouldReturnResponseHeaders
     */
    public function testShouldReturnResponseHeaders(array $requestHeaders, array $expected)
    {
        $this->request->headers = new HeaderBag($requestHeaders);

        $httpServer = new HTTPServer(null, [
            'application/json' => 'json',
            'application/xml' => 'xml'
        ]);

        $headers = HTTPClient::fromRequest($this->request, $httpServer);

        $this->assertEquals($expected, $headers->responseHeaders());
    }

    public function providerShouldReturnResponseHeaders()
    {
        return [
            [
                [
                    'Accept' => 'application/pdf, application/xml',
                    'Content-Type' => 'application/json'
                ],
                [
                    'Content-Type' => 'application/xml',
                ]
            ],
            [
                [
                    'Accept' => '*/*',
                    'Content-Type' => 'application/xml'
                ],
                [
                    'Content-Type' => 'application/json',
                ],
            ],
            [
                [
                    'Accept' => 'random/random, */*',
                    'Content-Type' => 'application/xml'
                ],
                [
                    'Content-Type' => 'application/json',
                ],
            ],
            [
                [
                    'Accept' => 'application/*, random/random',
                    'Content-Type' => 'application/xml'
                ],
                [
                    'Content-Type' => 'application/json',
                ],
            ],
            [
                [
                    'Accept' => 'application/xml;q=0.9,application/json;q=1.0,*/*;q=0.8',
                    'Content-Type' => 'application/xml'
                ],
                [
                    'Content-Type' => 'application/json',
                ],
            ],
            [
                [
                    'Accept' => 'application/xml;q=0.9,application/json,*/*;q=0.8',
                    'Content-Type' => 'application/xml'
                ],
                [
                    'Content-Type' => 'application/json',
                ],
            ],
            [
                [
                    'Accept' => 'application/xml;q=0.9,text/html;q=0.8,*/*',
                    'Content-Type' => 'application/xml'
                ],
                [
                    'Content-Type' => 'application/json',
                ],
            ]
        ];
    }

    /**
     * @dataProvider providerThrowExceptionIfContentNotNegotiatable
     */
    public function testShouldThrowExceptionIfContentNotNegotiatable(array $requestHeaders)
    {
        $this->expectException(RequestHeaderException::class);

        $this->request->headers = new HeaderBag($requestHeaders);

        $httpServer = new HTTPServer(null, [
            'application/json' => 'json',
            'application/xml' => 'xml'
        ]);

        $headers = HTTPClient::fromRequest($this->request, $httpServer);

        $headers->responseHeaders();
    }

    public function providerThrowExceptionIfContentNotNegotiatable()
    {
        return [
            [
                [
                    'Accept' => '',
                    'Content-Type' => 'application/xml'
                ],
            ],
            [
                [
                    'Accept' => 'randomstringButNotEmpty',
                    'Content-Type' => 'application/json'
                ]
            ],
            [
                [
                    'Accept' => 'application/random',
                    'Content-Type' => 'application/json'
                ]
            ]
        ];
    }
}
