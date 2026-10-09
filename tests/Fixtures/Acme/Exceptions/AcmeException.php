<?php

declare(strict_types=1);

namespace RefactorCircus\Foundation\Tests\Fixtures\Acme\Exceptions;

use RefactorCircus\Foundation\Exceptions\PackageException;

final class AcmeException extends PackageException
{
    protected int $status = 422;
}
