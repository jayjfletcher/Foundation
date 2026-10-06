<?php

declare(strict_types=1);

namespace JayI\Foundation\Exceptions;

use LogicException;

/**
 * A class asked for its package before the package registered itself.
 */
final class UnknownPackageException extends LogicException
{
    public static function forKey(string $key): self
    {
        return new self("No package is registered under the key [{$key}].");
    }

    public static function forClass(string $class): self
    {
        return new self("No registered package owns [{$class}]. Register the package from its service provider with registerPackage().");
    }
}
