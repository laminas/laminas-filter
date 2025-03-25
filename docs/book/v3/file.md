# File Filters

laminas-filter also comes with a set of classes for filtering file contents, and
performing file operations such as renaming.

NOTE: **`$_FILES`**
All file filter `filter()` implementations support either a file path string *or* a `$_FILES` array as the supplied argument.
When a `$_FILES` array is passed in, the `tmp_name` is used for the file path.

## Lowercase

`Laminas\Filter\File\Lowercase` can be used to convert file contents to lowercase.

### Supported Options

The following set of options are supported:

- `encoding`: Set the encoding to use during conversion.

### Basic Usage

```php
use Laminas\Filter\File\LowerCase;
use Laminas\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'

$filter = new LowerCase();
$filter->filter($files['my-upload']);
```

This example converts the contents of an uploaded file to lowercase.  After this
process, you can use the [Rename](#rename) or [RenameUpload](#renameupload)
filter to replace this file with your original file, or read directly from file.
But, don't forget, if you upload a file and send your `$_FILES` array to a
filter method, the `LowerCase` filter will only change the temporary file
(`tmp_name` index of array), not the original file. Let's check following
example:

```php
use Laminas\Filter\File\LowerCase;
use Laminas\Filter\File\Rename;
use Laminas\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'

$lowercaseFilter = new LowerCase();
$file = $lowercaseFilter->filter($files['userfile']);
$renameFilter = new Rename([
    'target'    => '/tmp/newfile.txt',
    'randomize' => true,
]);
$filename = $renameFilter->filter($file['tmp_name']);
```

With this example, the final, stored file on the server will have the lowercased
content.

If you want to use a specific encoding when converting file content, you should
specify the encoding when instantiating the `LowerCase` filter, or use the
`setEncoding` method to change it.

```php
use Laminas\Filter\File\LowerCase;
use Laminas\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'

$filter = new LowerCase();
$filter->setEncoding('ISO-8859-1');
$filter->filter($files['my-upload']);
```

The `LowerCase` filter extends from the `StringToLower` filter; read the
[`StringToLower` documentation](standard-filters.md#stringtolower)
for more information about encoding and its exceptions.

## Rename

`Laminas\Filter\File\Rename` can be used to rename a file and/or move a file to a new path.

### Supported Options

The following set of options are supported:

- `match` (string; default: `*`): File name pattern to check if this filter should be applied to the input.
Read the [`fnmatch` documentation](https://www.php.net/manual/function.fnmatch.php) for more detail
- `target_directory` (string; default: `*`): Directory to move the file(s) to, if specified. The target directory must exist and be writable by the server process.
- `rename_to` (string: default: `*`): Change the filename to the specified value. A value of `*` *(The default)* preserves the existing file name.
- `overwrite` (boolean; default: `false`): Shall existing files be overwritten?
If the file is unable to be moved into the target path, a `Laminas\Filter\Exception\RuntimeException` will be thrown.
- `randomize` (boolean; default: `false`): Shall target files have a random postfix attached?
The random postfix will generated with `uniqid('_')` after the file name and before the extension.
For example, `file.txt` might be randomized to `file_4b3403665fea6.txt`.

A list of "option sets" is also supported, where a single `Rename` filter instance can be configured to match and rename files using different options based on the received filename.
for example, a list such as

```php
[
  ['match' => '*.txt', 'target_directory' => '/tmp/text-files'],
  ['match' => '*.pdf', 'target_directory' => '/tmp/pdf-files'],
];
```

### Usage Examples

Move all filtered files to a different directory:

```php
// 'target' option is assumed if param is a string
$filter = new \Laminas\Filter\File\Rename(['target_directory' => '/tmp/']);
echo $filter->filter('./myfile.txt');
// File has been moved to '/tmp/myfile.txt'
```

Rename all filtered files to a new name:

```php
$filter = new \Laminas\Filter\File\Rename(['rename_to' => 'newfile.txt');
echo $filter->filter('./myfile.txt');
// File has been renamed to 'newfile.txt'
```

Move to a new path, and randomize file names:

```php
$filter = new \Laminas\Filter\File\Rename([
    'match'    => '/tmp/*.txt',
    'randomize' => true,
]);
echo $filter->filter('./myfile.txt');
// File has been renamed to '/tmp/newfile_4b3403665fea6.txt'
```

Configure different options for several possible source files:

```php
$filter = new \Laminas\Filter\File\Rename([
    [
        'match'    => '*.pdf'
        'target_directory' => '/pdf-files/',
        'rename_to'    => 'newfileA.pdf',
        'overwrite' => true,
    ],
    [
        'match'    => '*.txt'
        'target_directory' => '/text-files/',
        'rename_to'    => 'newfileB.txt',
        'randomize' => true,
    ],
]);
echo $filter->filter('fileA.txt');
// File has been renamed to '/dest1/newfileA.txt'
echo $filter->filter('fileB.txt');
// File has been renamed to '/dest2/newfileB_4b3403665fea6.txt'
```

## RenameUpload

`Laminas\Filter\File\RenameUpload` can be used to rename or move an uploaded file to a new path.

### Supported Options

The following set of options are supported:

- `target` (string; default: `*`): Target directory or full filename path.
- `overwrite` (boolean; default: `false`): Shall existing files be overwritten?
  If the file is unable to be moved into the target path, a
  `Laminas\Filter\Exception\RuntimeException` will be thrown.
- `randomize` (boolean; default: `false`): Shall target files have a random
  postfix attached? The random postfix will generated with `uniqid('_')` after
  the file name and before the extension. For example, `file.txt` might be
  randomized to `file_4b3403665fea6.txt`.
- `use_upload_name` (boolean; default: `false`): When true, this filter will
  use `$_FILES['name']` as the target filename. Otherwise, the default `target`
  rules and the `$_FILES['tmp_name']` will be used.
- `use_upload_extension` (boolean; default: `false`): When true, the uploaded
  file will maintains its original extension if not specified.  For example, if
  the uploaded file is `file.txt` and the target is `mynewfile`, the upload
  will be renamed to `mynewfile.txt`.
- `stream_factory` (`Psr\Http\Message\StreamFactoryInterface`; default: `null`):
  Required when passing a [PSR-7 UploadedFileInterface](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface)
  to the filter; used to create a new stream representing the renamed file.
  (Since 2.9.0)
- `upload_file_factory` (`Psr\Http\Message\UploadedFileFactoryInterface`; default:
  `null`): Required when passing a [PSR-7 UploadedFileInterface](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface)
  to the filter; used to create a new uploaded file representation of the
  renamed file.  (Since 2.9.0)

> WARNING: **Using the upload Name is unsafe**
>
> Be **very** careful when using the `use_upload_name` option. For instance,
> extremely bad things could happen if you were to allow uploaded `.php` files
> (or other CGI files) to be moved into the `DocumentRoot`.
>
> It is generally a better idea to supply an internal filename to avoid
> security risks.

`RenameUpload` does not support an array of options like the`Rename` filter.
When filtering HTML5 file uploads with the `multiple` attribute set, all files
will be filtered with the same option settings.

### Usage Examples

Move all filtered files to a different directory:

```php
use Laminas\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'
// i.e. $files['my-upload']['name'] === 'myfile.txt'

// 'target' option is assumed if param is a string
$filter = new \Laminas\Filter\File\RenameUpload('./data/uploads/');
echo $filter->filter($files['my-upload']);
// File has been moved to './data/uploads/php5Wx0aJ'

// ... or retain the uploaded file name
$filter->setUseUploadName(true);
echo $filter->filter($files['my-upload']);
// File has been moved to './data/uploads/myfile.txt'
```

Rename all filtered files to a new name:

```php
use Laminas\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'

$filter = new \Laminas\Filter\File\RenameUpload('./data/uploads/newfile.txt');
echo $filter->filter($files['my-upload']);
// File has been renamed to './data/uploads/newfile.txt'
```

Move to a new path and randomize file names:

```php
use Laminas\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'

$filter = new \Laminas\Filter\File\RenameUpload([
    'target'    => './data/uploads/newfile.txt',
    'randomize' => true,
]);
echo $filter->filter($files['my-upload']);
// File has been renamed to './data/uploads/newfile_4b3403665fea6.txt'
```

Handle a PSR-7 uploaded file:

```php
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Laminas\Filter\File\RenameUpload;

$filter = new \Laminas\Filter\File\RenameUpload([
    'target'              => './data/uploads/',
    'randomize'           => true,
    // @var StreamFactoryInterface $streamFactory
    'stream_factory'      => $streamFactory,
    // @var UploadedFileFactoryInterface $uploadedFileFactory
    'upload_file_factory' => $uploadedFileFactory,
]);

// @var ServerRequestInterface $request
foreach ($request->getUploadedFiles() as $uploadedFile) {
    // @var UploadedFileInterface $uploadedFile
    // @var UploadedFileInterface $movedFile
    $movedFile = $filter->filter($uploadedFile);
    echo $movedFile->getClientFilename();
    // File has been renamed to './data/uploads/newfile_4b3403665fea6.txt'
}
```

> NOTE: **PSR-7 Support**
>
> PSR-7/PSR-17 support requires a valid [psr/http-factory-implementation](https://packagist.org/providers/psr/http-factory-implementation) in your application, as it relies on the stream and uploaded file factories in order to produce the final `UploadedFileInterface` artifact representing the filtered file.
>
> [laminas/laminas-diactoros](https://docs.laminas.dev/laminas-diactoros/) provides a PSR-17 implementation.

## Uppercase

`Laminas\Filter\File\Uppercase` can be used to convert file contents to uppercase.

### Supported Options

The following set of options are supported:

- `encoding`: Set the encoding to use during conversion.

### Basic Usage

```php
use Laminas\Filter\File\UpperCase;
use Laminas\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'

$filter = new UpperCase();
$filter->filter($files['my-upload']);
```

See the documentation on the [`LowerCase`](#lowercase) filter, above, for more information.
