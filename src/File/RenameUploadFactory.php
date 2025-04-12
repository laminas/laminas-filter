<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class RenameUploadFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array|null $options = null,
    ): RenameUpload {
        /**
         * @psalm-suppress MixedArgumentTypeCoercion Runtime validation of options is not worth it.
         */
        return new RenameUpload($options ?? [], new MoveUploadedFile());
    }
}
