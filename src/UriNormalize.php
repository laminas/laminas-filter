<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Uri\Exception\ExceptionInterface as UriException;
use Laminas\Uri\Uri;
use Laminas\Uri\UriFactory;
use Traversable;

use function explode;
use function is_scalar;
use function strpos;

class UriNormalize extends AbstractFilter
{
    /**
     * The default scheme to use when parsing scheme-less URIs
     *
     * @var string|null
     */
    protected $defaultScheme;

    /**
     * Enforced scheme for scheme-less URIs. See setEnforcedScheme docs for info
     *
     * @var string|null
     */
    protected $enforcedScheme;

    /**
     * Sets filter options
     *
     * @param array|Traversable|null $options
     */
    public function __construct($options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * Set the default scheme to use when parsing scheme-less URIs
     *
     * The scheme used when parsing URIs may affect the specific object used to
     * normalize the URI and thus may affect the resulting normalize URI.
     *
     * @param  string $defaultScheme
     * @return self
     */
    public function setDefaultScheme($defaultScheme)
    {
        $this->defaultScheme = $defaultScheme;
        return $this;
    }

    /**
     * Set a URI scheme to enforce on schemeless URIs
     *
     * This allows forcing input values such as 'www.example.com/foo' into
     * 'http://www.example.com/foo'.
     *
     * This should be used with caution, as a standard-compliant URI parser
     * would regard 'www.example.com' in the above input URI to be the path and
     * not host part of the URI. While this option can assist in solving
     * real-world user mishaps, it may yield unexpected results at times.
     *
     * @param  string $enforcedScheme
     * @return self
     */
    public function setEnforcedScheme($enforcedScheme)
    {
        $this->enforcedScheme = $enforcedScheme;
        return $this;
    }

    /**
     * Filter the URL by normalizing it and applying a default scheme if set
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }
        $value = (string) $value;

        $defaultScheme = $this->defaultScheme ?: $this->enforcedScheme;

        // Reset default scheme if it is not a known scheme
        if (! UriFactory::getRegisteredSchemeClass($defaultScheme)) {
            $defaultScheme = null;
        }

        try {
            $uri = UriFactory::factory($value, $defaultScheme);
            if ($this->enforcedScheme && ! $uri->getScheme()) {
                $this->enforceScheme($uri);
            }
        } catch (UriException $ex) {
            // We are unable to parse / enfore scheme with the given config and input
            return $value;
        }

        $uri->normalize();

        if (! $uri->isValid()) {
            return $value;
        }

        return $uri->toString();
    }

    /**
     * Enforce the defined scheme on the URI
     *
     * This will also adjust the host and path parts of the URI as expected in
     * the case of scheme-less network URIs
     */
    protected function enforceScheme(Uri $uri)
    {
        $path = $uri->getPath();
        if (strpos($path, '/') !== false) {
            [$host, $path] = explode('/', $path, 2);
            $path          = '/' . $path;
        } else {
            $host = $path;
            $path = '';
        }

        // We have nothing to do if we have no host
        if (! $host) {
            return;
        }

        $uri->setScheme($this->enforcedScheme)
            ->setHost($host)
            ->setPath($path);
    }
}
