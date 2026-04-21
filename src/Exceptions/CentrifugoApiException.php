<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Exceptions;

use RuntimeException;

final class CentrifugoApiException extends RuntimeException
{
    public static function transportError(string $method, string $reason, ?\Throwable $previous = null): self
    {
        return new self(
            message: "Centrifugo transport error on method \"{$method}\": {$reason}",
            previous: $previous,
        );
    }

    public static function serverError(string $method, int $code, string $message): self
    {
        return new self(
            message: "Centrifugo server error on method \"{$method}\" (code {$code}): {$message}",
        );
    }
}
