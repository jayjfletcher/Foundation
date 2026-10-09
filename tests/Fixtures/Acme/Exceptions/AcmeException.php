<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Tests\Fixtures\Acme\Exceptions;

use RefactorCircus\Keystone\Exceptions\PackageException;

final class AcmeException extends PackageException
{
    protected int $status = 422;
}
