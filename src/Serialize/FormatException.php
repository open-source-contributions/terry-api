<?php

declare(strict_types=1);

namespace TerryApiBundle\Serialize;

use TerryApiBundle\Negotiation\MimeType;

final class FormatException extends \RuntimeException implements \Throwable
{
    private function __construct(string $message)
    {
        $this->message = $message;
    }

    public static function notConfigured(MimeType $mimeType): self
    {
        return new self(sprintf('MimeType %s was not configured for any Format. Check configuration under serialize > formats', $mimeType->toString()));
    }
}
