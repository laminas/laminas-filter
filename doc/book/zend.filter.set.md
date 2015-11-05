# Standard Filter Classes

Zend Framework comes with a standard set of filters, which are ready for you to use.

## Alnum

The `Alnum` filter can be used to return only alphabetic characters and digits in the unicode
"letter" and "number" categories, respectively. All other characters are suppressed.

### Supported Options

The following options are supported for `Alnum`:

`Alnum([ boolean $allowWhiteSpace [, string $locale ]])`

- `$allowWhiteSpace`: If set to true then whitespace characters are allowed. Otherwise they are
suppressed. Default is "false" (whitespace is not allowed).

    Methods for getting/setting the allowWhiteSpace option are also available:
`getAllowWhiteSpace()` and `setAllowWhiteSpace()`

- `$locale`: The locale string used in identifying the characters to filter (locale name, e.g.
en\_US). If unset, it will use the default locale (`Locale::getDefault()`).

    Methods for getting/setting the locale are also available: `getLocale()` and `setLocale()`

### Basic Usage

```php
// Default settings, deny whitespace
$filter = new \Zend\I18n\Filter\Alnum();
echo $filter->filter("This is (my) content: 123");
// Returns "Thisismycontent123"

// First param in constructor is $allowWhiteSpace
$filter = new \Zend\I18n\Filter\Alnum(true);
echo $filter->filter("This is (my) content: 123");
// Returns "This is my content 123"
```

> ## Note
`Alnum` works on almost all languages, except: Chinese, Japanese and Korean. Within these languages
the english alphabet is used instead of the characters from these languages. The language itself is
detected using the `Locale`.

## Alpha

The `Alpha` filter can be used to return only alphabetic characters in the unicode "letter"
category. All other characters are suppressed.

### Supported Options

The following options are supported for `Alpha`:

`Alpha([ boolean $allowWhiteSpace [, string $locale ]])`

- `$allowWhiteSpace`: If set to true then whitespace characters are allowed. Otherwise they are
suppressed. Default is "false" (whitespace is not allowed).

    Methods for getting/setting the allowWhiteSpace option are also available:
`getAllowWhiteSpace()` and `setAllowWhiteSpace()`

- `$locale`: The locale string used in identifying the characters to filter (locale name, e.g.
en\_US). If unset, it will use the default locale (`Locale::getDefault()`).

    Methods for getting/setting the locale are also available: `getLocale()` and `setLocale()`

### Basic Usage

```php
// Default settings, deny whitespace
$filter = new \Zend\I18n\Filter\Alpha();
echo $filter->filter("This is (my) content: 123");
// Returns "Thisismycontent"

// Allow whitespace
$filter = new \Zend\I18n\Filter\Alpha(true);
echo $filter->filter("This is (my) content: 123");
// Returns "This is my content "
```

> ## Note
`Alpha` works on almost all languages, except: Chinese, Japanese and Korean. Within these languages
the english alphabet is used instead of the characters from these languages. The language itself is
detected using the `Locale`.

## BaseName

`Zend\Filter\BaseName` allows you to filter a string which contains the path to a file and it will
return the base name of this file.

### Supported Options

There are no additional options for `Zend\Filter\BaseName`.

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\BaseName();

print $filter->filter('/vol/tmp/filename');
```

This will return 'filename'.

```php
$filter = new Zend\Filter\BaseName();

print $filter->filter('/vol/tmp/filename.txt');
```

This will return '`filename.txt`'.

## Blacklist

This filter will return `null` if the value being filtered is present in the filter's list of
values. If the value is not present, it will return that value.

For the opposite functionality see the `Whitelist` filter.

### Supported Options

The following options are supported for `Zend\Filter\Blacklist`:

- **strict**: Uses strict mode when comparing: passed through to `in_array`'s third argument.
- **list**: An array of forbidden values.

### Basic Usage

This is a basic example:

```php
$blacklist = new \Zend\Filter\Blacklist(array(
    'list' => array('forbidden-1', 'forbidden-2')
));
echo $blacklist->filter('forbidden-1'); // => null
echo $blacklist->filter('allowed');     // => 'allowed'
```

## Boolean

This filter changes a given input to be a `BOOLEAN` value. This is often useful when working with
databases or when processing form values.

### Supported Options

The following options are supported for `Zend\Filter\Boolean`:

- **casting**: When this option is set to `TRUE` then any given input will be casted to boolean.
This option defaults to `TRUE`.
- **translations**: This option sets the translations which will be used to detect localized input.
- **type**: The `type` option sets the boolean type which should be used. Read the following for
details.

### Default Behavior

By default, this filter works by casting the input to a `BOOLEAN` value; in other words, it operates
in a similar fashion to calling `(boolean) $value`.

```php
$filter = new Zend\Filter\Boolean();
$value  = '';
$result = $filter->filter($value);
// returns false
```

This means that without providing any configuration, `Zend\Filter\Boolean` accepts all input types
and returns a `BOOLEAN` just as you would get by type casting to `BOOLEAN`.

### Changing the Default Behavior

Sometimes casting with `(boolean)` will not suffice. `Zend\Filter\Boolean` allows you to configure
specific types to convert, as well as which to omit.

The following types can be handled:

- **boolean**: Returns a boolean value as is.
- **integer**: Converts an integer **0** value to `FALSE`.
- **float**: Converts a float **0.0** value to `FALSE`.
- **string**: Converts an empty string **''** to `FALSE`.
- **zero**: Converts a string containing the single character zero (**'0'**) to `FALSE`.
- **empty\_array**: Converts an empty **array** to `FALSE`.
- **null**: Converts a `NULL` value to `FALSE`.
- **php**: Converts values according to *PHP* when casting them to `BOOLEAN`.
- **false\_string**: Converts a string containing the word "false" to a boolean `FALSE`.
- **yes**: Converts a localized string which contains the word "no" to `FALSE`.
- **all**: Converts all above types to `BOOLEAN`.

All other given values will return `TRUE` by default.

There are several ways to select which of the above types are filtered. You can give one or multiple
types and add them, you can give an array, you can use constants, or you can give a textual string.
See the following examples:

```php
// converts 0 to false
$filter = new Zend\Filter\Boolean(Zend\Filter\Boolean::TYPE_INTEGER);

// converts 0 and '0' to false
$filter = new Zend\Filter\Boolean(
    Zend\Filter\Boolean::TYPE_INTEGER + Zend\Filter\Boolean::TYPE_ZERO_STRING
);

// converts 0 and '0' to false
$filter = new Zend\Filter\Boolean(array(
    'type' => array(
        Zend\Filter\Boolean::TYPE_INTEGER,
        Zend\Filter\Boolean::TYPE_ZERO_STRING,
    ),
));

// converts 0 and '0' to false
$filter = new Zend\Filter\Boolean(array(
    'type' => array(
        'integer',
        'zero',
    ),
));
```

You can also give an instance of `Zend\Config\Config` to set the desired types. To set types after
instantiation, use the `setType()` method.

### Localized Booleans

As mentioned previously, `Zend\Filter\Boolean` can also recognise localized "yes" and "no" strings.
This means that you can ask your customer in a form for "yes" or "no" within his native language and
`Zend\Filter\Boolean` will convert the response to the appropriate boolean value.

To set the translation and the corresponding value, you can use the `translations` option or the
method `setTranslations`.

```php
$filter = new Zend\Filter\Boolean(array(
    'type'         => Zend\Filter\Boolean::TYPE_LOCALIZED,
    'translations' => array(
        'ja'   => true,
        'nein' => false,
        'yes'  => true,
        'no'   => false,
    ),
));

// returns false
$result = $filter->filter('nein');

// returns true
$result = $filter->filter('yes');
```

### Disable Casting

Sometimes it is necessary to recognise only `TRUE` or `FALSE` and return all other values without
changes. `Zend\Filter\Boolean` allows you to do this by setting the `casting` option to `FALSE`.

In this case `Zend\Filter\Boolean` will work as described in the following table, which shows which
values return `TRUE` or `FALSE`. All other given values are returned without change when `casting`
is set to `FALSE`

The following example shows the behaviour when changing the `casting` option:

```php
$filter = new Zend\Filter\Boolean(array(
    'type'    => Zend\Filter\Boolean::TYPE_ALL,
    'casting' => false,
));

// returns false
$result = $filter->filter(0);

// returns true
$result = $filter->filter(1);

// returns the value
$result = $filter->filter(2);
```

## Callback

This filter allows you to use own methods in conjunction with `Zend\Filter`. You don't have to
create a new filter when you already have a method which does the job.

### Supported Options

The following options are supported for `Zend\Filter\Callback`:

- **callback**: This sets the callback which should be used.
- **callback\_params**: This property sets the options which are used when the callback is
processed.

### Basic Usage

The usage of this filter is quite simple. Let's expect we want to create a filter which reverses a
string.

```php
$filter = new Zend\Filter\Callback('strrev');

print $filter->filter('Hello!');
// returns "!olleH"
```

As you can see it's really simple to use a callback to define a own filter. It is also possible to
use a method, which is defined within a class, by giving an array as callback.

```php
// Our classdefinition
class MyClass
{
    public function Reverse($param);
}

// The filter definition
$filter = new Zend\Filter\Callback(array('MyClass', 'Reverse'));
print $filter->filter('Hello!');
```

To get the actual set callback use `getCallback()` and to set another callback use `setCallback()`.

> ## Note
#### Possible exceptions
You should note that defining a callback method which can not be called will raise an exception.

### Default Parameters Within a Callback

It is also possible to define default parameters, which are given to the called method as array when
the filter is executed. This array will be concatenated with the value which will be filtered.

```php
$filter = new Zend\Filter\Callback(
    array(
        'callback' => 'MyMethod',
        'options'  => array('key' => 'param1', 'key2' => 'param2')
    )
);
$filter->filter(array('value' => 'Hello'));
```

When you would call the above method definition manually it would look like this:

```php
$value = MyMethod('Hello', 'param1', 'param2');
```

## Compress and Decompress

These two filters are capable of compressing and decompressing strings, files, and directories.

### Supported Options

The following options are supported for `Zend\Filter\Compress` and `Zend\Filter\Decompress`:

- **adapter**: The compression adapter which should be used. It defaults to `Gz`.
- **options**: Additional options which are given to the adapter at initiation. Each adapter
supports it's own options.

### Supported Compression Adapters

The following compression formats are supported by their own adapter:

- **Bz2**
- **Gz**
- **Lzf**
- **Rar**
- **Tar**
- **Zip**

Each compression format has different capabilities as described below. All compression filters may
be used in approximately the same ways, and differ primarily in the options available and the type
of compression they offer (both algorithmically as well as string vs. file vs. directory)

### Generic Handling

To create a compression filter you need to select the compression format you want to use. The
following description takes the **Bz2** adapter. Details for all other adapters are described after
this section.

The two filters are basically identical, in that they utilize the same backends.
`Zend\Filter\Compress` should be used when you wish to compress items, and `Zend\Filter\Decompress`
should be used when you wish to decompress items.

For instance, if we want to compress a string, we have to initiate `Zend\Filter\Compress` and
indicate the desired adapter.

```php
$filter = new Zend\Filter\Compress('Bz2');
```

To use a different adapter, you simply specify it to the constructor.

You may also provide an array of options or a Traversable object. If you do, provide minimally the
key "adapter", and then either the key "options" or "adapterOptions" (which should be an array of
options to provide to the adapter on instantiation).

```php
$filter = new Zend\Filter\Compress(array(
    'adapter' => 'Bz2',
    'options' => array(
        'blocksize' => 8,
    ),
));
```

> ## Note
#### Default compression Adapter
When no compression adapter is given, then the **Gz** adapter will be used.

Almost the same usage is we want to decompress a string. We just have to use the decompression
filter in this case.

```php
$filter = new Zend\Filter\Decompress('Bz2');
```

To get the compressed string, we have to give the original string. The filtered value is the
compressed version of the original string.

```php
$filter     = new Zend\Filter\Compress('Bz2');
$compressed = $filter->filter('Uncompressed string');
// Returns the compressed string
```

Decompression works the same way.

```php
$filter     = new Zend\Filter\Decompress('Bz2');
$compressed = $filter->filter('Compressed string');
// Returns the uncompressed string
```

> ## Note
#### Note on string compression
Not all adapters support string compression. Compression formats like **Rar** can only handle files
and directories. For details, consult the section for the adapter you wish to use.

### Creating an Archive

Creating an archive file works almost the same as compressing a string. However, in this case we
need an additional parameter which holds the name of the archive we want to create.

```php
$filter     = new Zend\Filter\Compress(array(
    'adapter' => 'Bz2',
    'options' => array(
        'archive' => 'filename.bz2',
    ),
));
$compressed = $filter->filter('Uncompressed string');
// Returns true on success and creates the archive file
```

In the above example the uncompressed string is compressed, and is then written into the given
archive file.

> ## Note
#### Existing archives will be overwritten
The content of any existing file will be overwritten when the given filename of the archive already
exists.

When you want to compress a file, then you must give the name of the file with its path.

```php
$filter     = new Zend\Filter\Compress(array(
    'adapter' => 'Bz2',
    'options' => array(
        'archive' => 'filename.bz2'
    ),
));
$compressed = $filter->filter('C:\temp\compressme.txt');
// Returns true on success and creates the archive file
```

You may also specify a directory instead of a filename. In this case the whole directory with all
its files and subdirectories will be compressed into the archive.

```php
$filter     = new Zend\Filter\Compress(array(
    'adapter' => 'Bz2',
    'options' => array(
        'archive' => 'filename.bz2'
    ),
));
$compressed = $filter->filter('C:\temp\somedir');
// Returns true on success and creates the archive file
```

> ## Note
#### Do not compress large or base directories
You should never compress large or base directories like a complete partition. Compressing a
complete partition is a very time consuming task which can lead to massive problems on your server
when there is not enough space or your script takes too much time.

### Decompressing an Archive

Decompressing an archive file works almost like compressing it. You must specify either the
`archive` parameter, or give the filename of the archive when you decompress the file.

```php
$filter     = new Zend\Filter\Decompress('Bz2');
$decompressed = $filter->filter('filename.bz2');
// Returns true on success and decompresses the archive file
```

Some adapters support decompressing the archive into another subdirectory. In this case you can set
the `target` parameter.

```php
$filter     = new Zend\Filter\Decompress(array(
    'adapter' => 'Zip',
    'options' => array(
        'target' => 'C:\temp',
    )
));
$decompressed = $filter->filter('filename.zip');
// Returns true on success and decompresses the archive file
// into the given target directory
```

> ## Note
#### Directories to extract to must exist
When you want to decompress an archive into a directory, then that directory must exist.

### Bz2 Adapter

The Bz2 Adapter can compress and decompress:

- Strings
- Files
- Directories

This adapter makes use of *PHP*'s Bz2 extension.

To customize compression, this adapter supports the following options:

- **Archive**: This parameter sets the archive file which should be used or created.
- **Blocksize**: This parameter sets the blocksize to use. It can be from '0' to '9'. The default
value is '4'.

All options can be set at instantiation or by using a related method. For example, the related
methods for 'Blocksize' are `getBlocksize()` and `setBlocksize()`. You can also use the
`setOptions()` method which accepts all options as array.

### Gz Adapter

The Gz Adapter can compress and decompress:

- Strings
- Files
- Directories

This adapter makes use of *PHP*'s Zlib extension.

To customize the compression this adapter supports the following options:

- **Archive**: This parameter sets the archive file which should be used or created.
- **Level**: This compression level to use. It can be from '0' to '9'. The default value is '9'.
- **Mode**: There are two supported modes. 'compress' and 'deflate'. The default value is
'compress'.

All options can be set at initiation or by using a related method. For example, the related methods
for 'Level' are `getLevel()` and `setLevel()`. You can also use the `setOptions()` method which
accepts all options as array.

### Lzf Adapter

The Lzf Adapter can compress and decompress:

- Strings

> ## Note
#### Lzf supports only strings
The Lzf adapter can not handle files and directories.

This adapter makes use of *PHP*'s Lzf extension.

There are no options available to customize this adapter.

### Rar Adapter

The Rar Adapter can compress and decompress:

- Files
- Directories

> ## Note
#### Rar does not support strings
The Rar Adapter can not handle strings.

This adapter makes use of *PHP*'s Rar extension.

> ## Note
#### Rar compression not supported
Due to restrictions with the Rar compression format, there is no compression available for free.
When you want to compress files into a new Rar archive, you must provide a callback to the adapter
that can invoke a Rar compression program.

To customize the compression this adapter supports the following options:

- **Archive**: This parameter sets the archive file which should be used or created.
- **Callback**: A callback which provides compression support to this adapter.
- **Password**: The password which has to be used for decompression.
- **Target**: The target where the decompressed files will be written to.

All options can be set at instantiation or by using a related method. For example, the related
methods for 'Target' are `getTarget()` and `setTarget()`. You can also use the `setOptions()` method
which accepts all options as array.

### Tar Adapter

The Tar Adapter can compress and decompress:

- Files
- Directories

> ## Note
#### Tar does not support strings
The Tar Adapter can not handle strings.

This adapter makes use of *PEAR*'s `Archive_Tar` component.

To customize the compression this adapter supports the following options:

- **Archive**: This parameter sets the archive file which should be used or created.
- **Mode**: A mode to use for compression. Supported are either '`NULL`' which means no compression
at all, 'Gz' which makes use of *PHP*'s Zlib extension and 'Bz2' which makes use of *PHP*'s Bz2
extension. The default value is '`NULL`'.
- **Target**: The target where the decompressed files will be written to.

All options can be set at instantiation or by using a related method. For example, the related
methods for 'Target' are `getTarget()` and `setTarget()`. You can also use the `setOptions()` method
which accepts all options as array.

> ## Note
#### Directory usage
When compressing directories with Tar then the complete file path is used. This means that created
Tar files will not only have the subdirectory but the complete path for the compressed file.

### Zip Adapter

The Zip Adapter can compress and decompress:

- Strings
- Files
- Directories

> ## Note
#### Zip does not support string decompression
The Zip Adapter can not handle decompression to a string; decompression will always be written to a
file.

This adapter makes use of *PHP*'s `Zip` extension.

To customize the compression this adapter supports the following options:

- **Archive**: This parameter sets the archive file which should be used or created.
- **Target**: The target where the decompressed files will be written to.

All options can be set at instantiation or by using a related method. For example, the related
methods for 'Target' are `getTarget()` and `setTarget()`. You can also use the `setOptions()` method
which accepts all options as array.

## Digits

Returns the string `$value`, removing all but digits.

### Supported Options

There are no additional options for `Zend\Filter\Digits`.

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Digits();

print $filter->filter('October 2012');
```

This returns "2012".

```php
$filter = new Zend\Filter\Digits();

print $filter->filter('HTML 5 for Dummies');
```

This returns "5".

## Dir

Given a string containing a path to a file, this function will return the name of the directory.

### Supported Options

There are no additional options for `Zend\Filter\Dir`.

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Dir();

print $filter->filter('/etc/passwd');
```

This returns "`/etc`".

```php
$filter = new Zend\Filter\Dir();

print $filter->filter('C:/Temp/x');
```

This returns "`C:/Temp`".

## Encrypt and Decrypt

These filters allow to encrypt and decrypt any given string. Therefor they make use of Adapters.
Actually there are adapters for the `Zend\Crypt\BlockCipher` class and the `OpenSSL` extension of
*PHP*.

### Supported Options

The following options are supported for `Zend\Filter\Encrypt` and `Zend\Filter\Decrypt`:

- **adapter**: This sets the encryption adapter which should be used
- **algorithm**: Only `BlockCipher`. The algorithm which has to be used by the adapter
`Zend\Crypt\Symmetric\Mcrypt`. It should be one of the algorithm ciphers supported by
`Zend\Crypt\Symmetric\Mcrypt` (see the `getSupportedAlgorithms()` method). If not set it defaults to
`aes`, the Advanced Encryption Standard (see
Zend\\\\Crypt\\\\BlockCipher&lt;zend.crypt.blockcipher&gt; for more details).
- **compression**: If the encrypted value should be compressed. Default is no compression.
- **envelope**: Only `OpenSSL`. The encrypted envelope key from the user who encrypted the content.
You can either provide the path and filename of the key file, or just the content of the key file
itself. When the `package` option has been set, then you can omit this parameter.
- **key**: Only `BlockCipher`. The encryption key with which the input will be encrypted. You need
the same key for decryption.
- **mode**: Only `BlockCipher`. The encryption mode which has to be used. It should be one of the
modes which can be found under [PHP's mcrypt modes](http://php.net/manual/en/mcrypt.constants.php).
If not set it defaults to 'cbc'.
- **mode\_directory**: Only `BlockCipher`. The directory where the mode can be found. If not set it
defaults to the path set within the `Mcrypt` extension.
- **package**: Only `OpenSSL`. If the envelope key should be packed with the encrypted value.
Default is `FALSE`.
- **private**: Only `OpenSSL`. Your private key which will be used for encrypting the content. You
can either provide the path and filename of the key file, or just the content of the key file
itself.
- **public**: Only `OpenSSL`. The public key of the user whom you want to provide the encrypted
content. You can either provide the path and filename of the key file, or just the content of the
key file itself.
- **vector**: Only `BlockCipher`. The initialization vector which shall be used. If not set it will
be a random vector.

### Adapter Usage

As these two encryption methodologies work completely different, also the usage of the adapters
differ. You have to select the adapter you want to use when initiating the filter.

```php
// Use the BlockCipher adapter
$filter1 = new Zend\Filter\Encrypt(array('adapter' => 'BlockCipher'));

// Use the OpenSSL adapter
$filter2 = new Zend\Filter\Encrypt(array('adapter' => 'openssl'));
```

To set another adapter you can also use `setAdapter()`, and the `getAdapter()` method to receive the
actual set adapter.

```php
// Use the OpenSSL adapter
$filter = new Zend\Filter\Encrypt();
$filter->setAdapter('openssl');
```

> ## Note
When you do not supply the `adapter` option or do not use `setAdapter()`, then the `BlockCipher`
adapter will be used per default.

### Encryption with BlockCipher

To encrypt a string using the `BlockCipher` you have to specify the encryption key using the
`setKey()` method or passing it during the constructor.

```php
// Use the default AES encryption algorithm
$filter = new Zend\Filter\Encrypt(array('adapter' => 'BlockCipher'));
$filter->setKey('encryption key');

// or
// $filter = new Zend\Filter\Encrypt(array(
//     'adapter' => 'BlockCipher',
//     'key'     => 'encryption key'
// ));

$encrypted = $filter->filter('text to be encrypted');
printf ("Encrypted text: %s\n", $encrypted);
```

You can get and set the encryption values also afterwards with the `getEncryption()` and
`setEncryption()` methods.

```php
// Use the default AES encryption algorithm
$filter = new Zend\Filter\Encrypt(array('adapter' => 'BlockCipher'));
$filter->setKey('encryption key');
var_dump($filter->getEncryption());

// Will print:
//array(4) {
//  ["key_iteration"]=>
//  int(5000)
//  ["algorithm"]=>
//  string(3) "aes"
//  ["hash"]=>
//  string(6) "sha256"
//  ["key"]=>
//  string(14) "encryption key"
//}
```

> ## Note
The `BlockCipher` adapter uses the [Mcrypt](http://php.net/mcrypt) PHP extension by default. That
means you will need to install the Mcrypt module in your PHP environment.

If you don't specify an initialization Vector (salt or iv), the BlockCipher will generate a random
value during each encryption. If you try to execute the following code the output will be always
different (note that even if the output is always different you can decrypt it using the same key).

```php
$key  = 'encryption key';
$text = 'message to encrypt';

// use the default adapter that is BlockCipher
$filter = new \Zend\Filter\Encrypt();
$filter->setKey('encryption key');
for ($i=0; $i < 10; $i++) {
   printf("%d) %s\n", $i, $filter->filter($text));
}
```

If you want to obtain the same output you need to specify a fixed Vector, using the setVector()
method. This script will produce always the same encryption output.

```php
// use the default adapter that is BlockCipher
$filter = new \Zend\Filter\Encrypt();
$filter->setKey('encryption key');
$filter->setVector('12345678901234567890');
printf("%s\n", $filter->filter('message'));

// output:
//
04636a6cb8276fad0787a2e187803b6557f77825d5ca6ed4392be702b9754bb3MTIzNDU2Nzg5MDEyMzQ1NgZ+zPwTGpV6gQqPKECinig=
```

> ## Note
For a security reason it's always better to use a different Vector on each encryption. We suggest to
use the setVector() method only if you really need it.

### Decryption with BlockCipher

For decrypting content which was previously encrypted with `BlockCipher` you need to have the
options with which the encryption has been called.

If you used only the encryption key, you can just use it to decrypt the content. As soon as you have
provided all options decryption is as simple as encryption.

```php
$content =
'04636a6cb8276fad0787a2e187803b6557f77825d5ca6ed4392be702b9754bb3MTIzNDU2Nzg5MDEyMzQ1NgZ+zPwTGpV6gQqPKECinig=';
// use the default adapter that is BlockCipher
$filter = new Zend\Filter\Decrypt();
$filter->setKey('encryption key');
printf("Decrypt: %s\n", $filter->filter($content));

// output:
// Decrypt: message
```

Note that even if we did not specify the same Vector, the `BlockCipher` is able to decrypt the
message because the Vector is stored in the encryption string itself (note that the Vector can be
stored in plaintext, it is not a secret, the Vector is only used to improve the randomness of the
encryption algorithm).

> ## Note
You should also note that all settings which be checked when you create the instance or when you
call `setEncryption()`.

### Encryption with OpenSSL

When you have installed the `OpenSSL` extension you can use the `OpenSSL` adapter. You can get or
set the public key also afterwards with the `getPublicKey()` and `setPublicKey()` methods. The
private key can also be get and set with the related `getPrivateKey()` and `setPrivateKey()`
methods.

```php
// Use openssl and provide a private key
$filter = new Zend\Filter\Encrypt(array(
   'adapter' => 'openssl',
   'private' => '/path/to/mykey/private.pem'
));

// of course you can also give the public keys at initiation
$filter->setPublicKey('/public/key/path/public.pem');
```

> ## Note
Note that the `OpenSSL` adapter will not work when you do not provide valid keys.

When you want to decode content which was encoded with a passphrase you will not only need the
public key, but also the passphrase to decode the encrypted key.

```php
// Use openssl and provide a private key
$filter = new Zend\Filter\Encrypt(array(
   'adapter' => 'openssl',
   'passphrase' => 'enter here the passphrase for the private key',
   'private' => '/path/to/mykey/private.pem',
   'public' => '/public/key/path/public.pem'
));
```

At last, when you use OpenSSL you need to give the receiver the encrypted content, the passphrase
when have provided one, and the envelope keys for decryption.

This means for you, that you have to get the envelope keys after the encryption with the
`getEnvelopeKey()` method.

So our complete example for encrypting content with `OpenSSL` look like this.

```php
// Use openssl and provide a private key
$filter = new Zend\Filter\Encrypt(array(
   'adapter' => 'openssl',
   'passphrase' => 'enter here the passphrase for the private key',
   'private' => '/path/to/mykey/private.pem',
   'public' => '/public/key/path/public.pem'
));

$encrypted = $filter->filter('text_to_be_encoded');
$envelope  = $filter->getEnvelopeKey();
print $encrypted;

// For decryption look at the Decrypt filter
```

### Simplified usage with OpenSSL

As seen before, you need to get the envelope key to be able to decrypt the previous encrypted value.
This can be very annoying when you work with multiple values.

To have a simplified usage you can set the `package` option to `TRUE`. The default value is `FALSE`.

```php
// Use openssl and provide a private key
$filter = new Zend\Filter\Encrypt(array(
   'adapter' => 'openssl',
   'private' => '/path/to/mykey/private.pem',
   'public'  => '/public/key/path/public.pem',
   'package' => true
));

$encrypted = $filter->filter('text_to_be_encoded');
print $encrypted;

// For decryption look at the Decrypt filter
```

Now the returned value contains the encrypted value and the envelope. You don't need to get them
after the compression. But, and this is the negative aspect of this feature, the encrypted value can
now only be decrypted by using `Zend\Filter\Encrypt`.

### Compressing Content

Based on the original value, the encrypted value can be a very large string. To reduce the value
`Zend\Filter\Encrypt` allows the usage of compression.

The `compression` option can either be set to the name of a compression adapter, or to an array
which sets all wished options for the compression adapter.

```php
// Use basic compression adapter
$filter1 = new Zend\Filter\Encrypt(array(
   'adapter'     => 'openssl',
   'private'     => '/path/to/mykey/private.pem',
   'public'      => '/public/key/path/public.pem',
   'package'     => true,
   'compression' => 'bz2'
));

// Use basic compression adapter
$filter2 = new Zend\Filter\Encrypt(array(
   'adapter'     => 'openssl',
   'private'     => '/path/to/mykey/private.pem',
   'public'      => '/public/key/path/public.pem',
   'package'     => true,
   'compression' => array('adapter' => 'zip', 'target' => '\usr\tmp\tmp.zip')
));
```

> ## Note
#### Decryption with same settings
When you want to decrypt a value which is additionally compressed, then you need to set the same
compression settings for decryption as for encryption. Otherwise the decryption will fail.

### Decryption with OpenSSL

Decryption with `OpenSSL` is as simple as encryption. But you need to have all data from the person
who encrypted the content. See the following example:

```php
// Use openssl and provide a private key
$filter = new Zend\Filter\Decrypt(array(
   'adapter' => 'openssl',
   'private' => '/path/to/mykey/private.pem'
));

// of course you can also give the envelope keys at initiation
$filter->setEnvelopeKey('/key/from/encoder/envelope_key.pem');
```

> ## Note
Note that the `OpenSSL` adapter will not work when you do not provide valid keys.

Optionally it could be necessary to provide the passphrase for decrypting the keys themself passing
the `passphrase` option.

```php
// Use openssl and provide a private key
$filter = new Zend\Filter\Decrypt(array(
   'adapter' => 'openssl',
   'passphrase' => 'enter here the passphrase for the private key',
   'private' => '/path/to/mykey/private.pem'
));

// of course you can also give the envelope keys at initiation
$filter->setEnvelopeKey('/key/from/encoder/envelope_key.pem');
```

At last, decode the content. Our complete example for decrypting the previously encrypted content
looks like this.

```php
// Use openssl and provide a private key
$filter = new Zend\Filter\Decrypt(array(
   'adapter' => 'openssl',
   'passphrase' => 'enter here the passphrase for the private key',
   'private' => '/path/to/mykey/private.pem'
));

// of course you can also give the envelope keys at initiation
$filter->setEnvelopeKey('/key/from/encoder/envelope_key.pem');

$decrypted = $filter->filter('encoded_text_normally_unreadable');
print $decrypted;
```

## HtmlEntities

Returns the string `$value`, converting characters to their corresponding *HTML* entity equivalents
where they exist.

### Supported Options

The following options are supported for `Zend\Filter\HtmlEntities`:

- **quotestyle**: Equivalent to the *PHP* htmlentities native function parameter **quote\_style**.
This allows you to define what will be done with 'single' and "double" quotes. The following
constants are accepted: `ENT_COMPAT`, `ENT_QUOTES` `ENT_NOQUOTES` with the default being
`ENT_COMPAT`.
- **charset**: Equivalent to the *PHP* htmlentities native function parameter **charset**. This
defines the character set to be used in filtering. Unlike the *PHP* native function the default is
'UTF-8'. See "<http://php.net/htmlentities>" for a list of supported character sets.

> ##     note
    >
    > This option can also be set via the `$options` parameter as a Traversable object or array. The
option key will be accepted as either charset or encoding.

- **doublequote**: Equivalent to the *PHP* htmlentities native function parameter
**double\_encode**. If set to false existing html entities will not be encoded. The default is to
convert everything (true).

> ##     note
    >
    > This option must be set via the `$options` parameter or the `setDoubleEncode()` method.

### Basic Usage

See the following example for the default behavior of this filter.

```php
$filter = new Zend\Filter\HtmlEntities();

print $filter->filter('<');
```

### Quote Style

`Zend\Filter\HtmlEntities` allows changing the quote style used. This can be useful when you want to
leave double, single, or both types of quotes un-filtered. See the following example:

```php
$filter = new Zend\Filter\HtmlEntities(array('quotestyle' => ENT_QUOTES));

$input  = "A 'single' and " . '"double"';
print $filter->filter($input);
```

The above example returns `A &#039;single&#039; and &quot;double&quot;`. Notice that `'single'` as
well as `"double"` quotes are filtered.

```php
$filter = new Zend\Filter\HtmlEntities(array('quotestyle' => ENT_COMPAT));

$input  = "A 'single' and " . '"double"';
print $filter->filter($input);
```

The above example returns `A 'single' and &quot;double&quot;`. Notice that `"double"` quotes are
filtered while `'single'` quotes are not altered.

```php
$filter = new Zend\Filter\HtmlEntities(array('quotestyle' => ENT_NOQUOTES));

$input  = "A 'single' and " . '"double"';
print $filter->filter($input);
```

The above example returns `A 'single' and "double"`. Notice that neither `"double"` or `'single'`
quotes are altered.

### Helper Methods

To change or retrieve the `quotestyle` after instantiation, the two methods `setQuoteStyle()` and
`getQuoteStyle()` may be used respectively. `setQuoteStyle()` accepts one parameter `$quoteStyle`.
The following constants are accepted: `ENT_COMPAT`, `ENT_QUOTES`, `ENT_NOQUOTES`

```php
$filter = new Zend\Filter\HtmlEntities();

$filter->setQuoteStyle(ENT_QUOTES);
print $filter->getQuoteStyle(ENT_QUOTES);
```

To change or retrieve the `charset` after instantiation, the two methods `setCharSet()` and
`getCharSet()` may be used respectively. `setCharSet()` accepts one parameter `$charSet`. See
"<http://php.net/htmlentities>" for a list of supported character sets.

```php
$filter = new Zend\Filter\HtmlEntities();

$filter->setQuoteStyle(ENT_QUOTES);
print $filter->getQuoteStyle(ENT_QUOTES);
```

To change or retrieve the `doublequote` option after instantiation, the two methods
`setDoubleQuote()` and `getDoubleQuote()` may be used respectively. `setDoubleQuote()` accepts one
boolean parameter `$doubleQuote`.

```php
$filter = new Zend\Filter\HtmlEntities();

$filter->setQuoteStyle(ENT_QUOTES);
print $filter->getQuoteStyle(ENT_QUOTES);
```

## ToInt

`Zend\Filter\ToInt` allows you to transform a scalar value which contains into an integer.

### Supported Options

There are no additional options for `Zend\Filter\ToInt`.

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\ToInt();

print $filter->filter('-4 is less than 0');
```

This will return '-4'.

### Migration from 2.0-2.3 to 2.4+

Version 2.4 adds support for PHP 7. In PHP 7, `int` is a reserved keyword, which required renaming
the `Int` filter. If you were using the `Int` filter directly previously, you will now receive an
`E_USER_DEPRECATED` notice on instantiation. Please update your code to refer to the `ToInt` class
instead.

Users pulling their `Int` filter instance from the filter plugin manager receive a `ToInt` instance
instead starting in 2.4.0.

## ToNull

This filter will change the given input to be `NULL` if it meets specific criteria. This is often
necessary when you work with databases and want to have a `NULL` value instead of a boolean or any
other type.

### Supported Options

The following options are supported for `Zend\Filter\ToNull`:

- **type**: The variable type which should be supported.

### Default Behavior

Per default this filter works like *PHP*'s `empty()` method; in other words, if `empty()` returns a
boolean `TRUE`, then a `NULL` value will be returned.

```php
$filter = new Zend\Filter\ToNull();
$value  = '';
$result = $filter->filter($value);
// returns null instead of the empty string
```

This means that without providing any configuration, `Zend\Filter\ToNull` will accept all input
types and return `NULL` in the same cases as `empty()`.

Any other value will be returned as is, without any changes.

### Changing the Default Behavior

Sometimes it's not enough to filter based on `empty()`. Therefor `Zend\Filter\ToNull` allows you to
configure which type will be converted and which not.

The following types can be handled:

- **boolean**: Converts a boolean **FALSE** value to `NULL`.
- **integer**: Converts an integer **0** value to `NULL`.
- **empty\_array**: Converts an empty **array** to `NULL`.
- **float**: Converts an float **0.0** value to `NULL`.
- **string**: Converts an empty string **''** to `NULL`.
- **zero**: Converts a string containing the single character zero (**'0'**) to `NULL`.
- **all**: Converts all above types to `NULL`. (This is the default behavior.)

There are several ways to select which of the above types are filtered. You can give one or multiple
types and add them, you can give an array, you can use constants, or you can give a textual string.
See the following examples:

```php
// converts false to null
$filter = new Zend\Filter\ToNull(Zend\Filter\ToNull::BOOLEAN);

// converts false and 0 to null
$filter = new Zend\Filter\ToNull(
    Zend\Filter\ToNull::BOOLEAN + Zend\Filter\ToNull::INTEGER
);

// converts false and 0 to null
$filter = new Zend\Filter\ToNull( array(
    Zend\Filter\ToNull::BOOLEAN,
    Zend\Filter\ToNull::INTEGER
));

// converts false and 0 to null
$filter = new Zend\Filter\ToNull(array(
    'boolean',
    'integer',
));
```

You can also give a Traversable or an array to set the wished types. To set types afterwards use
`setType()`.

### Migration from 2.0-2.3 to 2.4+

Version 2.4 adds support for PHP 7. In PHP 7, `null` is a reserved keyword, which required renaming
the `Null` filter. If you were using the `Null` filter directly previously, you will now receive an
`E_USER_DEPRECATED` notice on instantiation. Please update your code to refer to the `ToNull` class
instead.

Users pulling their `Null` filter instance from the filter plugin manager receive a `ToNull`
instance instead starting in 2.4.0.

## NumberFormat

The `NumberFormat` filter can be used to return locale-specific number and percentage strings. It
extends the `NumberParse` filter, which acts as wrapper for the `NumberFormatter` class within the
Internationalization extension (Intl).

### Supported Options

The following options are supported for `NumberFormat`:

`NumberFormat([ string $locale [, int $style [, int $type ]]])`

- `$locale`: (Optional) Locale in which the number would be formatted (locale name, e.g. en\_US). If
unset, it will use the default locale (`Locale::getDefault()`)

    Methods for getting/setting the locale are also available: `getLocale()` and `setLocale()`

- `$style`: (Optional) Style of the formatting, one of the [format style
constants](http://www.php.net/manual/class.numberformatter.php#intl.numberformatter-constants.unumberformatstyle).
If unset, it will use `NumberFormatter::DEFAULT_STYLE` as the default style.

    Methods for getting/setting the format style are also available: `getStyle()` and `setStyle()`

- `$type`: (Optional) The [formatting
type](http://www.php.net/manual/class.numberformatter.php#intl.numberformatter-constants.types) to
use. If unset, it will use `NumberFormatter::TYPE_DOUBLE` as the default type.

    Methods for getting/setting the format type are also available: `getType()` and `setType()`

### Basic Usage

```php
$filter = new \Zend\I18n\Filter\NumberFormat("de_DE");
echo $filter->filter(1234567.8912346);
// Returns "1.234.567,891"

$filter = new \Zend\I18n\Filter\NumberFormat("en_US", NumberFormatter::PERCENT);
echo $filter->filter(0.80);
// Returns "80%"

$filter = new \Zend\I18n\Filter\NumberFormat("fr_FR", NumberFormatter::SCIENTIFIC);
echo $filter->filter(0.00123456789);
// Returns "1,23456789E-3"
```

## NumberParse

The `NumberParse` filter can be used to parse a number from a string. It acts as a wrapper for the
`NumberFormatter` class within the Internationalization extension (Intl).

### Supported Options

The following options are supported for `NumberParse`:

`NumberParse([ string $locale [, int $style [, int $type ]]])`

- `$locale`: (Optional) Locale in which the number would be parsed (locale name, e.g. en\_US). If
unset, it will use the default locale (`Locale::getDefault()`)

    Methods for getting/setting the locale are also available: `getLocale()` and `setLocale()`

- `$style`: (Optional) Style of the parsing, one of the [format style
constants](http://www.php.net/manual/class.numberformatter.php#intl.numberformatter-constants.unumberformatstyle).
If unset, it will use `NumberFormatter::DEFAULT_STYLE` as the default style.

    Methods for getting/setting the parse style are also available: `getStyle()` and `setStyle()`

- `$type`: (Optional) The [parsing
type](http://www.php.net/manual/class.numberformatter.php#intl.numberformatter-constants.types) to
use. If unset, it will use `NumberFormatter::TYPE_DOUBLE` as the default type.

    Methods for getting/setting the parse type are also available: `getType()` and `setType()`

### Basic Usage

```php
$filter = new \Zend\I18n\Filter\NumberParse("de_DE");
echo $filter->filter("1.234.567,891");
// Returns 1234567.8912346

$filter = new \Zend\I18n\Filter\NumberParse("en_US", NumberFormatter::PERCENT);
echo $filter->filter("80%");
// Returns 0.80

$filter = new \Zend\I18n\Filter\NumberParse("fr_FR", NumberFormatter::SCIENTIFIC);
echo $filter->filter("1,23456789E-3");
// Returns 0.00123456789
```

## PregReplace

`Zend\Filter\PregReplace` performs a search using regular expressions and replaces all found
elements.

### Supported Options

The following options are supported for `Zend\Filter\PregReplace`:

- **pattern**: The pattern which will be searched for.
- **replacement**: The string which is used as replacement for the matches.

### Basic Usage

To use this filter properly you must give two options:

The option `pattern` has to be given to set the pattern which will be searched for. It can be a
string for a single pattern, or an array of strings for multiple pattern.

To set the pattern which will be used as replacement the option `replacement` has to be used. It can
be a string for a single pattern, or an array of strings for multiple pattern.

```php
$filter = new Zend\Filter\PregReplace(array(
    'pattern'     => '/bob/',
    'replacement' => 'john',
));
$input  = 'Hi bob!';

$filter->filter($input);
// returns 'Hi john!'
```

You can use `getPattern()` and `setPattern()` to set the matching pattern afterwards. To set the
replacement pattern you can use `getReplacement()` and `setReplacement()`.

```php
$filter = new Zend\Filter\PregReplace();
$filter->setMatchPattern(array('bob', 'Hi'))
      ->setReplacement(array('john', 'Bye'));
$input  = 'Hi bob!';

$filter->filter($input);
// returns 'Bye john!'
```

For a more complex usage take a look into *PHP*'s [PCRE Pattern
Chapter](http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php).

## RealPath

This filter will resolve given links and pathnames and returns canonicalized absolute pathnames.

### Supported Options

The following options are supported for `Zend\Filter\RealPath`:

- **exists**: This option defaults to `TRUE` which checks if the given path really exists.

### Basic Usage

For any given link of pathname its absolute path will be returned. References to '`/./`', '`/../`'
and extra '`/`' characters in the input path will be stripped. The resulting path will not have any
symbolic link, '`/./`' or '`/../`' character.

`Zend\Filter\RealPath` will return `FALSE` on failure, e.g. if the file does not exist. On *BSD*
systems `Zend\Filter\RealPath` doesn't fail if only the last path component doesn't exist, while
other systems will return `FALSE`.

```php
$filter = new Zend\Filter\RealPath();
$path   = '/www/var/path/../../mypath';
$filtered = $filter->filter($path);

// returns '/www/mypath'
```

### Non-Existing Paths

Sometimes it is useful to get also paths when they don't exist, f.e. when you want to get the real
path for a path which you want to create. You can then either give a `FALSE` at initiation, or use
`setExists()` to set it.

```php
$filter = new Zend\Filter\RealPath(false);
$path   = '/www/var/path/../../non/existing/path';
$filtered = $filter->filter($path);

// returns '/www/non/existing/path'
// even when file_exists or realpath would return false
```

## StringToLower

This filter converts any input to be lowercased.

### Supported Options

The following options are supported for `Zend\Filter\StringToLower`:

- **encoding**: This option can be used to set an encoding which has to be used.

### Basic Usage

This is a basic example:

```php
$filter = new Zend\Filter\StringToLower();

print $filter->filter('SAMPLE');
// returns "sample"
```

### Different Encoded Strings

Per default it will only handle characters from the actual locale of your server. Characters from
other charsets would be ignored. Still, it's possible to also lowercase them when the mbstring
extension is available in your environment. Simply set the wished encoding when initiating the
`StringToLower` filter. Or use the `setEncoding()` method to change the encoding afterwards.

```php
// using UTF-8
$filter = new Zend\Filter\StringToLower('UTF-8');

// or give an array which can be useful when using a configuration
$filter = new Zend\Filter\StringToLower(array('encoding' => 'UTF-8'));

// or do this afterwards
$filter->setEncoding('ISO-8859-1');
```

> ## Note
#### Setting wrong encodings
Be aware that you will get an exception when you want to set an encoding and the mbstring extension
is not available in your environment.
Also when you are trying to set an encoding which is not supported by your mbstring extension you
will get an exception.

## StringToUpper

This filter converts any input to be uppercased.

### Supported Options

The following options are supported for `Zend\Filter\StringToUpper`:

- **encoding**: This option can be used to set an encoding which has to be used.

### Basic Usage

This is a basic example for using the `StringToUpper` filter:

```php
$filter = new Zend\Filter\StringToUpper();

print $filter->filter('Sample');
// returns "SAMPLE"
```

### Different Encoded Strings

Like the `StringToLower` filter, this filter handles only characters from the actual locale of your
server. Using different character sets works the same as with `StringToLower`.

```php
$filter = new Zend\Filter\StringToUpper(array('encoding' => 'UTF-8'));

// or do this afterwards
$filter->setEncoding('ISO-8859-1');
```

## StringTrim

This filter modifies a given string such that certain characters are removed from the beginning and
end.

### Supported Options

The following options are supported for `Zend\Filter\StringTrim`:

- **charlist**: List of characters to remove from the beginning and end of the string. If this is
not set or is null, the default behavior will be invoked, which is to remove only whitespace from
the beginning and end of the string.

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\StringTrim();

print $filter->filter(' This is (my) content: ');
```

The above example returns 'This is (my) content:'. Notice that the whitespace characters have been
removed.

### Default Behavior

```php
$filter = new Zend\Filter\StringTrim(':');
// or new Zend\Filter\StringTrim(array('charlist' => ':'));

print $filter->filter(' This is (my) content:');
```

The above example returns 'This is (my) content'. Notice that the whitespace characters and colon
are removed. You can also provide a Traversable or an array with a 'charlist' key. To set the
desired character list after instantiation, use the `setCharList()` method. The `getCharList()`
return the values set for charlist.

## StripNewlines

This filter modifies a given string and removes all new line characters within that string.

### Supported Options

There are no additional options for `Zend\Filter\StripNewlines`:

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\StripNewlines();

print $filter->filter(' This is (my)``\n\r``content: ');
```

The above example returns 'This is (my) content:'. Notice that all newline characters have been
removed.

## StripTags

This filter can strip XML and HTML tags from given content.

> ## Warning
#### Zend\\Filter\\StripTags is potentially unsecure
Be warned that Zend\\Filter\\StripTags should only be used to strip all available tags.
Using Zend\\Filter\\StripTags to make your site secure by stripping some unwanted tags will lead to
unsecure and dangerous code.
Zend\\Filter\\StripTags must not be used to prevent XSS attacks. This filter is no replacement for
using Tidy or HtmlPurifier.

### Supported Options

The following options are supported for `Zend\Filter\StripTags`:

- **allowAttribs**: This option sets the attributes which are accepted. All other attributes are
stripped from the given content.
- **allowTags**: This option sets the tags which are accepted. All other tags will be stripped from
the given content.

### Basic Usage

See the following example for the default behaviour of this filter:

```php
$filter = new Zend\Filter\StripTags();

print $filter->filter('<B>My content</B>');
```

As result you will get the stripped content 'My content'.

When the content contains broken or partial tags then the complete following content will be erased.
See the following example:

```php
$filter = new Zend\Filter\StripTags();

print $filter->filter('This contains <a href="http://example.com">no ending tag');
```

The above will return 'This contains' with the rest being stripped.

### Allowing Defined Tags

`Zend\Filter\StripTags` allows stripping of all but defined tags. This can be used for example to
strip all tags but links from a text.

```php
$filter = new Zend\Filter\StripTags(array('allowTags' => 'a'));

$input  = "A text with <br/> a <a href='link.com'>link</a>";
print $filter->filter($input);
```

The above will return 'A text with a &lt;a href='link.com'&gt;link&lt;/a&gt;' as result. It strips
all tags but the link. By providing an array you can set multiple tags at once.

> ## Warning
Do not use this feature to get a probably secure content. This component does not replace the use of
a proper configured html filter.

### Allowing Defined Attributes

It is also possible to strip all but allowed attributes from a tag.

```php
$filter = new Zend\Filter\StripTags(array('allowTags' => 'img', 'allowAttribs' => 'src'));

$input  = "A text with <br/> a <img src='picture.com' width='100'>picture</img>";
print $filter->filter($input);
```

The above will return 'A text with a &lt;img src='picture.com'&gt;picture&lt;/img&gt;' as result. It
strips all tags but img. Additionally from the img tag all attributes but src will be stripped. By
providing an array you can set multiple attributes at once.

### Allowing Advanced Defined Tags with Attributes

You can pass the allowed tags with their attributes in a single array to the constructor.

```php
$allowedElements = array(
    'img' => array(
        'src',
        'width'
    ),
    'a' => array(
        'href'
    )
);
$filter = new Zend\Filter\StripTags($allowedElements);

$input  = "A text with <br/> a <img src='picture.com' width='100'>picture</img> click " .
          "<a href='http://picture.com/zend' id='hereId'>here</a>!";
print $filter->filter($input);
```

The above will return 'A text with a &lt;img src='picture.com' width='100'&gt;picture&lt;/img&gt;
click &lt;a href='<http://picture.com/zend>'&gt;here&lt;/a&gt;!' as result.

## UriNormalize

This filter can set a scheme on an URI, if a scheme is not present. If a scheme is present, that
scheme will not be affected, even if a different scheme is enforced.

### Supported Options

The following options are supported for `Zend\Filter\UriNormalize`:

- **defaultScheme**: This option can be used to set the default scheme to use when parsing
scheme-less URIs.
- **enforcedScheme**: Set a URI scheme to enforce on schemeless URIs.

### Basic Usage

See the following example for the default behaviour of this filter:

```php
$filter = new Zend\Filter\UriNormalize(array(
    'enforcedScheme' => 'https'
));

echo $filter->filter('www.example.com');
```

As the result the string `https://www.example.com` will be output.

## Whitelist

This filter will return `null` if the value being filtered is not present the filter's allowed list
of values. If the value is present, it will return that value.

For the opposite functionality see the `Blacklist` filter.

### Supported Options

The following options are supported for `Zend\Filter\Whitelist`:

- **strict**: Uses strict mode when comparing: passed through to `in_array`'s third argument.
- **list**: An array of allowed values.

### Basic Usage

This is a basic example:

```php
$whitelist = new \Zend\Filter\Whitelist(array(
    'list' => array('allowed-1', 'allowed-2')
));
echo $whitelist->filter('allowed-2');   // => 'allowed-2'
echo $whitelist->filter('not-allowed'); // => null
```
