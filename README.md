This package is based on "spatie/laravel-query-builder" allows you to rapidly creating API controllers for your Laravel application. This package also works with authorization policies.

## Basic Usage

Create a new API controller: `ProductApiController`:

```php
use DevLabor\Api\ApiController;

// ...

class ProductApiController extends ApiController {
// ...
}
```

Extend your API routes within `routes/api.php`:

```php
// ...

Route::resource('products', 'Api\ProductApiController');
```

## Installation

You can install the package via composer:

```bash
composer require devlabor/api
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
		 * Number of items returned in index()
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

For more information about policies, take a look at Laravel's [Creating Policies](https://laravel.com/docs/5.8/authorization#creating-policies)

### Disable policy authorization

You are able to disable the complete check with following member change in your controller class.

```php
protected $authorizeAbilities = false;
```

Extend your API routes within `routes/api.php`:

```php
// ...

Route::resource('products', 'Api\ProductApiController');
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