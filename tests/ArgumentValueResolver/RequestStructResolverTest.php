<?php

declare(strict_types=1);

namespace TerryApi\Tests\ArgumentValueResolver;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;
use TerryApiBundle\Annotation\Struct;
use TerryApiBundle\Annotation\StructReader;
use TerryApiBundle\ArgumentValueResolver\RequestStructResolver;
use TerryApiBundle\Exception\AnnotationNotFoundException;
use TerryApiBundle\Tests\Stubs\CandyStructStub;

class RequestStructResolverTest extends TestCase
{
    /**
     * @Mock
     * @var SerializerInterface
     */
    private \Phake_IMock $serializer;

    /**
     * @Mock
     * @var StructReader
     */
    private \Phake_IMock $structReader;

    /**
     * @Mock
     * @var HttpFoundationRequest
     */
    private \Phake_IMock $request;

    /**
     * @Mock
     * @var ArgumentMetadata
     */
    private \Phake_IMock $argument;

    private RequestStructResolver $requestStructResolver;

    public function setUp(): void
    {
        parent::setUp();

        \Phake::initAnnotations($this);

        $this->requestStructResolver = new RequestStructResolver($this->serializer, $this->structReader);
    }

    /**
     * @dataProvider providerSupportsShouldReturnFalse
     */
    public function testSupportsShouldReturnFalse(?string $type, ?string $content)
    {
        \Phake::when($this->request)->getContent->thenReturn($content);
        \Phake::when($this->argument)->getType->thenReturn($type);
        \Phake::when($this->structReader)->read->thenThrow(new AnnotationNotFoundException());

        $supports = $this->requestStructResolver->supports($this->request, $this->argument);

        $this->assertFalse($supports);
    }

    public function providerSupportsShouldReturnFalse(): array
    {
        return [
            ['string', 'this is a string'],
            [null, 'this is a string'],
            [CandyStructStub::class, 'this is a string'],
            [CandyStructStub::class, null],
        ];
    }

    public function testSupportsShouldReturnTrue()
    {
        \Phake::when($this->request)->getContent->thenReturn('this is a string');
        \Phake::when($this->argument)->getType->thenReturn(CandyStructStub::class);

        $structAnnotation = new Struct();
        $structAnnotation->supports = true;
        \Phake::when($this->structReader)->read->thenReturn($structAnnotation);

        $supports = $this->requestStructResolver->supports($this->request, $this->argument);

        $this->assertTrue($supports);
    }

    /**
     * @dataProvider providerResolveShouldThrowException
     */
    public function testResolveShouldThrowException(?string $type, ?string $content)
    {
        \Phake::when($this->request)->getContent->thenReturn($content);
        \Phake::when($this->argument)->getType->thenReturn($type);

        $this->expectException(\LogicException::class);

        $result = $this->requestStructResolver->resolve($this->request, $this->argument);

        foreach ($result as $item) {
        }
    }

    public function providerResolveShouldThrowException(): array
    {
        return [
            ['string', 'this is a string'],
            [null, 'this is a string'],
            [CandyStructStub::class, null],
        ];
    }

    /**
     * @dataProvider providerResolveShouldYield
     */
    public function testResolveShouldYield($isVariadic, $expected, $expectedClassName)
    {
        \Phake::when($this->request)->getContent->thenReturn(json_encode($expected));
        \Phake::when($this->argument)->isVariadic->thenReturn($isVariadic);
        \Phake::when($this->argument)->getType->thenReturn($expectedClassName);
        \Phake::when($this->serializer)->deserialize->thenReturn($expected);

        $result = $this->requestStructResolver->resolve($this->request, $this->argument);

        $count = 0;

        foreach ($result as $generatorObject) {
            ++$count;
            $this->assertInstanceOf($expectedClassName, $generatorObject);
        }

        // makes sure, that generator does not yield nothing
        $this->assertGreaterThan(0, $count);
    }

    public function providerResolveShouldYield(): array
    {
        return [
            [
                true,
                [new CandyStructStub(), new CandyStructStub()],
                CandyStructStub::class
            ],
            [
                false,
                new CandyStructStub(),
                CandyStructStub::class
            ],
        ];
    }
}
