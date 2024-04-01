<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;

/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
class ConfigProvider
{
    /**
     * Return configuration for this component.
     *
     * @return array{dependencies: ServiceManagerConfiguration, filters: ServiceManagerConfiguration}
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'filters'      => $this->filters(),
        ];
    }

    /**
     * Return dependency mappings for this component.
     *
     * @return ServiceManagerConfiguration
     */
    public function getDependencyConfig()
    {
        return [
            'aliases'   => [
                'FilterManager' => FilterPluginManager::class,
            ],
            'factories' => [
                FilterPluginManager::class => FilterPluginManagerFactory::class,
            ],
        ];
    }

    /**
     * Return standard filter configuration
     *
     * @return ServiceManagerConfiguration
     */
    private function filters(): array
    {
        return [
            'factories' => [
                AllowList::class                   => InvokableFactory::class,
                BaseName::class                    => InvokableFactory::class,
                Boolean::class                     => InvokableFactory::class,
                Callback::class                    => InvokableFactory::class,
                Compress::class                    => InvokableFactory::class,
                DataUnitFormatter::class           => InvokableFactory::class,
                DateSelect::class                  => InvokableFactory::class,
                DateTimeFormatter::class           => InvokableFactory::class,
                DateTimeSelect::class              => InvokableFactory::class,
                Decompress::class                  => InvokableFactory::class,
                DenyList::class                    => InvokableFactory::class,
                Digits::class                      => InvokableFactory::class,
                Dir::class                         => InvokableFactory::class,
                File\LowerCase::class              => InvokableFactory::class,
                File\Rename::class                 => InvokableFactory::class,
                File\RenameUpload::class           => InvokableFactory::class,
                File\UpperCase::class              => InvokableFactory::class,
                HtmlEntities::class                => InvokableFactory::class,
                Inflector::class                   => InvokableFactory::class,
                ToFloat::class                     => InvokableFactory::class,
                MonthSelect::class                 => InvokableFactory::class,
                UpperCaseWords::class              => InvokableFactory::class,
                PregReplace::class                 => InvokableFactory::class,
                RealPath::class                    => InvokableFactory::class,
                StringPrefix::class                => InvokableFactory::class,
                StringSuffix::class                => InvokableFactory::class,
                StringToLower::class               => InvokableFactory::class,
                StringToUpper::class               => InvokableFactory::class,
                StringTrim::class                  => InvokableFactory::class,
                StripNewlines::class               => InvokableFactory::class,
                StripTags::class                   => InvokableFactory::class,
                ToInt::class                       => InvokableFactory::class,
                ToNull::class                      => InvokableFactory::class,
                UriNormalize::class                => InvokableFactory::class,
                Word\CamelCaseToDash::class        => InvokableFactory::class,
                Word\CamelCaseToSeparator::class   => InvokableFactory::class,
                Word\CamelCaseToUnderscore::class  => InvokableFactory::class,
                Word\DashToCamelCase::class        => InvokableFactory::class,
                Word\DashToSeparator::class        => InvokableFactory::class,
                Word\DashToUnderscore::class       => InvokableFactory::class,
                Word\SeparatorToCamelCase::class   => InvokableFactory::class,
                Word\SeparatorToDash::class        => InvokableFactory::class,
                Word\SeparatorToSeparator::class   => Word\Service\SeparatorToSeparatorFactory::class,
                Word\UnderscoreToCamelCase::class  => InvokableFactory::class,
                Word\UnderscoreToStudlyCase::class => InvokableFactory::class,
                Word\UnderscoreToDash::class       => InvokableFactory::class,
                Word\UnderscoreToSeparator::class  => InvokableFactory::class,
            ],
        ];
    }
}
