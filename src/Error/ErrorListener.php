<?php

declare(strict_types=1);

namespace TerryApiBundle\Error;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use TerryApiBundle\HttpApi\HttpApiReader;
use TerryApiBundle\Negotiation\ContentNegotiator;
use TerryApiBundle\Request\AcceptHeader;
use TerryApiBundle\Response\ContentTypeHeader;
use TerryApiBundle\Response\ResponseBuilder;
use TerryApiBundle\Serialize\Serializer;

final class ErrorListener
{
    private HttpApiReader $httpApiReader;
    private ContentNegotiator $contentNegotiator;
    private ResponseBuilder $responseBuilder;
    private Serializer $serializer;

    public function __construct(
        HttpApiReader $httpApiReader,
        ContentNegotiator $contentNegotiator,
        ResponseBuilder $responseBuilder,
        Serializer $serializer
    ) {
        $this->httpApiReader = $httpApiReader;
        $this->contentNegotiator = $contentNegotiator;
        $this->responseBuilder = $responseBuilder;
        $this->serializer = $serializer;
    }

    public function handle(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof ErrorInterface) {
            return;
        }

        $event->setResponse($this->createResponse($event->getRequest(), $exception));
    }


    private function createResponse(Request $request, ErrorInterface $exception): Response
    {
        $acceptHeader = AcceptHeader::fromString((string) $request->headers->get(AcceptHeader::NAME, ''));
        $preferredMimeType = $this->contentNegotiator->negotiate($acceptHeader);

        $object = $exception->getContent();

        $this->httpApiReader->read(get_class($object));

        return $this->responseBuilder
            ->setContent($this->serializer->serialize($object, $preferredMimeType))
            ->setStatus($exception->getStatusCode())
            ->setContentType(ContentTypeHeader::fromString($preferredMimeType->toString()))
            ->getResponse();
    }
}
