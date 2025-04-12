<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

use function array_replace_recursive;
use function get_debug_type;
use function is_callable;
use function sprintf;

/**
 * Plugin manager implementation for filters
 *
 * Enforces that filters retrieved are either callbacks or instances of FilterInterface.
 *
 * @psalm-type InstanceType = FilterInterface|callable(mixed): mixed
 * @extends AbstractPluginManager<InstanceType>
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class FilterPluginManager extends AbstractPluginManager
{
    private const CONFIGURATION = [
        'factories' => [
            AllowList::class                   => InvokableFactory::class,
            BaseName::class                    => InvokableFactory::class,
            Boolean::class                     => InvokableFactory::class,
            Callback::class                    => InvokableFactory::class,
            CompressString::class              => InvokableFactory::class,
            CompressToArchive::class           => InvokableFactory::class,
            DataUnitFormatter::class           => InvokableFactory::class,
            DateSelect::class                  => InvokableFactory::class,
            DateTimeFormatter::class           => InvokableFactory::class,
            DateTimeSelect::class              => InvokableFactory::class,
            DecompressArchive::class           => InvokableFactory::class,
            DecompressString::class            => InvokableFactory::class,
            DenyList::class                    => InvokableFactory::class,
            Digits::class                      => InvokableFactory::class,
            Dir::class                         => InvokableFactory::class,
            File\LowerCase::class              => InvokableFactory::class,
            File\Rename::class                 => InvokableFactory::class,
            File\RenameUpload::class           => InvokableFactory::class,
            File\UpperCase::class              => InvokableFactory::class,
            FilterChain::class                 => FilterChainFactory::class,
            ForceUriScheme::class              => InvokableFactory::class,
            HtmlEntities::class                => InvokableFactory::class,
            ImmutableFilterChain::class        => ImmutableFilterChainFactory::class,
            Inflector::class                   => InflectorFactory::class,
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
            ToString::class                    => InvokableFactory::class,
            Word\CamelCaseToDash::class        => InvokableFactory::class,
            Word\CamelCaseToSeparator::class   => InvokableFactory::class,
            Word\CamelCaseToUnderscore::class  => InvokableFactory::class,
            Word\DashToCamelCase::class        => InvokableFactory::class,
            Word\DashToSeparator::class        => InvokableFactory::class,
            Word\DashToUnderscore::class       => InvokableFactory::class,
            Word\SeparatorToCamelCase::class   => InvokableFactory::class,
            Word\SeparatorToDash::class        => InvokableFactory::class,
            Word\SeparatorToSeparator::class   => InvokableFactory::class,
            Word\UnderscoreToCamelCase::class  => InvokableFactory::class,
            Word\UnderscoreToStudlyCase::class => InvokableFactory::class,
            Word\UnderscoreToDash::class       => InvokableFactory::class,
            Word\UnderscoreToSeparator::class  => InvokableFactory::class,
        ],
        'aliases'   => [
            // For the future
            'int'  => ToInt::class,
            'Int'  => ToInt::class,
            'null' => ToNull::class,
            'Null' => ToNull::class,

            // Standard filters
            'allowlist'                  => AllowList::class,
            'allowList'                  => AllowList::class,
            'AllowList'                  => AllowList::class,
            'basename'                   => BaseName::class,
            'Basename'                   => BaseName::class,
            'boolean'                    => Boolean::class,
            'Boolean'                    => Boolean::class,
            'callback'                   => Callback::class,
            'Callback'                   => Callback::class,
            'dataunitformatter'          => DataUnitFormatter::class,
            'dataUnitFormatter'          => DataUnitFormatter::class,
            'DataUnitFormatter'          => DataUnitFormatter::class,
            'dateselect'                 => DateSelect::class,
            'dateSelect'                 => DateSelect::class,
            'DateSelect'                 => DateSelect::class,
            'datetimeformatter'          => DateTimeFormatter::class,
            'datetimeFormatter'          => DateTimeFormatter::class,
            'DatetimeFormatter'          => DateTimeFormatter::class,
            'dateTimeFormatter'          => DateTimeFormatter::class,
            'DateTimeFormatter'          => DateTimeFormatter::class,
            'datetimeselect'             => DateTimeSelect::class,
            'datetimeSelect'             => DateTimeSelect::class,
            'DatetimeSelect'             => DateTimeSelect::class,
            'dateTimeSelect'             => DateTimeSelect::class,
            'DateTimeSelect'             => DateTimeSelect::class,
            'denylist'                   => DenyList::class,
            'denyList'                   => DenyList::class,
            'DenyList'                   => DenyList::class,
            'digits'                     => Digits::class,
            'Digits'                     => Digits::class,
            'dir'                        => Dir::class,
            'Dir'                        => Dir::class,
            'filelowercase'              => File\LowerCase::class,
            'fileLowercase'              => File\LowerCase::class,
            'FileLowercase'              => File\LowerCase::class,
            'fileLowerCase'              => File\LowerCase::class,
            'FileLowerCase'              => File\LowerCase::class,
            'filerename'                 => File\Rename::class,
            'fileRename'                 => File\Rename::class,
            'FileRename'                 => File\Rename::class,
            'filerenameupload'           => File\RenameUpload::class,
            'fileRenameUpload'           => File\RenameUpload::class,
            'FileRenameUpload'           => File\RenameUpload::class,
            'fileuppercase'              => File\UpperCase::class,
            'fileUppercase'              => File\UpperCase::class,
            'FileUppercase'              => File\UpperCase::class,
            'fileUpperCase'              => File\UpperCase::class,
            'FileUpperCase'              => File\UpperCase::class,
            'htmlentities'               => HtmlEntities::class,
            'htmlEntities'               => HtmlEntities::class,
            'HtmlEntities'               => HtmlEntities::class,
            'inflector'                  => Inflector::class,
            'Inflector'                  => Inflector::class,
            'monthselect'                => MonthSelect::class,
            'monthSelect'                => MonthSelect::class,
            'MonthSelect'                => MonthSelect::class,
            'pregreplace'                => PregReplace::class,
            'pregReplace'                => PregReplace::class,
            'PregReplace'                => PregReplace::class,
            'realpath'                   => RealPath::class,
            'realPath'                   => RealPath::class,
            'RealPath'                   => RealPath::class,
            'stringprefix'               => StringPrefix::class,
            'stringPrefix'               => StringPrefix::class,
            'StringPrefix'               => StringPrefix::class,
            'stringsuffix'               => StringSuffix::class,
            'stringSuffix'               => StringSuffix::class,
            'StringSuffix'               => StringSuffix::class,
            'stringtolower'              => StringToLower::class,
            'stringToLower'              => StringToLower::class,
            'StringToLower'              => StringToLower::class,
            'stringtoupper'              => StringToUpper::class,
            'stringToUpper'              => StringToUpper::class,
            'StringToUpper'              => StringToUpper::class,
            'stringtrim'                 => StringTrim::class,
            'stringTrim'                 => StringTrim::class,
            'StringTrim'                 => StringTrim::class,
            'stripnewlines'              => StripNewlines::class,
            'stripNewlines'              => StripNewlines::class,
            'StripNewlines'              => StripNewlines::class,
            'striptags'                  => StripTags::class,
            'stripTags'                  => StripTags::class,
            'StripTags'                  => StripTags::class,
            'toint'                      => ToInt::class,
            'toInt'                      => ToInt::class,
            'ToInt'                      => ToInt::class,
            'tofloat'                    => ToFloat::class,
            'toFloat'                    => ToFloat::class,
            'ToFloat'                    => ToFloat::class,
            'tonull'                     => ToNull::class,
            'toNull'                     => ToNull::class,
            'ToNull'                     => ToNull::class,
            'uppercasewords'             => UpperCaseWords::class,
            'upperCaseWords'             => UpperCaseWords::class,
            'UpperCaseWords'             => UpperCaseWords::class,
            'wordcamelcasetodash'        => Word\CamelCaseToDash::class,
            'wordCamelCaseToDash'        => Word\CamelCaseToDash::class,
            'WordCamelCaseToDash'        => Word\CamelCaseToDash::class,
            'wordcamelcasetoseparator'   => Word\CamelCaseToSeparator::class,
            'wordCamelCaseToSeparator'   => Word\CamelCaseToSeparator::class,
            'WordCamelCaseToSeparator'   => Word\CamelCaseToSeparator::class,
            'wordcamelcasetounderscore'  => Word\CamelCaseToUnderscore::class,
            'wordCamelCaseToUnderscore'  => Word\CamelCaseToUnderscore::class,
            'WordCamelCaseToUnderscore'  => Word\CamelCaseToUnderscore::class,
            'worddashtocamelcase'        => Word\DashToCamelCase::class,
            'wordDashToCamelCase'        => Word\DashToCamelCase::class,
            'WordDashToCamelCase'        => Word\DashToCamelCase::class,
            'worddashtoseparator'        => Word\DashToSeparator::class,
            'wordDashToSeparator'        => Word\DashToSeparator::class,
            'WordDashToSeparator'        => Word\DashToSeparator::class,
            'worddashtounderscore'       => Word\DashToUnderscore::class,
            'wordDashToUnderscore'       => Word\DashToUnderscore::class,
            'WordDashToUnderscore'       => Word\DashToUnderscore::class,
            'wordseparatortocamelcase'   => Word\SeparatorToCamelCase::class,
            'wordSeparatorToCamelCase'   => Word\SeparatorToCamelCase::class,
            'WordSeparatorToCamelCase'   => Word\SeparatorToCamelCase::class,
            'wordseparatortodash'        => Word\SeparatorToDash::class,
            'wordSeparatorToDash'        => Word\SeparatorToDash::class,
            'WordSeparatorToDash'        => Word\SeparatorToDash::class,
            'wordseparatortoseparator'   => Word\SeparatorToSeparator::class,
            'wordSeparatorToSeparator'   => Word\SeparatorToSeparator::class,
            'WordSeparatorToSeparator'   => Word\SeparatorToSeparator::class,
            'wordunderscoretocamelcase'  => Word\UnderscoreToCamelCase::class,
            'wordUnderscoreToCamelCase'  => Word\UnderscoreToCamelCase::class,
            'WordUnderscoreToCamelCase'  => Word\UnderscoreToCamelCase::class,
            'wordunderscoretostudlycase' => Word\UnderscoreToStudlyCase::class,
            'wordUnderscoreToStudlyCase' => Word\UnderscoreToStudlyCase::class,
            'WordUnderscoreToStudlyCase' => Word\UnderscoreToStudlyCase::class,
            'wordunderscoretodash'       => Word\UnderscoreToDash::class,
            'wordUnderscoreToDash'       => Word\UnderscoreToDash::class,
            'WordUnderscoreToDash'       => Word\UnderscoreToDash::class,
            'wordunderscoretoseparator'  => Word\UnderscoreToSeparator::class,
            'wordUnderscoreToSeparator'  => Word\UnderscoreToSeparator::class,
            'WordUnderscoreToSeparator'  => Word\UnderscoreToSeparator::class,
        ],
    ];

    /** Filter instances are never shared */
    protected bool $sharedByDefault = false;

    /** Generally speaking, filters can be constructed without arguments */
    protected bool $autoAddInvokableClass = true;

    /**
     * @param ServiceManagerConfiguration $config
     */
    public function __construct(ContainerInterface $creationContext, array $config = [])
    {
        /** @var ServiceManagerConfiguration $config */
        $config = array_replace_recursive(self::CONFIGURATION, $config);
        parent::__construct($creationContext, $config);
    }

    /** @inheritDoc */
    public function validate(mixed $instance): void
    {
        if ($instance instanceof FilterInterface) {
            return;
        }

        if (is_callable($instance)) {
            return;
        }

        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement %s\FilterInterface or be callable',
            get_debug_type($instance),
            __NAMESPACE__
        ));
    }
}
