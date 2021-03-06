<?php

declare(strict_types=1);

namespace TerryApiBundle\Request;

use TerryApiBundle\Negotiation\MimeType;

final class ContentTypeHeader
{
    public const NAME = 'Content-Type';
    private string $contentType;

    private function __construct(string $contentType)
    {
        $this->contentType = $contentType;
    }

    public static function fromString(string $contentType): self
    {
        return new self($contentType);
    }

    public function toString(): string
    {
        return $this->contentType;
    }

    public function toMimeType(): MimeType
    {
        return MimeType::fromString($this->contentType);
    }
}
