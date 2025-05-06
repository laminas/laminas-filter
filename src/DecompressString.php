<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Filter\Compress\Bz2Adapter;
use Laminas\Filter\Compress\GzAdapter;
use Laminas\Filter\Compress\StringCompressionAdapterInterface;

use function is_string;
use function strtolower;

/**
 * @psalm-type Options = array{
 *     adapter?: 'gz'|'bz2'|StringCompressionAdapterInterface,
 * }
 * @implements FilterInterface<string>
 */
final class DecompressString implements FilterInterface
{
    private readonly StringCompressionAdapterInterface $adapter;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $adapter = $options['adapter'] ?? null;

        if ($adapter instanceof StringCompressionAdapterInterface) {
            $this->adapter = $adapter;
        } else {
            /** @psalm-suppress RedundantFunctionCallGivenDocblockType This is for legacy compat with 'Gz2' and 'Bz' */
            $adapter       = strtolower($adapter ?? 'gz');
            $this->adapter = match ($adapter) {
                'gz' => new GzAdapter(),
                'bz2' => new Bz2Adapter(),
            };
        }
    }

    public function filter(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return $this->adapter->decompress($value);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
