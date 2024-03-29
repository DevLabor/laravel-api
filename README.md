# Laravel Package for building REST API rapidly.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/devlabor/laravel-api)](https://packagist.org/packages/devlabor/laravel-api)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/devlabor/laravel-api/run-tests?label=tests)](https://github.com/devlabor/laravel-api/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Sonarcloud Status](https://sonarcloud.io/api/project_badges/measure?project=DevLabor_laravel-api&metric=alert_status)](https://sonarcloud.io/dashboard?id=DevLabor_laravel-api)
[![Total Downloads](https://img.shields.io/packagist/dt/devlabor/laravel-api?style=flat)](https://packagist.org/packages/devlabor/laravel-api)

This package is based on "spatie/laravel-query-builder" allows you to rapidly creating API controllers for your Laravel application. This package also works with authorization policies.

## Basic Usage

Create a new API controller: `ProductApiController`:

```php
use DevLabor\Api\Http\Controllers\ApiController;

// ...

class ProductApiController extends ApiController
{
// ...
}
```

Extend your API routes within `routes/api.php`:

```php
// ...

Route::resource('products', 'Api\ProductApiController');
```

Sometimes you need an identifcation for the object in your api. For this reason, you can use the `DevLabor\Api\Http\Resources\ApiResource` as base class for your own resource classes:

```php
use DevLabor\Api\Http\Resources\ApiResource;

class Product extends ApiResource
{
//
}
```

## Installation

You can install the package via composer:

```bash
composer require devlabor/laravel-api
```

You can optionally publish the config file with:
```bash
php artisan vendor:publish --tag=api
```

This is the contents of the published config file:
```php
return [
	'pagination' => [
		/**
		 * Number of items per page returned in index()
		 */
		'items' => 20
	]
];
```

## Usage

### Configure policy authorization

By adapting the `$authorizeAbilities` member in the controller class, the authorization check can be partially restricted. 
```php
// ...

protected $authorizeAbilities = [
	'viewAny', // index
	'view', // show
	'store',
	'update',
	'destroy'
];
```

For more information about policies, take a look at Laravel's [Creating Policies](https://laravel.com/docs/9.x/authorization#creating-policies)

### Disable policy authorization

You are able to disable the complete check with following member change in your controller class.

```php
protected $authorizeAbilities = false;
```

### Configure custom paths
```php
    /**
     * Models location
     * @var string
     */
    protected $modelPath = 'App\\Models\\';

    /**
     * Resources location
     * @var string
     */
    protected $resourcePath = 'App\\Http\\Resources\\';
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email office@devlabor.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
