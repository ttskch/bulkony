# bulkony

[![](https://github.com/ttskch/bulkony/actions/workflows/ci.yaml/badge.svg?branch=main)](https://github.com/ttskch/bulkony/actions/workflows/ci.yaml?query=branch:main)
[![codecov](https://codecov.io/gh/ttskch/bulkony/graph/badge.svg?token=zwZHUbGrHp)](https://codecov.io/gh/ttskch/bulkony)
[![Packagist Version](https://img.shields.io/packagist/v/ttskch/bulkony?style=flat-square)](https://packagist.org/packages/ttskch/bulkony)
[![Packagist Downloads](https://img.shields.io/packagist/dm/ttskch/bulkony?style=flat-square)](https://packagist.org/packages/ttskch/bulkony)

Easy and flexible CSV exports and imports in PHP ⚡

```php
use Ttskch\Bulkony\Import\Importer;

$importer = new Importer();
$rowVisitor = new App\ValidatableRowVisitor();

$importer->import('/path/to/input.csv', $rowVisitor);

if ($importer->getErrorListCollection()->isEmpty()) {
    echo "Successfully imported!\n";
}
```

## TOC

<details>

* [Features](#features)
* [Requirements](#requirements)
* [Installation](#installation)
* [Usage](#usage)
    * [Export](#export)
        * [Export to file](#export-to-file)
        * [Send HTTP response in WAF way](#send-http-response-in-waf-way)
            * [Symfony](#symfony)
            * [Laravel](#laravel)
            * [CakePHP](#cakephp)
    * [Import](#import)
        * [With validation](#with-validation)
        * [With previewing feature](#with-previewing-feature)
* [Getting involved](#getting-involved)

</details>

## Features

* Multibyte support
* MS Excel friendly (exports as UTF-8 CSV with BOM)
* Memory efficient (unless you import non UTF-8 CSV)
* Easy to validate row by row
* Easy to implement preview feature, that shows which cell will be changed after importing

## Requirements

* PHP >= 7.4
* ext-mbstring

## Installation

```shell
$ composer require ttskch/bulkony
```

## Usage

### Export

```php
use Ttskch\Bulkony\Export\Exporter;

$exporter = new Exporter();
$rowGenerator = new App\UserRowGenerator();

$exporter->exportAndOutput('users.csv', $rowGenerator); // send HTTP response for downloading
```

```php
namespace App;

use Ttskch\Bulkony\Export\RowGenerator\RowGeneratorInterface;

class UserRowGenerator implements RowGeneratorInterface
{
    public function __construct(private $userRepository)
    {
    }

    public function getHeadingRows(): array
    {
        // return 2D array so that you can export multiple header rows
        return [['id', 'name', 'email']];
    }

    public function getBodyRowsIterator(): iterable
    {
        while ($user = $this->userRepository->findNext()) {
            // yield 2D array so that you can export multiple rows for one data
            yield [
                [$user->getId(), $user->getName(), $user->getEmail()],
            ];
        }
    }
}
```

#### Export to file

```php
use Ttskch\Bulkony\Export\Exporter;

$exporter = new Exporter();
$rowGenerator = new App\UserRowGenerator();

$exporter->export('/path/to/output.csv', $rowGenerator);
```

#### Send HTTP response in WAF way

##### Symfony

```php
$response = new StreamedResponse();
$response->headers->set('Content-Type', 'text/csv');
$response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, 'users.csv'));
$response->setCallback(function () use ($exporter, $rowGenerator) {
    $exporter->export('php://output', $rowGenerator);
});

return $response->send();
```

##### Laravel

```php
return response()
    ->header('Content-Type', 'text/csv')
    ->streamDownload(function () use ($exporter, $rowGenerator) {
        $exporter->export('php://output', $rowGenerator);
    }, 'users.csv');
```

##### CakePHP

```php
$stream = new CallbackStream(function () use ($exporter, $rowGenerator) {
    $exporter->export('php://output', $rowGenerator);
});

return $response
    ->withType('csv')
    ->withDownload('users.csv')
    ->withBody($stream);
```

### Import

```php
use Ttskch\Bulkony\Import\Importer;

$importer = new Importer();
$rowVisitor = new App\UserRowVisitor();

$importer->import('/path/to/input.csv', $rowVisitor);
```

```php
namespace App;

use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\RowVisitorInterface;

class UserRowVisitor implements RowVisitorInterface
{
    public function __constructor(private $userRepository)
    {
    }

    public function import(array $csvRow, int $csvLineNumber, Context $context): void
    {
        $this->userRepository->persist($this->hydrate($csvRow));
    }
    
    private function hydrate(array $csvRow): App\User
    {
        // create App\User instance from csv row in some way
        return new App\User($csvRow);
    }
}
```

#### With validation

```php
use Ttskch\Bulkony\Import\Importer;

$importer = new Importer();
$rowVisitor = new App\UserRowVisitor();

$importer->import('/path/to/input.csv', $rowVisitor);

if ($importer->getErrorListCollection()->isEmpty()) {
    echo "Successfully imported!\n";
} else {
    // you can access to validation errors by csv line number and column (heading) name
    // in other words,
    //   ErrorListCollection : errors in whole csv file
    //   ErrorList           : errors in one csv row
    //   Error               : errors in one csv cell (can contain multiple error messages)
    foreach ($importer->getErrorListCollection() as $errorList) {
        foreach ($errorList as $error) {
            foreach ($error->getMessages() as $message) {
                echo sprintf("Error: row %d col `%s`: %s\n", $errorList->getCsvLineNumber(), $error->getCsvHeading(), $message);
            }
        }
    }
}
```

```php
namespace App;

use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\ValidatableRowVisitorInterface;
use Ttskch\Bulkony\Import\Validation\ErrorList;

class UserRowVisitor implements ValidatableRowVisitorInterface
{
    public function __constructor(private $userRepository, private $validator)
    {
    }

    public function import(array $csvRow, int $csvLineNumber, Context $context): void
    {
        $this->userRepository->persist($this->hydrate($csvRow));
    }

    public function validate(array $csvRow, int $csvLineNumber, ErrorList $errorList, Context $context): void
    {
        $user = $this->hydrate($csvRow);

        foreach ($this->validator->validate($user) as $validationError) {
            // get csv heading name from validation error in some way
            $csvHeading = $this->getCsvHeadingFromValidationError($validationError);

            // upsert Error into ErrorList
            $errorList->get($csvHeading, true)->addMessage($validationError->getMessage());
        }
    }

    public function onError(array $csvRow, int $csvLineNumber,  ErrorList $errorList, Context $context): bool
    {
        // you can log errors for one csv row or do something here...
        
        // you can choose continue or abort on error occurred
        return ValidatableRowVisitorInterface::CONTINUE_ON_ERROR;
        // return ValidatableRowVisitorInterface::ABORT_ON_ERROR;
    }
    
    private function hydrate(array $csvRow): App\User
    {
        // create App\User instance from csv row in some way
        return new App\User($csvRow);
    }
}
```

In this example, you may find that `$this->hydrate($csvRow)` is called twice in `validate()` and `import()`. Sometimes this is not good.

If cost of hydrating object from csv row is very high, you can pass the hydrated object through `Context` like below.

```php
public function import(array $csvRow, int $csvLineNumber, Context $context): void
{
    // get hydrated $user
    $user = $context['user'];
    
    $this->userRepository->persist($user);
}

public function validate(array $csvRow, int $csvLineNumber, ErrorList $errorList, Context $context): void
{
    $user = $this->hydrate($csvRow);

    // pass hydrated $user
    $context['user'] = $user;    
    
    // validate $user ...
}
```

#### With previewing feature

```php
use Ttskch\Bulkony\Import\Importer;
use Ttskch\Bulkony\Import\Preview\Preview;

$importer = new Importer();
$rowVisitor = new App\UserRowVisitor();

/** @var Preview $preview */
$preview = $importer->preview('/path/to/input.csv', $rowVisitor);

// $preview contains whole csv data and knows WHICH CELL WILL BE CHANGED after importing
render('some/template', [
    'preview' => $preview,
]);
```

```php
namespace App;

use Ttskch\Bulkony\Import\Preview\Row;
use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\PreviewableRowVisitorInterface;

class UserRowVisitor implements PreviewableRowVisitorInterface
{
    // ...

    public function preview(array $csvRow, int $csvLineNumber, Row $previewRow, Context $context): void
    {
        $originalUser = $this->repository->find($csvRow['id']);
        $importedUser = $this->hydrate($csvRow);
        
        if ($originalUser->name !== $importedUser->name) {
            $previewRow->get('name')->setChanged();
        }

        if ($originalUser->email !== $importedUser->email) {
            $previewRow->get('email')->setChanged();
        }
    }
}
```

Of course you can implement previewing feature with validation.

In this example, if `App\UserRowVisitor` implements `ValidatableRowVisitorInterface`, `$preview` holds whole validation errors automatically. 

#### With preprocessing

You can preprocess all csv rows before importing and previewing and store some results in `Context`. 

```php
namespace App;

use Ttskch\Bulkony\Import\Preview\Row;
use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\PreprocessableRowVisitorInterface;
use Ttskch\Bulkony\Import\RowVisitor\PreviewableRowVisitorInterface;

class UserRowVisitor implements PreprocessableRowVisitorInterface, PreviewableRowVisitorInterface
{
    // ...

    public function preprocess(array $csvRow, int $csvLineNumber, Context $context): void
    {
        $originalUser = $this->repository->find($csvRow['id']);
        $importedUser = $this->hydrate($csvRow);

        $context[sprintf('originalUser%d', $csvLineNumber)] = $originalUser;
        $context[sprintf('importedUser%d', $csvLineNumber)] = $importedUser;
    }

    public function import(array $csvRow, int $csvLineNumber, Context $context): void
    {
        $user = $context[sprintf('originalUser%d', $csvLineNumber)];
        
        $this->userRepository->persist($user);
    }

    public function preview(array $csvRow, int $csvLineNumber, Row $previewRow, Context $context): void
    {
        $originalUser = $context[sprintf('originalUser%d', $csvLineNumber)];
        $importedUser = $context[sprintf('importedUser%d', $csvLineNumber)];
        
        if ($originalUser->name !== $importedUser->name) {
            $previewRow->get('name')->setChanged();
        }

        if ($originalUser->email !== $importedUser->email) {
            $previewRow->get('email')->setChanged();
        }
    }
}
```

For example, if the entity to be imported has a self-referential property and validation is required based on the member state of that property, it is necessary to pre-hydrate the entities corresponding to all rows in the CSV. This feature is useful in such cases.

## Getting involved

```shell
$ composer install
$ composer bin tools install

# Develop...

$ composer tests
```
