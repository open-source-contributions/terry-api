<?php

declare(strict_types=1);

namespace TerryApiBundle\Negotiation;

final class MimeType
{
    private $mimeType;

    public function __construct(string $mimeType)
    {
        $this->mimeType = $mimeType;
    }

    public static function fromString(string $mimeType): self
    {
        return new self($mimeType);
    }

    public function toString(): string
    {
        return $this->mimeType;
    }
}
