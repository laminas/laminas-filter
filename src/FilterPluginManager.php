<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Filter\Exception\RuntimeException;
use Laminas\I18n\Filter\Alnum;
use Laminas\I18n\Filter\Alpha;
use Laminas\I18n\Filter\NumberFormat;
use Laminas\I18n\Filter\NumberParse;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;

use function get_debug_type;
use function is_callable;
use function sprintf;

/**
 * Plugin manager implementation for the filter chain.
 *
 * Enforces that filters retrieved are either callbacks or instances of
 * FilterInterface. Additionally, it registers a number of default filters
 * available, as well as aliases for them.
 *
 * @final
 * @extends AbstractPluginManager<FilterInterface|callable(mixed): mixed>
 */
class FilterPluginManager extends AbstractPluginManager
{
    protected $aliases = [
        // @codingStandardsIgnoreStart
        // For the future
        'int'  => ToInt::class,
        'Int'  => ToInt::class,
        'null' => ToNull::class,
        'Null' => ToNull::class,

        // I18n filters
        'alnum'        => Alnum::class,
        'Alnum'        => Alnum::class,
        'alpha'        => Alpha::class,
        'Alpha'        => Alpha::class,
        'numberformat' => NumberFormat::class,
        'numberFormat' => NumberFormat::class,
        'NumberFormat' => NumberFormat::class,
        'numberparse'  => NumberParse::class,
        'numberParse'  => NumberParse::class,
        'NumberParse'  => NumberParse::class,

        // Standard filters
        'allowlist'                  => AllowList::class,
        'allowList'                  => AllowList::class,
        'AllowList'                  => AllowList::class,
        'basename'                   => BaseName::class,
        'Basename'                   => BaseName::class,
        'blacklist'                  => DenyList::class,
        'Blacklist'                  => DenyList::class,
        'boolean'                    => Boolean::class,
        'Boolean'                    => Boolean::class,
        'callback'                   => Callback::class,
        'Callback'                   => Callback::class,
        'compress'                   => Compress::class,
        'Compress'                   => Compress::class,
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
        'decompress'                 => Decompress::class,
        'Decompress'                 => Decompress::class,
        'decrypt'                    => Decrypt::class,
        'Decrypt'                    => Decrypt::class,
        'denylist'                   => DenyList::class,
        'denyList'                   => DenyList::class,
        'DenyList'                   => DenyList::class,
        'digits'                     => Digits::class,
        'Digits'                     => Digits::class,
        'dir'                        => Dir::class,
        'Dir'                        => Dir::class,
        'encrypt'                    => Encrypt::class,
        'Encrypt'                    => Encrypt::class,
        'filedecrypt'                => File\Decrypt::class,
        'fileDecrypt'                => File\Decrypt::class,
        'FileDecrypt'                => File\Decrypt::class,
        'fileencrypt'                => File\Encrypt::class,
        'fileEncrypt'                => File\Encrypt::class,
        'FileEncrypt'                => File\Encrypt::class,
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
        'urinormalize'               => UriNormalize::class,
        'uriNormalize'               => UriNormalize::class,
        'UriNormalize'               => UriNormalize::class,
        'whitelist'                  => AllowList::class,
        'Whitelist'                  => AllowList::class,
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

        // Legacy Zend Framework aliases
        'Zend\I18n\Filter\Alnum'                  => Alnum::class,
        'Zend\I18n\Filter\Alpha'                  => Alpha::class,
        'Zend\I18n\Filter\NumberFormat'           => NumberFormat::class,
        'Zend\I18n\Filter\NumberParse'            => NumberParse::class,
        'Zend\Filter\BaseName'                    => BaseName::class,
        'Zend\Filter\Blacklist'                   => DenyList::class,
        'Zend\Filter\Boolean'                     => Boolean::class,
        'Zend\Filter\Callback'                    => Callback::class,
        'Zend\Filter\Compress'                    => Compress::class,
        'Zend\Filter\DataUnitFormatter'           => DataUnitFormatter::class,
        'Zend\Filter\DateSelect'                  => DateSelect::class,
        'Zend\Filter\DateTimeFormatter'           => DateTimeFormatter::class,
        'Zend\Filter\DateTimeSelect'              => DateTimeSelect::class,
        'Zend\Filter\Decompress'                  => Decompress::class,
        'Zend\Filter\Decrypt'                     => Decrypt::class,
        'Zend\Filter\Digits'                      => Digits::class,
        'Zend\Filter\Dir'                         => Dir::class,
        'Zend\Filter\Encrypt'                     => Encrypt::class,
        'Zend\Filter\File\Decrypt'                => File\Decrypt::class,
        'Zend\Filter\File\Encrypt'                => File\Encrypt::class,
        'Zend\Filter\File\LowerCase'              => File\LowerCase::class,
        'Zend\Filter\File\Rename'                 => File\Rename::class,
        'Zend\Filter\File\RenameUpload'           => File\RenameUpload::class,
        'Zend\Filter\File\UpperCase'              => File\UpperCase::class,
        'Zend\Filter\HtmlEntities'                => HtmlEntities::class,
        'Zend\Filter\Inflector'                   => Inflector::class,
        'Zend\Filter\ToInt'                       => ToInt::class,
        'Zend\Filter\ToFloat'                     => ToFloat::class,
        'Zend\Filter\MonthSelect'                 => MonthSelect::class,
        'Zend\Filter\ToNull'                      => ToNull::class,
        'Zend\Filter\UpperCaseWords'              => UpperCaseWords::class,
        'Zend\Filter\PregReplace'                 => PregReplace::class,
        'Zend\Filter\RealPath'                    => RealPath::class,
        'Zend\Filter\StringPrefix'                => StringPrefix::class,
        'Zend\Filter\StringSuffix'                => StringSuffix::class,
        'Zend\Filter\StringToLower'               => StringToLower::class,
        'Zend\Filter\StringToUpper'               => StringToUpper::class,
        'Zend\Filter\StringTrim'                  => StringTrim::class,
        'Zend\Filter\StripNewlines'               => StripNewlines::class,
        'Zend\Filter\StripTags'                   => StripTags::class,
        'Zend\Filter\UriNormalize'                => UriNormalize::class,
        'Zend\Filter\Whitelist'                   => AllowList::class,
        'Zend\Filter\Word\CamelCaseToDash'        => Word\CamelCaseToDash::class,
        'Zend\Filter\Word\CamelCaseToSeparator'   => Word\CamelCaseToSeparator::class,
        'Zend\Filter\Word\CamelCaseToUnderscore'  => Word\CamelCaseToUnderscore::class,
        'Zend\Filter\Word\DashToCamelCase'        => Word\DashToCamelCase::class,
        'Zend\Filter\Word\DashToSeparator'        => Word\DashToSeparator::class,
        'Zend\Filter\Word\DashToUnderscore'       => Word\DashToUnderscore::class,
        'Zend\Filter\Word\SeparatorToCamelCase'   => Word\SeparatorToCamelCase::class,
        'Zend\Filter\Word\SeparatorToDash'        => Word\SeparatorToDash::class,
        'Zend\Filter\Word\SeparatorToSeparator'   => Word\SeparatorToSeparator::class,
        'Zend\Filter\Word\UnderscoreToCamelCase'  => Word\UnderscoreToCamelCase::class,
        'Zend\Filter\Word\UnderscoreToStudlyCase' => Word\UnderscoreToStudlyCase::class,
        'Zend\Filter\Word\UnderscoreToDash'       => Word\UnderscoreToDash::class,
        'Zend\Filter\Word\UnderscoreToSeparator'  => Word\UnderscoreToSeparator::class,

        // v2 normalized FQCNs
        'zendfiltertoint'                      => ToInt::class,
        'zendfiltertofloat'                    => ToFloat::class,
        'zendfiltertonull'                     => ToNull::class,
        'zendi18nfilteralnum'                  => Alnum::class,
        'zendi18nfilteralpha'                  => Alpha::class,
        'zendi18nfilternumberformat'           => NumberFormat::class,
        'zendi18nfilternumberparse'            => NumberParse::class,
        'zendfilterbasename'                   => BaseName::class,
        'zendfilterblacklist'                  => DenyList::class,
        'zendfilterboolean'                    => Boolean::class,
        'zendfiltercallback'                   => Callback::class,
        'zendfiltercompress'                   => Compress::class,
        'zendfilterdataunitformatter'          => DataUnitFormatter::class,
        'zendfilterdateselect'                 => DateSelect::class,
        'zendfilterdatetimeformatter'          => DateTimeFormatter::class,
        'zendfilterdatetimeselect'             => DateTimeSelect::class,
        'zendfilterdecompress'                 => Decompress::class,
        'zendfilterdecrypt'                    => Decrypt::class,
        'zendfilterdigits'                     => Digits::class,
        'zendfilterdir'                        => Dir::class,
        'zendfilterencrypt'                    => Encrypt::class,
        'zendfilterfiledecrypt'                => File\Decrypt::class,
        'zendfilterfileencrypt'                => File\Encrypt::class,
        'zendfilterfilelowercase'              => File\LowerCase::class,
        'zendfilterfilerename'                 => File\Rename::class,
        'zendfilterfilerenameupload'           => File\RenameUpload::class,
        'zendfilterfileuppercase'              => File\UpperCase::class,
        'zendfilterhtmlentities'               => HtmlEntities::class,
        'zendfilterinflector'                  => Inflector::class,
        'zendfiltermonthselect'                => MonthSelect::class,
        'zendfilterpregreplace'                => PregReplace::class,
        'zendfilterrealpath'                   => RealPath::class,
        'zendfilterstringprefix'               => StringPrefix::class,
        'zendfilterstringsuffix'               => StringSuffix::class,
        'zendfilterstringtolower'              => StringToLower::class,
        'zendfilterstringtoupper'              => StringToUpper::class,
        'zendfilterstringtrim'                 => StringTrim::class,
        'zendfilterstripnewlines'              => StripNewlines::class,
        'zendfilterstriptags'                  => StripTags::class,
        'zendfilteruppercasewords'             => UpperCaseWords::class,
        'zendfilterurinormalize'               => UriNormalize::class,
        'zendfilterwhitelist'                  => AllowList::class,
        'zendfilterwordcamelcasetodash'        => Word\CamelCaseToDash::class,
        'zendfilterwordcamelcasetoseparator'   => Word\CamelCaseToSeparator::class,
        'zendfilterwordcamelcasetounderscore'  => Word\CamelCaseToUnderscore::class,
        'zendfilterworddashtocamelcase'        => Word\DashToCamelCase::class,
        'zendfilterworddashtoseparator'        => Word\DashToSeparator::class,
        'zendfilterworddashtounderscore'       => Word\DashToUnderscore::class,
        'zendfilterwordseparatortocamelcase'   => Word\SeparatorToCamelCase::class,
        'zendfilterwordseparatortodash'        => Word\SeparatorToDash::class,
        'zendfilterwordseparatortoseparator'   => Word\SeparatorToSeparator::class,
        'zendfilterwordunderscoretocamelcase'  => Word\UnderscoreToCamelCase::class,
        'zendfilterwordunderscoretostudlycase' => Word\UnderscoreToStudlyCase::class,
        'zendfilterwordunderscoretodash'       => Word\UnderscoreToDash::class,
        'zendfilterwordunderscoretoseparator'  => Word\UnderscoreToSeparator::class,
        // @codingStandardsIgnoreEnd
    ];

    /**
     * Default set of plugins factories
     *
     * @var array
     */
    protected $factories = [
        // I18n filters
        Alnum::class        => InvokableFactory::class,
        Alpha::class        => InvokableFactory::class,
        NumberFormat::class => InvokableFactory::class,
        NumberParse::class  => InvokableFactory::class,

        // Standard filters
        AllowList::class                   => InvokableFactory::class,
        BaseName::class                    => InvokableFactory::class,
        Blacklist::class                   => InvokableFactory::class,
        Boolean::class                     => InvokableFactory::class,
        Callback::class                    => InvokableFactory::class,
        Compress::class                    => InvokableFactory::class,
        DataUnitFormatter::class           => InvokableFactory::class,
        DateSelect::class                  => InvokableFactory::class,
        DateTimeFormatter::class           => InvokableFactory::class,
        DateTimeSelect::class              => InvokableFactory::class,
        Decompress::class                  => InvokableFactory::class,
        Decrypt::class                     => InvokableFactory::class,
        DenyList::class                    => InvokableFactory::class,
        Digits::class                      => InvokableFactory::class,
        Dir::class                         => InvokableFactory::class,
        Encrypt::class                     => InvokableFactory::class,
        File\Decrypt::class                => InvokableFactory::class,
        File\Encrypt::class                => InvokableFactory::class,
        File\LowerCase::class              => InvokableFactory::class,
        File\Rename::class                 => InvokableFactory::class,
        File\RenameUpload::class           => InvokableFactory::class,
        File\UpperCase::class              => InvokableFactory::class,
        ForceUriScheme::class              => InvokableFactory::class,
        HtmlEntities::class                => InvokableFactory::class,
        Inflector::class                   => InvokableFactory::class,
        ToInt::class                       => InvokableFactory::class,
        ToFloat::class                     => InvokableFactory::class,
        MonthSelect::class                 => InvokableFactory::class,
        ToNull::class                      => InvokableFactory::class,
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
        Whitelist::class                   => InvokableFactory::class,
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

        // v2 canonical FQCNs
        'laminasfiltertoint'                      => InvokableFactory::class,
        'laminasfiltertofloat'                    => InvokableFactory::class,
        'laminasfiltertonull'                     => InvokableFactory::class,
        'laminasi18nfilteralnum'                  => InvokableFactory::class,
        'laminasi18nfilteralpha'                  => InvokableFactory::class,
        'laminasi18nfilternumberformat'           => InvokableFactory::class,
        'laminasi18nfilternumberparse'            => InvokableFactory::class,
        'laminasfilterbasename'                   => InvokableFactory::class,
        'laminasfilterblacklist'                  => InvokableFactory::class,
        'laminasfilterboolean'                    => InvokableFactory::class,
        'laminasfiltercallback'                   => InvokableFactory::class,
        'laminasfiltercompress'                   => InvokableFactory::class,
        'laminasfilterdataunitformatter'          => InvokableFactory::class,
        'laminasfilterdateselect'                 => InvokableFactory::class,
        'laminasfilterdatetimeformatter'          => InvokableFactory::class,
        'laminasfilterdatetimeselect'             => InvokableFactory::class,
        'laminasfilterdecompress'                 => InvokableFactory::class,
        'laminasfilterdecrypt'                    => InvokableFactory::class,
        'laminasfilterdigits'                     => InvokableFactory::class,
        'laminasfilterdir'                        => InvokableFactory::class,
        'laminasfilterencrypt'                    => InvokableFactory::class,
        'laminasfilterfiledecrypt'                => InvokableFactory::class,
        'laminasfilterfileencrypt'                => InvokableFactory::class,
        'laminasfilterfilelowercase'              => InvokableFactory::class,
        'laminasfilterfilerename'                 => InvokableFactory::class,
        'laminasfilterfilerenameupload'           => InvokableFactory::class,
        'laminasfilterfileuppercase'              => InvokableFactory::class,
        'laminasfilterhtmlentities'               => InvokableFactory::class,
        'laminasfilterinflector'                  => InvokableFactory::class,
        'laminasfiltermonthselect'                => InvokableFactory::class,
        'laminasfilterpregreplace'                => InvokableFactory::class,
        'laminasfilterrealpath'                   => InvokableFactory::class,
        'laminasfilterstringprefix'               => InvokableFactory::class,
        'laminasfilterstringsuffix'               => InvokableFactory::class,
        'laminasfilterstringtolower'              => InvokableFactory::class,
        'laminasfilterstringtoupper'              => InvokableFactory::class,
        'laminasfilterstringtrim'                 => InvokableFactory::class,
        'laminasfilterstripnewlines'              => InvokableFactory::class,
        'laminasfilterstriptags'                  => InvokableFactory::class,
        'laminasfilteruppercasewords'             => InvokableFactory::class,
        'laminasfilterurinormalize'               => InvokableFactory::class,
        'laminasfilterwhitelist'                  => InvokableFactory::class,
        'laminasfilterwordcamelcasetodash'        => InvokableFactory::class,
        'laminasfilterwordcamelcasetoseparator'   => InvokableFactory::class,
        'laminasfilterwordcamelcasetounderscore'  => InvokableFactory::class,
        'laminasfilterworddashtocamelcase'        => InvokableFactory::class,
        'laminasfilterworddashtoseparator'        => InvokableFactory::class,
        'laminasfilterworddashtounderscore'       => InvokableFactory::class,
        'laminasfilterwordseparatortocamelcase'   => InvokableFactory::class,
        'laminasfilterwordseparatortodash'        => InvokableFactory::class,
        'laminasfilterwordseparatortoseparator'   => Word\Service\SeparatorToSeparatorFactory::class,
        'laminasfilterwordunderscoretocamelcase'  => InvokableFactory::class,
        'laminasfilterwordunderscoretostudlycase' => InvokableFactory::class,
        'laminasfilterwordunderscoretodash'       => InvokableFactory::class,
        'laminasfilterwordunderscoretoseparator'  => InvokableFactory::class,
    ];

    protected $instanceOf = FilterInterface::class;

    /**
     * Whether or not to share by default; default to false (v2)
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Whether or not to share by default; default to false (v3)
     *
     * @var bool
     */
    protected $sharedByDefault = false;

    /**
     * {@inheritdoc}
     *
     * @psalm-assert FilterInterface|callable(mixed): mixed $instance
     */
    public function validate(mixed $instance)
    {
        if ($instance instanceof $this->instanceOf) {
            // we're okay
            return;
        }

        if (is_callable($instance)) {
            // also okay
            return;
        }

        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement %s\FilterInterface or be callable',
            get_debug_type($instance),
            __NAMESPACE__
        ));
    }

    /**
     * Validate the plugin (v2)
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @return void
     * @throws RuntimeException If invalid.
     */
    public function validatePlugin(mixed $plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     * @template InstanceType of FilterInterface
     * @param class-string<InstanceType>|string $name Service name of plugin to retrieve.
     * @param null|array<mixed> $options Options to use when creating the instance.
     * @return InstanceType|callable(mixed): mixed
     * @psalm-return ($name is class-string ? InstanceType : callable(mixed): mixed)
     */
    public function get($name, ?array $options = null)
    {
        /** @psalm-suppress MixedReturnStatement */
        return parent::get($name, $options);
    }

    /**
     * @param string $name
     * @param FilterInterface|callable(mixed): mixed $service
     * @return void
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function setService($name, $service)
    {
        parent::setService($name, $service);
    }
}
