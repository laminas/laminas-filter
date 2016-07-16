# File Filters

zend-filter also comes with a set of classes for filtering file contents, and
performing file operations such as renaming.

> ## $_FILES
>
> All file filter `filter()` implementations support either a file path string
> *or* a `$_FILES` array as the supplied argument. When a `$_FILES` array is
> passed in, the `tmp_name` is used for the file path.

## Decrypt

- TODO

## Encrypt

- TODO

## Lowercase

`Zend\Filter\File\Lowercase` can be used to convert given file content to 
lowercase. 

### Supported Options

The following set of options are supported:

 - `encoding`: This option can be used to set an encoding to use.

### Basic Usage

Basically, convert all content of a file to lowercase:

```php
use Zend\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'

$filter = new \Zend\Filter\File\LowerCase();
$filter->filter($files['my-upload']);
```

In this example, your temporary file content converted to lowercase characters 
after you uploaded. After this process, you can use [Rename](#rename) or 
[RenameUpload](#renameupload) filter to replace this file with your original 
file, or read directly from file. But, don't forget, if you upload a file and 
send your $_FILES array to filter method, LowerCase filter only change your 
temporary file (`tmp_name` index of array), not your original file. Let's check 
following example:

```php
use Zend\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'

$lowercaseFilter = new \Zend\Filter\File\LowerCase();
$file = $lowercaseFilter->filter($files['userfile']);
$renameFilter    = new \Zend\Filter\File\Rename([
    'target'    => '/tmp/newfile.txt',
    'randomize' => true,
]);
$filename = $renameFilter->filter($file['tmp_name']);
```

With this example, we can easily reach content of the converted file. 

If you want to use spesific encoding while converting file content, you should 
send encoding while initiating the `LowerCase` filter or use the `setEncoding`
method to change. Let's check below example:

```
use Zend\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'

$filter = new \Zend\Filter\File\LowerCase();
$filter->setEncoding('ISO-8859-1');
$filter->filter($files['my-upload']);
```

Additionally, `LowerCase` filter extended from `StringToLower`. You can check 
[`StringToLower` documentation](/zend-filter/standard-filters/#stringtolower) 
for more information about encoding and its exceptions.

## Rename

`Zend\Filter\File\Rename` can be used to rename a file and/or move a file to a new path.

### Supported Options

The following set of options are supported:

- `target` (string; default: `*`): Target filename or directory; the new name
  of the source file.
- `source` (string; default: `*`): Source filename or directory which will be
  renamed. Used to match the filtered file with an options set.
- `overwrite` (boolean; default: `false`): Shall existing files be overwritten?
  If the file is unable to be moved into the target path, a
  `Zend\Filter\Exception\RuntimeException` will be thrown.
- `randomize` (boolean; default: `false`): Shall target files have a random
  postfix attached? The random postfix will generated with `uniqid('_')` after
  the file name and before the extension. For example, `file.txt` might be
  randomized to `file_4b3403665fea6.txt`.

An array of option sets is also supported, where a single `Rename` filter
instance can filter several files using different options. The options used for
the filtered file will be matched from the `source` option in the options set.

### Usage Examples

Move all filtered files to a different directory:

```php
// 'target' option is assumed if param is a string
$filter = new \Zend\Filter\File\Rename('/tmp/');
echo $filter->filter('./myfile.txt');
// File has been moved to '/tmp/myfile.txt'
```

Rename all filtered files to a new name:

```php
$filter = new \Zend\Filter\File\Rename('/tmp/newfile.txt');
echo $filter->filter('./myfile.txt');
// File has been renamed to '/tmp/newfile.txt'
```

Move to a new path, and randomize file names:

```php
$filter = new \Zend\Filter\File\Rename([
    'target'    => '/tmp/newfile.txt',
    'randomize' => true,
]);
echo $filter->filter('./myfile.txt');
// File has been renamed to '/tmp/newfile_4b3403665fea6.txt'
```

Configure different options for several possible source files:

```php
$filter = new \Zend\Filter\File\Rename([
    [
        'source'    => 'fileA.txt'
        'target'    => '/dest1/newfileA.txt',
        'overwrite' => true,
    ],
    [
        'source'    => 'fileB.txt'
        'target'    => '/dest2/newfileB.txt',
        'randomize' => true,
    ],
]);
echo $filter->filter('fileA.txt');
// File has been renamed to '/dest1/newfileA.txt'
echo $filter->filter('fileB.txt');
// File has been renamed to '/dest2/newfileB_4b3403665fea6.txt'
```

### Public Methods

The `Rename` filter defines the following public methods in addition to `filter()`:
follows:

- `getFile() : array`: Returns the files to rename along with their new name and location.
- `setFile(string|array $options) : void`: Sets the file options for renaming.
  Removes any previously set file options.
- `addFile(string|array $options) : void`: Adds file options for renaming to
  the current list of file options.

## RenameUpload

`Zend\Filter\File\RenameUpload` can be used to rename or move an uploaded file to a new path.

### Supported Options

The following set of options are supported:

- `target` (string; default: `*`): Target directory or full filename path.
- `overwrite` (boolean; default: `false`): Shall existing files be overwritten?
  If the file is unable to be moved into the target path, a
  `Zend\Filter\Exception\RuntimeException` will be thrown.
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

> #### Using the upload name is unsafe
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
use Zend\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'
// i.e. $files['my-upload']['name'] === 'myfile.txt'

// 'target' option is assumed if param is a string
$filter = new \Zend\Filter\File\RenameUpload('./data/uploads/');
echo $filter->filter($files['my-upload']);
// File has been moved to './data/uploads/php5Wx0aJ'

// ... or retain the uploaded file name
$filter->setUseUploadName(true);
echo $filter->filter($files['my-upload']);
// File has been moved to './data/uploads/myfile.txt'
```

Rename all filtered files to a new name:

```php
use Zend\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'

$filter = new \Zend\Filter\File\Rename('./data/uploads/newfile.txt');
echo $filter->filter($files['my-upload']);
// File has been renamed to './data/uploads/newfile.txt'
```

Move to a new path and randomize file names:

```php
use Zend\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'

$filter = new \Zend\Filter\File\Rename(array(
    'target'    => './data/uploads/newfile.txt',
    'randomize' => true,
));
echo $filter->filter($files['my-upload']);
// File has been renamed to './data/uploads/newfile_4b3403665fea6.txt'
```

## Uppercase

`Zend\Filter\File\Uppercase` can be used to convert given file content to 
uppercase. 

### Supported Options

The following set of options are supported:

 - `encoding`: This option can be used to set an encoding to use.

### Basic Usage

Basically, convert all content of a file to lowercase:

```php
use Zend\Http\PhpEnvironment\Request;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['tmp_name'] === '/tmp/php5Wx0aJ'

$filter = new \Zend\Filter\File\UpperCase();
$filter->filter($files['my-upload']);
```

You can check [`LowerCase`](#lowercase) filter for more information. 