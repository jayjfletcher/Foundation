<?php

declare(strict_types=1);

namespace JayI\Foundation\Tests\Fixtures\Acme\Exceptions;

use JayI\Foundation\Exceptions\PackageException;

final class AcmeException extends PackageException
{
    protected int $status = 422;
}
