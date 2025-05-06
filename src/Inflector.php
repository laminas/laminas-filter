<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;

use function array_keys;
use function array_map;
use function array_values;
use function assert;
use function get_object_vars;
use function is_array;
use function is_object;
use function is_scalar;
use function is_string;
use function ltrim;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function str_replace;
use function str_starts_with;

/**
 * Filter chain for string inflection
 *
 * @psalm-import-type InstanceType from FilterPluginManager
 * @psalm-type RulesArray = array<string, string|list<string|InstanceType>>
 * @psalm-type Options = array{
 *     target: string,
 *     rules?: RulesArray,
 *     throwTargetExceptionsOn?: bool,
 *     targetReplacementIdentifier?: non-empty-string,
 * }
 * @implements FilterInterface<string>
 */
final class Inflector implements FilterInterface
{
    /** @var non-empty-string */
    private readonly string $target;
    private readonly bool $throwTargetExceptionsOn;
    /** @var non-empty-string */
    private readonly string $targetReplacementIdentifier;
    /** @var array<string, string|list<InstanceType>> */
    private readonly array $rules;

    /** @param Options $options */
    public function __construct(
        private readonly FilterPluginManager $pluginManager,
        array $options,
    ) {
        $target = $options['target'] ?? null;
        if (! is_string($target) || $target === '') {
            throw new InvalidArgumentException('Inflector requires the target option to be a non-empty string');
        }

        $this->target                      = $target;
        $this->throwTargetExceptionsOn     = $options['throwTargetExceptionsOn'] ?? true;
        $this->targetReplacementIdentifier = $options['targetReplacementIdentifier'] ?? ':';
        $this->rules                       = $this->resolveRules($options['rules'] ?? []);
    }

    /**
     * Resolve rules argument
     *
     * If prefixed with a ":" (colon), a filter rule will be added.
     * If not prefixed, a static string replacement will be added.
     *
     * example:
     * [
     *     ':controller' => [CamelCaseToUnderscore::class, StringToLower::class],
     *     ':action'     => [CamelCaseToUnderscore::class, StringToLower::class],
     *     'suffix'      => 'phtml',
     * ]
     *
     * @param RulesArray $rules
     * @return array<string, string|list<InstanceType>>
     */
    private function resolveRules(array $rules): array
    {
        $resolved = [];
        foreach ($rules as $spec => $ruleSet) {
            $name = ltrim($spec, ':');
            if (str_starts_with($spec, ':')) {
                $resolved[$name] = array_map(
                    function (string|FilterInterface|callable $filter): FilterInterface|callable {
                        return $this->loadFilter($filter);
                    },
                    is_string($ruleSet) ? [$ruleSet] : $ruleSet,
                );
            } else {
                assert(is_string($ruleSet));
                $resolved[$name] = $ruleSet;
            }
        }

        return $resolved;
    }

    public function filter(mixed $value): mixed
    {
        if (is_object($value)) {
            $value = get_object_vars($value);
        }

        if (! is_array($value)) {
            return $value;
        }

        // clean source
        $subject = [];
        foreach ($value as $sourceName => $sourceValue) {
            if (! is_string($sourceName) || ! is_scalar($sourceValue)) {
                continue;
            }

            $sourceName           = ltrim($sourceName, ':');
            $subject[$sourceName] = (string) $sourceValue;
        }

        $pregQuotedTargetReplacementIdentifier = preg_quote($this->targetReplacementIdentifier, '#');
        $processedParts                        = [];

        foreach ($this->rules as $ruleName => $ruleValue) {
            if (isset($subject[$ruleName])) {
                if (is_string($ruleValue)) {
                    $processedParts['#' . $pregQuotedTargetReplacementIdentifier . $ruleName . '#'] = str_replace(
                        '\\',
                        '\\\\',
                        $subject[$ruleName],
                    );
                } else {
                    $processedPart = $subject[$ruleName];
                    foreach ($ruleValue as $ruleFilter) {
                        $processedPart = (string) $ruleFilter($processedPart);
                    }
                    $processedParts['#' . $pregQuotedTargetReplacementIdentifier . $ruleName . '#'] = str_replace(
                        '\\',
                        '\\\\',
                        $processedPart,
                    );
                }
            } elseif (is_string($ruleValue)) {
                $processedParts['#' . $pregQuotedTargetReplacementIdentifier . $ruleName . '#'] = str_replace(
                    '\\',
                    '\\\\',
                    $ruleValue,
                );
            }
        }

        // all of the values of processedParts would have been str_replace('\\', '\\\\', ..)'d
        // to disable preg_replace backreferences
        $inflectedTarget = preg_replace(array_keys($processedParts), array_values($processedParts), $this->target);
        assert($inflectedTarget !== null);

        if (
            $this->throwTargetExceptionsOn
            && preg_match('#(?=' . $pregQuotedTargetReplacementIdentifier . '[A-Za-z]{1})#', $inflectedTarget)
        ) {
            throw new Exception\RuntimeException(
                'A replacement identifier ' . $this->targetReplacementIdentifier
                . ' was found inside the inflected target, perhaps a rule was not satisfied with a target source?  '
                . 'Unsatisfied inflected target: ' . $inflectedTarget,
            );
        }

        return $inflectedTarget;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }

    /**
     * Resolve named filters and convert them to filter objects.
     *
     * @return InstanceType
     */
    private function loadFilter(string|FilterInterface|callable $rule): FilterInterface|callable
    {
        if (! is_string($rule)) {
            return $rule;
        }

        return $this->pluginManager->get($rule);
    }
}
