<?php

declare(strict_types=1);

namespace TerryApiBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use TerryApiBundle\DependencyInjection\TerryApiExtension;

/**
 * @covers \TerryApiBundle\DependencyInjection\TerryApiExtension
 */
class TerryApiExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @dataProvider providerForEntryPointServiceIds
     */
    public function testShouldCheckDefaultServiceLoad(string $serviceId): void
    {
        $this->load();

        $this->assertContainerBuilderHasService($serviceId);
    }

    public function providerForEntryPointServiceIds(): array
    {
        return [
            [
                'terry_api.error.validation_exception_listener'
            ],
            [
                'terry_api.error.error_listener'
            ],
            [
                'terry_api.http_api.http_api_reader'
            ],
            [
                'terry_api.negotiation.content_negotiator'
            ],
            [
                'terry_api.response.response_builder'
            ],
            [
                'terry_api.response.response_listener'
            ],
            [
                'terry_api.request.body_argument_resolver'
            ],
            [
                'terry_api.request.query_string_argument_resolver'
            ],
            [
                'terry_api.serialize.format_mapper'
            ],
            [
                'terry_api.serialize.serializer'
            ],
            [
                'terry_api.validation.validator'
            ]
        ];
    }

    /**
     * @dataProvider providerShouldCheckServiceConfigurationArguments
     */
    public function testShouldCheckServiceConfigurationArguments(string $serviceId, int $argNo, $expected): void
    {
        $this->load();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument($serviceId, $argNo, $expected);
    }

    public function providerShouldCheckServiceConfigurationArguments(): array
    {
        return [
            [
                'terry_api.negotiation.content_negotiator',
                0,
                [
                    'json' => ['application/json'],
                    'xml' => ['application/xml']
                ]
            ],
            [
                'terry_api.negotiation.content_negotiator',
                1,
                'application/json',
            ],
            [
                'terry_api.serialize.format_mapper',
                0,
                [
                    'json' => ['application/json'],
                    'xml' => ['application/xml']
                ]
            ]
        ];
    }

    protected function getContainerExtensions(): array
    {
        return [new TerryApiExtension()];
    }
}
